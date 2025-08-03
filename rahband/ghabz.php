<?php
include_once('first.php'); //بررسی ورود
include_once('sar.php');
include_once('ca.php');
include_once('jdf.php');

header('Content-Type: text/html; charset=utf-8');

// حذف
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $query = "UPDATE rghabz SET act = 0 WHERE id = $id";
    mysqli_query($connection, $query) or die(mysqli_error($connection));
    header("Location: ".$_SERVER['PHP_SELF']."?deleted=1");
    exit;
}

// ویرایش فرم
$edit_success = false;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['edit_submit'])) {
    $pid = intval($_POST['edit_id']);
    $pelak = mysqli_real_escape_string($connection, $_POST['edit_pelak']);
    $shomare = mysqli_real_escape_string($connection, $_POST['edit_shomare']);
    $q = "UPDATE rghabz SET pelak='$pelak', shomare='$shomare' WHERE id=$pid";
    $edit_success = mysqli_query($connection, $q);
    header("Location: ".$_SERVER['PHP_SELF']."?edited=1");
    exit;
}

$query = "SELECT id, pelak, zaman, shomare FROM rghabz WHERE act=1 ORDER BY zaman DESC";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت پلاک‌های قبض انبار</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {background: #f7fafd; font-family:'Vazirmatn', Tahoma;}
        .header {background:#07638A; color:#fff; padding:20px 0 16px; border-radius:0 0 36px 36px; margin-bottom:32px; text-align:center; font-weight:bold; font-size:21px; box-shadow: 0 2px 18px #aee5f6; letter-spacing:1px;}
        .pelak-container {max-width:750px;}
        .pelak-card {background: #fff; border-radius:1.4em; box-shadow: 0 4px 18px #a5cfee25; margin-bottom: 18px;}
        .pelak-number {font-weight:bold; font-size:1.1em;}
        .pelak-actions {min-width:135px;}
        .btn-edit {background:#41b8e7; color:#fff;}
        .btn-edit:hover {background:#179fc9;}
        .btn-delete {background: #fa4c4c; color:#fff;}
        .empty-state {background:#fff; color:#666; border-radius:2em; box-shadow:0 5px 20px #cfd4eb2c; padding:44px; text-align:center; margin-top:24px;}
    </style>
</head>
<body>
<div class="header">
    <i class="fas fa-warehouse fa-lg ms-2"></i>
    مدیریت پلاک‌های قبض انبار
    <a href="index.php" class="btn btn-light btn-sm ms-3" style="border-radius:2em;"><i class="fas fa-arrow-right"></i> بازگشت</a>
</div>

<div class="container pelak-container">
    <?php if(isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        پلاک با موفقیت حذف شد!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['edited'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        ویرایش با موفقیت ثبت شد!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="mb-3 text-center">
            <textarea id="allPelaksText" readonly rows="2" class="form-control" style="max-width:400px;display:inline-block;font-weight:bold;text-align:center;background:#eee;"><?php
                mysqli_data_seek($result,0); $out=''; while($r = mysqli_fetch_assoc($result)) $out.=$r['pelak']."\n"; echo trim($out); ?></textarea>
            <button onclick="copyAllPelaks()" class="btn btn-success ms-2"><i class="fas fa-copy"></i> کپی همه پلاک‌ها</button>
        </div>
        
        <div class="row">
        <?php mysqli_data_seek($result,0); while($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-12" id="card-<?php echo $row['id']; ?>">
                <div class="pelak-card p-4 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <div class="pelak-number" id="pelak-text-<?php echo $row['id']; ?>">
                            <i class="fas fa-car ms-1"></i>
                            <?php echo htmlspecialchars($row['pelak']); ?>
                        </div>
                        <div class="pelak-time mt-2">
                            <i class="far fa-clock ms-1"></i>
                            <?php echo jdate('Y/m/d H:i', $row['zaman']); ?>
                        </div>
                        <div class="pelak-shomare mt-2">
                            <i class="fas fa-file ms-1"></i>
                            شماره قبض:
                            <span>
                                <?php echo ($row['shomare'] ? htmlspecialchars($row['shomare']) : '<span class="text-danger">ثبت نشده</span>'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="pelak-actions mt-2">
                        <button type="button"
                            class="btn btn-edit btn-sm px-3 mb-1"
                            data-bs-toggle="modal"
                            data-bs-target="#editPelakModal"
                            data-id="<?php echo $row['id']; ?>"
                            data-pelak="<?php echo htmlspecialchars($row['pelak']); ?>"
                            data-shomare="<?php echo htmlspecialchars($row['shomare']); ?>"
                        ><i class="fas fa-pen"></i></button>
                        <a href="?delete_id=<?php echo $row['id']; ?>"
                            class="btn btn-delete btn-sm px-3 mb-1"
                            onclick="return confirm('آیا از حذف این پلاک اطمینان دارید؟');"
                        ><i class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fad fa-parking fa-3x mb-3"></i>
            <div>پلاکی ثبت نشده است.</div>
        </div>
    <?php endif; ?>
</div>

<!-- مودال ویرایش، فرم کامل (بدون ajax) -->
<div class="modal fade" id="editPelakModal" tabindex="-1" aria-labelledby="editPelakModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPelakModalLabel">ویرایش اطلاعات پلاک</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_id" name="edit_id">
        <div class="mb-3">
          <label class="form-label">شماره پلاک:</label>
          <input type="text" class="form-control" id="edit_pelak" name="edit_pelak" maxlength="30" required>
        </div>
        <div class="mb-3">
          <label class="form-label">شماره قبض انبار:</label>
          <input type="text" class="form-control" id="edit_shomare" name="edit_shomare" maxlength="30" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_submit" class="btn btn-success"><i class="fas fa-save me-1"></i> ثبت ویرایش</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
      </div>
    </form>
  </div>
</div>

<!-- بوت‌استرپ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// پر کردن مقادیر مودال موقع کلیک ویرایش
let modal = document.getElementById('editPelakModal');
modal.addEventListener('show.bs.modal', function(event){
    let button = event.relatedTarget;
    document.getElementById('edit_id').value = button.getAttribute('data-id');
    document.getElementById('edit_pelak').value = button.getAttribute('data-pelak');
    document.getElementById('edit_shomare').value = button.getAttribute('data-shomare');
});

// دکمه کپی همه پلاک‌ها بدون ajax
function copyAllPelaks() {
    let textarea = document.getElementById('allPelaksText');
    textarea.select();
    document.execCommand('copy');
    alert('همه پلاک‌‌ها با موفقیت کپی شدند');
}
</script>
</body>
</html>
