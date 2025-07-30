<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود حساسیت محاسباتی</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazir', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background-color: white;
        }
        .form-title {
            color: #0d6efd;
            margin-bottom: 20px;
        }
        .input-hint {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .current-value {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php
include_once('sar.php');
include_once('jdf.php');
include_once('ca.php');
include_once('aval.php');

// دریافت مقدار فعلی از دیتابیس
$currentSensitivity = 0;
$query = mysqli_query($connection, "SELECT has FROM rhas WHERE id='1'");
if(mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);
    $currentSensitivity = $row['has'];
}

// پردازش فرم پس از ارسال
if(isset($_POST['calculationSensitivity']) && !empty($_POST['calculationSensitivity'])){
    $sensitivity = isset($_POST['calculationSensitivity']) ? (int)$_POST['calculationSensitivity'] : null;
    
    if ($sensitivity && $sensitivity >= 1 && $sensitivity <= 100000) {
        echo '<div class="alert alert-success mt-4">';
        echo 'مقدار با موفقیت ذخیره شد: ' . htmlspecialchars($sensitivity) . ' ساعت';
        echo '</div>';
        
        $rib = $sensitivity * 3600;
        $vib = time();
        $ha = $vib - $rib;
        $time = date('H:i:s', $ha);
        
        $updatedlbl2ow = mysqli_query($connection,"UPDATE rhas SET has='$sensitivity' WHERE id='1'") or die(mysqli_error());
        
        // به روزرسانی مقدار فعلی پس از ذخیره
        $currentSensitivity = $sensitivity;
    } else {
        echo '<div class="alert alert-danger mt-4">';
        echo 'لطفا یک مقدار معتبر بین 1 تا 100000 ساعت وارد نمایید';
        echo '</div>';
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title text-center">تنظیم حساسیت محاسباتی</h2>
        
        <!-- نمایش مقدار فعلی -->
        <div class="current-value">
            <span>مقدار فعلی حساسیت: </span>
            <span class="text-primary"><?php echo htmlspecialchars($currentSensitivity); ?></span>
            <span> ساعت</span>
        </div>
        
        <form method="post" action="set.php">
            <div class="mb-3">
                <label for="calculationSensitivity" class="form-label">
                    حساسیت محاسباتی (ساعت)
                </label>
                <input 
                    type="number" 
                    class="form-control" 
                    id="calculationSensitivity" 
                    name="calculationSensitivity"
                    min="1"
                    max="100000"
                    step="1"
                    required
                    placeholder="مقدار بین 1 تا 100000"
                    value="<?php echo htmlspecialchars($currentSensitivity); ?>">
                <div class="input-hint">
                    لطفا حساسیت محاسباتی قبض انبار را بر اساس ساعت وارد نمایید
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mt-3">ذخیره تنظیمات</button>
        </form>
    </div>
</div>

<?php
if (file_exists("error_log")) {
    unlink("error_log");
}

mysqli_close($connection);
?>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- فونت وزیر برای پشتیبانی از فارسی -->
<style>
    @font-face {
        font-family: Vazir;
        src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir.woff2') format('woff2');
    }
</style>
</body>
</html>