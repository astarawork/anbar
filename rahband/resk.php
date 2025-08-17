<?php
include_once('first.php'); //بررسی ورود
include_once('sar.php');

// تعریف مسیر پوشه تصاویر
$folder_path = './uploadimage/';
$message = '';

// بررسی درخواست‌های POST برای مدیریت تصاویر
if ($_POST) {
    if (isset($_POST['download_zip'])) {
        // دانلود فایل‌های پوشه به صورت ZIP
        downloadFolderAsZip($folder_path);
    } elseif (isset($_POST['delete_images'])) {
        // حذف تمام تصاویر
        $message = deleteAllImages($folder_path);
    }
}

function downloadFolderAsZip($folder_path) {
    // بررسی وجود پوشه
    if (!is_dir($folder_path)) {
        die('پوشه مورد نظر یافت نشد!');
    }
    
    // نام فایل ZIP
    $zip_filename = 'images_backup_' . date('Y-m-d_H-i-s') . '.zip';
    
    // ایجاد فایل ZIP جدید
    $zip = new ZipArchive();
    $zip_path = sys_get_temp_dir() . '/' . $zip_filename;
    
    if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
        die('خطا در ایجاد فایل ZIP');
    }
    
    // اضافه کردن فایل‌های پوشه به ZIP
    $files = scandir($folder_path);
    $file_count = 0;
    
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_path = $folder_path . $file;
            if (is_file($file_path)) {
                $zip->addFile($file_path, $file);
                $file_count++;
            }
        }
    }
    
    $zip->close();
    
    // بررسی اینکه آیا فایلی در ZIP قرار گرفته یا نه
    if ($file_count == 0) {
        unlink($zip_path);
        die('هیچ فایلی در پوشه یافت نشد!');
    }
    
    // ارسال فایل ZIP برای دانلود
    if (file_exists($zip_path)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_path));
        
        // پاک کردن هر خروجی قبلی
        ob_clean();
        flush();
        
        // خواندن و نمایش فایل
        readfile($zip_path);
        
        // حذف فایل موقت
        unlink($zip_path);
        exit;
    } else {
        die('خطا در ایجاد فایل ZIP');
    }
}

function deleteAllImages($folder_path) {
    if (!is_dir($folder_path)) {
        return 'پوشه مورد نظر یافت نشد!';
    }
    
    // انواع فرمت‌های تصویری
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'tiff', 'tif'];
    
    $files = scandir($folder_path);
    $deleted_count = 0;
    $error_count = 0;
    
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_path = $folder_path . $file;
            if (is_file($file_path)) {
                $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                
                // بررسی اینکه فایل تصویر است یا نه
                if (in_array($extension, $image_extensions)) {
                    if (unlink($file_path)) {
                        $deleted_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
        }
    }
    
    if ($deleted_count == 0 && $error_count == 0) {
        return 'هیچ تصویری در پوشه یافت نشد!';
    } elseif ($error_count > 0) {
        return "$deleted_count تصویر با موفقیت حذف شد، $error_count تصویر حذف نشد!";
    } else {
        return "$deleted_count تصویر با موفقیت حذف شد!";
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت عملیات</title>
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            padding: 20px;
            min-height: 100vh;
            margin: 0;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 30px;
            font-size: 20px;
        }
        
        .btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            font-weight: bold;
            min-width: 200px;
        }
        
        .btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        .btn-download {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-download:hover {
            background: linear-gradient(135deg, #218838, #1aa085);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-delete-images {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
        }
        
        .btn-delete-images:hover {
            background: linear-gradient(135deg, #c82333, #d91a72);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        .alert {
            padding: 15px;
            background-color: #2ecc71;
            color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        
        .message {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }
        
        .info-box strong {
            color: #495057;
        }
        
        .button-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        
        .section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .section-title {
            color: #495057;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
            font-weight: bold;
        }
        
        .file-count {
            margin: 15px 0;
            text-align: center;
            color: #6c757d;
            background: #e9ecef;
            padding: 10px;
            border-radius: 8px;
        }
        
        hr {
            border: none;
            height: 2px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 25px 0;
        }
        
        form {
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <?php include_once('aval.php'); ?>
    
    <script type="text/javascript" src="/files/jalalidatepicker.min.js"></script>
    <script>
        jalaliDatepicker.startWatch({
            minDate: "attr",
            maxDate: "attr",
            time: true
        });
    </script>
    
    <?php
    include_once('jdf.php');
    include_once('ca.php');
    
    // پردازش درخواست‌های حذف داده‌های پایگاه داده
    if (isset($_POST['hazf1']) && !empty($_POST['hazf1'])) {
        mysqli_query($connection, "TRUNCATE TABLE rkarbar");
        echo '<div class="alert">اطلاعات کاربر حذف شد</div>';
    }
    
    if (isset($_POST['hazf2']) && !empty($_POST['hazf2'])) {
        mysqli_query($connection, "TRUNCATE TABLE rinfo");
        echo '<div class="alert">اطلاعات ورودی سیستم حذف شد</div>';
    }
    
    if (isset($_POST['hazf3']) && !empty($_POST['hazf3'])) {
        mysqli_query($connection, "TRUNCATE TABLE rinfo2");
        echo '<div class="alert">اطلاعات خروجی های سیستم حذف شد</div>';
    }
    
    if (isset($_POST['hazf']) && !empty($_POST['hazf'])) {
        mysqli_query($connection, "TRUNCATE TABLE rkarbar");
        mysqli_query($connection, "TRUNCATE TABLE rinfo");
        mysqli_query($connection, "TRUNCATE TABLE rinfo2");
        echo '<div class="alert">تمام اطلاعات حذف شدند</div>';
    }
    ?>

    <!-- بخش مدیریت پوشه تصاویر -->
    <div class="section">
        <div class="section-title">🗂️ مدیریت پوشه تصاویر</div>
        
        <div class="info-box">
            <strong>مسیر پوشه:</strong> <?php echo htmlspecialchars($folder_path); ?>
        </div>
        
        <?php
        // نمایش تعداد فایل‌های موجود
        if (is_dir($folder_path)) {
            $files = scandir($folder_path);
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'tiff', 'tif'];
            $image_count = 0;
            $total_files = 0;
            
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $file_path = $folder_path . $file;
                    if (is_file($file_path)) {
                        $total_files++;
                        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                        if (in_array($extension, $image_extensions)) {
                            $image_count++;
                        }
                    }
                }
            }
            
            echo "<div class='file-count'>";
            echo "📁 تعداد کل فایل‌ها: <strong>$total_files</strong><br>";
            echo "🖼️ تعداد تصاویر: <strong>$image_count</strong>";
            echo "</div>";
        }
        ?>
        
        <form method="post">
            <div class="button-container">
                <button type="submit" name="download_zip" class="btn btn-download" 
                        onclick="return confirm('آیا مطمئن هستید که می‌خواهید تمام محتویات پوشه را دانلود کنید؟')">
                    📥 ذخیره به ZIP
                </button>
                
                <button type="submit" name="delete_images" class="btn btn-delete-images" 
                        onclick="return confirm('⚠️ هشدار: تمام تصاویر موجود در پوشه حذف خواهند شد. آیا مطمئن هستید؟')">
                    🗑️ حذف تصاویر
                </button>
            </div>
        </form>
        
        <div class="warning">
            <strong>⚠️ توجه:</strong> قبل از حذف تصاویر، حتماً از آنها نسخه پشتیبان تهیه کنید.
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, 'موفقیت') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!is_dir($folder_path)): ?>
            <div class="message error">
                ❌ پوشه مورد نظر یافت نشد! لطفاً مسیر را بررسی کنید.
            </div>
        <?php endif; ?>
    </div>

    <!-- بخش حذف اطلاعات پایگاه داده -->
    <h2>حذف اطلاعات کاربر</h2>
    
    <form action="resk.php" method="post">
        <input type="hidden" name="hazf" value="hazf">
        <input type="submit" value="حذف تمام ورودی ها" class="btn" 
               onclick="return confirm('⚠️ هشدار: تمام اطلاعات حذف خواهند شد. آیا مطمئن هستید؟')">
    </form>
    <hr>
    
    <form action="resk.php" method="post">
        <input type="hidden" name="hazf1" value="hazf1">
        <input type="submit" value="حذف تمام ورودی های کاربر" class="btn"
               onclick="return confirm('آیا از حذف اطلاعات کاربر مطمئن هستید؟')">
    </form>
    <hr>
    
    <form action="resk.php" method="post">
        <input type="hidden" name="hazf2" value="hazf2">
        <input type="submit" value="حذف تمام ورودی های سیستم راهبند" class="btn"
               onclick="return confirm('آیا از حذف ورودی های سیستم مطمئن هستید؟')">
    </form>
    <hr>
    
    <form action="resk.php" method="post">
        <input type="hidden" name="hazf3" value="hazf3">
        <input type="submit" value="حذف تمام خروجی های سیستم راهبند" class="btn"
               onclick="return confirm('آیا از حذف خروجی های سیستم مطمئن هستید؟')">
    </form>
</div>

<script>
    // نمایش پیام تأیید برای دکمه‌ها
    document.querySelectorAll('.btn-delete-images').forEach(button => {
        button.addEventListener('click', function(e) {
            const imageCount = <?php echo isset($image_count) ? $image_count : 0; ?>;
            if (imageCount === 0) {
                alert('هیچ تصویری برای حذف یافت نشد!');
                e.preventDefault();
                return false;
            }
        });
    });
</script>

<?php
if (file_exists("error_log")) {
    unlink("error_log");
}
mysqli_close($connection);
include_once('sb/foot.php');
?>
</body>
</html>