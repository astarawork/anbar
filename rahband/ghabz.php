<?php
include_once('sar.php');
include_once('ca.php');
include_once('jdf.php'); // فرض می‌کنیم فایل jdf.php در دسترس است

// بررسی اگر درخواست حذف پلاک ارسال شده باشد
if(isset($_GET['delete_pelak']) && !empty($_GET['delete_pelak'])) {
    $pelak_to_delete = mysqli_real_escape_string($connection, $_GET['delete_pelak']);
    $query = "UPDATE rghabz SET act = 0 WHERE pelak = '$pelak_to_delete'";
    mysqli_query($connection, $query) or die(mysqli_error($connection));
    
    // نمایش پیام موفقیت
    $success_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        پلاک با موفقیت حذف شد.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// دریافت پلاک‌های قبض انبار شده که act=1 دارند
$query = "SELECT pelak, zaman FROM rghabz WHERE act = 1 ORDER BY zaman DESC";
$result = mysqli_query($connection, $query) or die(mysqli_error($connection));

// تولید رشته قابل کپی برای همه پلاک‌ها
$all_pelaks = '';
while($row = mysqli_fetch_assoc($result)) {
    $all_pelaks .= $row['pelak'] . "\n";
}
mysqli_data_seek($result, 0); // بازگرداندن اشاره گر به ابتدای نتایج
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت پلاک‌های قبض انبار</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .pelak-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .pelak-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .pelak-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .pelak-number {
            font-weight: bold;
            font-size: 1.2rem;
            color: #0d6efd;
        }
        .pelak-time {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background-color: #bb2d3b;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .empty-state i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .badge-status {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .copy-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px dashed #dee2e6;
        }
        .copy-textarea {
            width: 100%;
            min-height: 100px;
            font-family: monospace;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            direction: ltr;
            text-align: left;
        }
        .btn-copy {
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .pelak-card {
                flex-direction: column;
            }
            .pelak-actions {
                margin-top: 15px;
                justify-content: flex-start !important;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-car me-2"></i>مدیریت پلاک‌های قبض انبار</h1>
                <p class="mb-0">لیست تمام پلاک‌های دارای قبض انبار</p>
            </div>
            <div class="col-md-4 text-start">
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i> بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container pelak-container">
    <?php if(isset($success_message)) echo $success_message; ?>
    
    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="copy-section">
            <h5 class="mb-3"><i class="fas fa-copy me-2"></i>کپی کلی پلاک‌ها</h5>
            <textarea class="copy-textarea" id="allPelaksText" readonly><?php echo trim($all_pelaks); ?></textarea>
            <button class="btn btn-primary btn-copy" onclick="copyAllPelaks()">
                <i class="fas fa-copy me-2"></i>کپی همه پلاک‌ها
            </button>
        </div>
        
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">تعداد پلاک‌ها: <?php echo mysqli_num_rows($result); ?></h5>
                            </div>
                            <div>
                                <span class="badge bg-primary badge-status">فعال</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-12">
                    <div class="pelak-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="pelak-number mb-2">
                                    <i class="fas fa-car me-2"></i><?php echo htmlspecialchars($row['pelak']); ?>
                                </div>
                                <div class="pelak-time">
                                    <i class="far fa-clock me-2"></i>
                                    زمان ثبت: <?php echo jdate('Y/m/d H:i', $row['zaman']); ?>
                                </div>
                            </div>
                            <div class="pelak-actions d-flex justify-content-end">
                                <a href="?delete_pelak=<?php echo $row['pelak']; ?>" class="btn btn-delete btn-sm">
                                    <i class="fas fa-trash-alt me-1"></i> حذف کردن
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="far fa-folder-open"></i>
            <h4 class="mt-3">پلاکی یافت نشد</h4>
            <p class="text-muted">هیچ پلاک فعالی با قبض انبار ثبت نشده است.</p>
            <a href="index.php" class="btn btn-primary mt-3">
                <i class="fas fa-home me-2"></i> بازگشت به صفحه اصلی
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // تایید قبل از حذف پلاک
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if(!confirm('آیا از حذف این پلاک اطمینان دارید؟')) {
                e.preventDefault();
            }
        });
    });
    
    // کپی کردن همه پلاک‌ها
    function copyAllPelaks() {
        const textarea = document.getElementById('allPelaksText');
        textarea.select();
        document.execCommand('copy');
        
        // نمایش پیام موفقیت
        alert('همه پلاک‌ها با موفقیت کپی شدند');
    }
</script>

</body>
</html>