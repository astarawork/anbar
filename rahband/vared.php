<?php
session_start();
include_once('sar.php');
include_once('ca.php');

// بررسی لاگین بودن کاربر
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// دریافت نام کاربری از session
$loggedin_username = $_SESSION['username'];

function convertPersianNumbersToEnglish($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    
    $string = str_replace($persian, $english, $string);
    $string = str_replace($arabic, $english, $string);
    
    return $string;
}

function removeSpaces($string) {
    return str_replace(' ', '', $string);
}

// حالت‌های مختلف فرم
$show_warning = false;
$confirmed = false;
$form_submitted = false;

// متغیرهای فرم برای حفظ مقادیر
$form_values = [
    'type' => isset($_POST['type']) ? $_POST['type'] : 'kharej',
    'khareji' => isset($_POST['khareji']) ? $_POST['khareji'] : '',
    'charstype' => isset($_POST['charstype']) ? $_POST['charstype'] : '',
    'charstype2' => isset($_POST['charstype2']) ? $_POST['charstype2'] : '',
    'charstype3' => isset($_POST['charstype3']) ? $_POST['charstype3'] : '',
    'makan' => isset($_POST['makan']) ? $_POST['makan'] : '',
    'barchasb' => isset($_POST['barchasb']) ? $_POST['barchasb'] : '0',
    'raha' => isset($_POST['raha']) ? $_POST['raha'] : '0',
    'latitude' => isset($_POST['latitude']) ? $_POST['latitude'] : '',
    'longitude' => isset($_POST['longitude']) ? $_POST['longitude'] : ''
];

if(isset($_POST['type']) && !empty($_POST['type'])) {
    // بررسی آیا کاربر اخطار را تایید کرده است
    if(isset($_POST['confirm_submit']) && $_POST['confirm_submit'] == '1') {
        $confirmed = true;
    }
    
    // دریافت مختصات GPS
    $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;

    if($_POST['type'] == "kharej") {
        $pir = strtolower(trim($_POST['khareji']));
        $pir = removeSpaces($pir);
        $pir = convertPersianNumbersToEnglish($pir);
    } else if($_POST['type'] == "iran") {
        $rr1 = convertPersianNumbersToEnglish(removeSpaces(trim($_POST['charstype'])));
        $rr2 = convertPersianNumbersToEnglish(removeSpaces(trim($_POST['charstype2'])));
        $rr3 = convertPersianNumbersToEnglish(removeSpaces(trim($_POST['charstype3'])));
        $pir = $rr1."u".$rr2.$rr3;
    }
    
    // بررسی وجود پلاک در rkarbar
    $stmt = mysqli_prepare($connection, "SELECT * FROM rkarbar WHERE pelak = ?");
    mysqli_stmt_bind_param($stmt, "s", $pir);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) > 0) {
        $error_message = '
        <div class="form-container">
            <div class="alert alert-danger">
                <h4 class="alert-heading">خطا در ثبت</h4>
                <p>این پلاک قبلاً ثبت شده است: '.htmlspecialchars($pir).'</p>
            </div>
        </div>';
    } else {
        // بررسی وجود پلاک در rinfo
        $stmt_rinfo = mysqli_prepare($connection, "SELECT * FROM rinfo WHERE pelak = ?");
        mysqli_stmt_bind_param($stmt_rinfo, "s", $pir);
        mysqli_stmt_execute($stmt_rinfo);
        mysqli_stmt_store_result($stmt_rinfo);
        
        if(mysqli_stmt_num_rows($stmt_rinfo) == 0 && !$confirmed) {
            // پلاک در rinfo وجود ندارد و کاربر هنوز تایید نکرده است
            $show_warning = true;
            $warning_pelak = $pir;
        } else {
            $barchasb = 0;  // پیش‌فرض برای پلاک‌های جدید
            $raha = 0;      // پیش‌فرض برای پلاک‌های جدید
            
            // اگر پلاک در rinfo وجود دارد، مقادیر را از فرم بگیر
            if(mysqli_stmt_num_rows($stmt_rinfo) > 0) {
                $barchasb = isset($_POST['barchasb']) ? 1 : 0;
                $raha = isset($_POST['raha']) ? 1 : 0;
            }
            
            // اگر کاربر به صورت دستی مقادیر را انتخاب کرده، آنها را اعمال کن
            if(isset($_POST['barchasb'])) {
                $barchasb = $_POST['barchasb'];
            }
            if(isset($_POST['raha'])) {
                $raha = $_POST['raha'];
            }
            
            $shi = time();
            $makan = trim($_POST['makan']);
            
            // درج اطلاعات با نام کاربری
            $stmt = mysqli_prepare($connection, "INSERT INTO rkarbar (pelak, user, zaman, barchasb, raha, makan, latitude, longitude) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssiiisdd", $pir, $loggedin_username, $shi, $barchasb, $raha, $makan, $latitude, $longitude);
            mysqli_stmt_execute($stmt);
            
            $queryfodi = mysqli_query($connection,"SELECT * FROM rhas WHERE id='1'") or die(mysqli_error());
            $countf = mysqli_num_rows($queryfodi);
            if($countf>0){
                while($fodstk = mysqli_fetch_array($queryfodi)) { 
                    $has = $fodstk['has']*3600;    
                }
            }
            
            $queryfodi = mysqli_query($connection,"SELECT * FROM rinfo WHERE pelak='$pir'") or die(mysqli_error());
            $countf = mysqli_num_rows($queryfodi);
            if($countf>0){
                while($fodstk = mysqli_fetch_array($queryfodi)) { 
                    $zaman2 = $fodstk['zaman'];    
                }
            }
            
            $success_message = '
            <div class="form-container">
                <div class="alert alert-success">
                    <h4 class="alert-heading">ثبت موفق</h4>
                    <p>اطلاعات پلاک با موفقیت ثبت شد</p>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>شماره پلاک:</strong> '.htmlspecialchars($pir).'
                        </div>
                        <div class="col-md-4">
                            <strong>ثبت کننده:</strong> '.htmlspecialchars($loggedin_username).'
                        </div>
                        <div class="col-md-4">
                            <strong>وضعیت برچسب:</strong> '.($barchasb ? '<span class="badge bg-success">دارد</span>' : '<span class="badge bg-secondary">ندارد</span>').'
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <strong>وضعیت رها شده:</strong> '.($raha ? '<span class="badge bg-danger">رها شده</span>' : '<span class="badge bg-secondary">عادی</span>').'
                        </div>
                        <div class="col-md-8">
                            <strong>موقعیت GPS:</strong> '.($latitude && $longitude ? 
                                '<span class="badge bg-info" title="عرض: '.$latitude.'، طول: '.$longitude.'">ثبت شد</span>' : 
                                '<span class="badge bg-warning">ثبت نشد</span>').'
                        </div>
                    </div>
                    '.($latitude && $longitude ? '
                    <div class="mt-3">
                        <a href="https://www.google.com/maps?q='.$latitude.','.$longitude.'" target="_blank" class="btn btn-sm btn-outline-primary">
                            مشاهده موقعیت در نقشه
                        </a>
                    </div>' : '').'
                </div>
            </div>';
            
            // ریست کردن مقادیر فرم بعد از ثبت موفق
            $form_values = [
                'type' => 'kharej',
                'khareji' => '',
                'charstype' => '',
                'charstype2' => '',
                'charstype3' => '',
                'makan' => '',
                'barchasb' => '0',
                'raha' => '0',
                'latitude' => '',
                'longitude' => ''
            ];
        }
        mysqli_stmt_close($stmt_rinfo);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت عملیات</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-title {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
        }
        .card-option {
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .card-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            font-weight: 600;
            background-color: #f1f8ff;
        }
        .input-group-text {
            min-width: 40px;
            justify-content: center;
        }
        .btn-submit {
            width: 100%;
            padding: 10px;
            font-weight: 600;
            margin-top: 15px;
        }
        #resultContainer {
            margin: 20px 0;
            text-align: center;
        }
        .form-check {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-check-input {
            width: 24px;
            height: 24px;
            margin-left: 10px;
            margin-right: 0;
            position: relative;
            flex-shrink: 0;
        }
        .form-check-label {
            order: -1;
            flex-grow: 1;
            padding-left: 10px;
            font-size: 1.1rem;
        }
        .duplicate-error {
            color: #dc3545;
            font-weight: bold;
            margin-top: 5px;
            text-align: center;
        }
        .ltr-input-group {
            direction: ltr;
        }
        .ltr-input {
            text-align: left;
            direction: ltr;
            font-family: monospace;
            font-size: 1.1rem;
        }
        .iran-pelak-format {
            direction: rtl;
            text-align: right;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        #makan {
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        #makan:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            background-color: white;
        }
        .form-label.fw-bold {
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }
        .form-text.text-muted {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #6c757d !important;
        }
        .gps-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
            padding: 10px;
            background-color: #e8f4fd;
            border-radius: 8px;
            border: 1px solid #cfe2ff;
        }
        .gps-status i {
            font-size: 1.2rem;
            color: #0d6efd;
        }
        .gps-status .text {
            font-size: 0.9rem;
        }
        .gps-coords {
            margin-top: 10px;
            font-family: monospace;
            direction: ltr;
            text-align: center;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        #submitBtn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .warning-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1050;
        }
        .warning-content {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        @media (max-width: 576px) {
            .form-container {
                margin: 15px;
                padding: 15px;
            }
            .input-group > .form-control {
                flex: 1;
                min-width: 50px;
            }
            .form-check-input {
                width: 22px;
                height: 22px;
            }
            .form-check-label {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<?php
if(isset($success_message)) {
    echo $success_message;
} elseif(isset($error_message)) {
    echo $error_message;
}
?>

<?php if($show_warning): ?>
    <div class="warning-modal">
        <div class="warning-content">
            <div class="alert alert-warning">
                <h4 class="alert-heading">هشدار!</h4>
                <p>پلاک وارد شده (<strong><?php echo htmlspecialchars($warning_pelak); ?></strong>) در سیستم راهبند ثبت نشده است.</p>
                <hr>
                <p class="mb-0">آیا از صحت پلاک وارد شده اطمینان دارید؟</p>
            </div>
            <form method="post">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($form_values['type']); ?>">
                <input type="hidden" name="khareji" value="<?php echo htmlspecialchars($form_values['khareji']); ?>">
                <input type="hidden" name="charstype" value="<?php echo htmlspecialchars($form_values['charstype']); ?>">
                <input type="hidden" name="charstype2" value="<?php echo htmlspecialchars($form_values['charstype2']); ?>">
                <input type="hidden" name="charstype3" value="<?php echo htmlspecialchars($form_values['charstype3']); ?>">
                <input type="hidden" name="makan" value="<?php echo htmlspecialchars($form_values['makan']); ?>">
                <input type="hidden" name="barchasb" value="<?php echo htmlspecialchars($form_values['barchasb']); ?>">
                <input type="hidden" name="raha" value="<?php echo htmlspecialchars($form_values['raha']); ?>">
                <input type="hidden" name="latitude" value="<?php echo htmlspecialchars($form_values['latitude']); ?>">
                <input type="hidden" name="longitude" value="<?php echo htmlspecialchars($form_values['longitude']); ?>">
                <input type="hidden" name="confirm_submit" value="1">
                
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-success flex-grow-1 me-2">
                        <i class="fas fa-check-circle me-2"></i> بله، اطمینان دارم
                    </button>
                    <button type="button" onclick="history.back()" class="btn btn-danger flex-grow-1 ms-2">
                        <i class="fas fa-times-circle me-2"></i> انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="form-container">
    <h2 class="form-title">ثبت عملیات</h2>
    
    <div id="resultContainer"></div>
    <div id="duplicateWarning" class="duplicate-error"></div>

    <form id="mainForm" action="vared.php" method="post">
        <div class="card card-option border-primary">
            <div class="card-header">
                <input onclick="togglePelakFields(true)" type="radio" name="type" value="kharej" <?php echo $form_values['type'] == 'kharej' ? 'checked' : ''; ?> class="form-check-input">
                پلاک خارجی
            </div>
            <div class="card-body">
                <div class="form-floating">
                    <input type="text" class="form-control" name="khareji" id="custom" placeholder="پلاک خارجی" required oninput="removeSpaces(this)" value="<?php echo htmlspecialchars($form_values['khareji']); ?>">
                    <label for="custom">پلاک خارجی</label>
                </div>
            </div>
        </div>

        <div class="card card-option border-primary">
            <div class="card-header">
                <input onclick="togglePelakFields(false)" type="radio" name="type" value="iran" <?php echo $form_values['type'] == 'iran' ? 'checked' : ''; ?> class="form-check-input">
                پلاک ایرانی
            </div>
            <div class="card-body">
                <div class="input-group mb-3 ltr-input-group">
                    <input type="text" class="form-control ltr-input" maxlength="2" id="charstype" name="charstype" pattern="[\d]*" <?php echo $form_values['type'] == 'kharej' ? 'disabled' : ''; ?> placeholder="12" oninput="removeSpaces(this)" value="<?php echo htmlspecialchars($form_values['charstype']); ?>">
                    <span class="input-group-text bg-light">ع</span>
                    <input type="text" class="form-control ltr-input" maxlength="3" id="charstype2" name="charstype2" pattern="[\d]*" <?php echo $form_values['type'] == 'kharej' ? 'disabled' : ''; ?> placeholder="345" oninput="removeSpaces(this)" value="<?php echo htmlspecialchars($form_values['charstype2']); ?>">
                    <span class="input-group-text bg-light">-</span>
                    <input type="text" class="form-control ltr-input" maxlength="2" id="charstype3" name="charstype3" pattern="[\d]*" <?php echo $form_values['type'] == 'kharej' ? 'disabled' : ''; ?> placeholder="67" oninput="removeSpaces(this)" value="<?php echo htmlspecialchars($form_values['charstype3']); ?>">
                </div>
                <div class="iran-pelak-format text-muted">فرمت: 12ع345-67 (ورود از چپ به راست)</div>
            </div>
        </div>

        <div class="mb-3 position-relative">
            <label for="makan" class="form-label fw-bold">موقعیت</label>
            <input type="text" 
                   class="form-control" 
                   name="makan" 
                   id="makan" 
                   value="<?php echo htmlspecialchars($form_values['makan']); ?>"
                   placeholder="مثال: درب ساحلی"
                   required>
            <div class="form-text text-muted">موقعیت فعلی خود را مشخص کنید</div>
        </div>

        <div class="form-check text-center my-4 p-3 bg-light rounded">
            <input class="form-check-input" type="checkbox" name="barchasb" id="barchasb" value="1" <?php echo $form_values['barchasb'] == '1' ? 'checked' : ''; ?>
                   style="width: 20px; height: 20px; margin-left: 10px;">
            <label class="form-check-label fw-bold fs-5" for="barchasb" 
                   style="color: #495057;">
                خودرو دارای قبض انبار است
            </label>
        </div>
        
        <div class="form-check text-center my-4 p-3 bg-light rounded">
            <input class="form-check-input" type="checkbox" name="raha" id="raha" value="1" <?php echo $form_values['raha'] == '1' ? 'checked' : ''; ?>
                   style="width: 20px; height: 20px; margin-left: 10px;">
            <label class="form-check-label fw-bold fs-5" for="raha" 
                   style="color: #495057;">
                خودروی رها شده
            </label>
        </div>
        
        <div class="gps-status">
            <i class="fas fa-map-marker-alt"></i>
            <div class="text">در حال دریافت موقعیت جغرافیایی...</div>
        </div>
        
        <div id="gpsCoords" class="gps-coords d-none">
            <span id="latitudeDisplay">عرض جغرافیایی: -</span> | 
            <span id="longitudeDisplay">طول جغرافیایی: -</span>
        </div>
        
        <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($form_values['latitude']); ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($form_values['longitude']); ?>">
        
        <button type="button" id="submitBtn" class="btn btn-primary btn-submit" disabled>
            <i class="fas fa-paper-plane me-2"></i> ثبت اطلاعات
        </button>
    </form>
</div>

<script type="text/javascript" src="/files/jalalidatepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // متغیرهای وضعیت موقعیت یابی
    let hasValidPosition = false;
    let currentLatitude = null;
    let currentLongitude = null;
    let positionWatchId = null;
    
    // تابع برای حذف فاصله‌ها از فیلدهای ورودی
    function removeSpaces(input) {
        input.value = input.value.replace(/\s/g, '');
    }
    
    // شروع موقعیت یابی خودکار هر 10 ثانیه
    function startAutoPositioning() {
        // اگر قبلا موقعیت یابی فعال بود، آن را متوقف کن
        if(positionWatchId !== null) {
            navigator.geolocation.clearWatch(positionWatchId);
        }
        
        const gpsStatus = document.querySelector('.gps-status');
        const gpsCoords = document.getElementById('gpsCoords');
        const submitBtn = document.getElementById('submitBtn');
        
        // نمایش وضعیت در حال دریافت موقعیت
        gpsStatus.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            <div class="text">در حال دریافت موقعیت جغرافیایی...</div>
        `;
        
        // مخفی کردن مختصات تا زمانی که دریافت شوند
        gpsCoords.classList.add('d-none');
        
        // غیرفعال کردن دکمه ثبت تا زمانی که موقعیت معتبر دریافت شود
        submitBtn.disabled = true;
        hasValidPosition = false;
        
        // اگر قبلاً مختصاتی در فرم وجود دارد، از آن استفاده کنید
        const existingLat = document.getElementById('latitude').value;
        const existingLng = document.getElementById('longitude').value;
        
        if(existingLat && existingLng) {
            currentLatitude = parseFloat(existingLat);
            currentLongitude = parseFloat(existingLng);
            
            document.getElementById('latitudeDisplay').textContent = `عرض جغرافیایی: ${currentLatitude.toFixed(6)}`;
            document.getElementById('longitudeDisplay').textContent = `طول جغرافیایی: ${currentLongitude.toFixed(6)}`;
            gpsCoords.classList.remove('d-none');
            
            gpsStatus.innerHTML = `
                <i class="fas fa-check-circle text-success"></i>
                <div class="text">موقعیت جغرافیایی با موفقیت دریافت شد</div>
            `;
            
            submitBtn.disabled = false;
            hasValidPosition = true;
            return;
        }
        
        // تلاش برای دریافت موقعیت
        if (navigator.geolocation) {
            positionWatchId = navigator.geolocation.watchPosition(
                function(position) {
                    // ذخیره مختصات فعلی
                    currentLatitude = position.coords.latitude;
                    currentLongitude = position.coords.longitude;
                    
                    // نمایش مختصات
                    document.getElementById('latitude').value = currentLatitude;
                    document.getElementById('longitude').value = currentLongitude;
                    document.getElementById('latitudeDisplay').textContent = `عرض جغرافیایی: ${currentLatitude.toFixed(6)}`;
                    document.getElementById('longitudeDisplay').textContent = `طول جغرافیایی: ${currentLongitude.toFixed(6)}`;
                    gpsCoords.classList.remove('d-none');
                    
                    // نمایش موفقیت دریافت موقعیت
                    gpsStatus.innerHTML = `
                        <i class="fas fa-check-circle text-success"></i>
                        <div class="text">موقعیت جغرافیایی با موفقیت دریافت شد</div>
                    `;
                    
                    // فعال کردن دکمه ثبت
                    submitBtn.disabled = false;
                    hasValidPosition = true;
                },
                function(error) {
                    // نمایش خطا در دریافت موقعیت
                    let errorMessage;
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "دسترسی به موقعیت جغرافیایی رد شد. لطفاً مجوز دسترسی را فعال کنید.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = "اطلاعات موقعیت در دسترس نیست.";
                            break;
                        case error.TIMEOUT:
                            errorMessage = "دریافت موقعیت زمان‌بر شد.";
                            break;
                        case error.UNKNOWN_ERROR:
                            errorMessage = "خطای ناشناخته در دریافت موقعیت رخ داد.";
                            break;
                    }
                    
                    gpsStatus.innerHTML = `
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <div class="text">${errorMessage}</div>
                    `;
                    
                    // غیرفعال نگه داشتن دکمه ثبت
                    submitBtn.disabled = true;
                    hasValidPosition = false;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
            
            // تنظیم تایمر برای بروزرسانی موقعیت هر 10 ثانیه
            setInterval(() => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            currentLatitude = position.coords.latitude;
                            currentLongitude = position.coords.longitude;
                            
                            document.getElementById('latitude').value = currentLatitude;
                            document.getElementById('longitude').value = currentLongitude;
                            document.getElementById('latitudeDisplay').textContent = `عرض جغرافیایی: ${currentLatitude.toFixed(6)}`;
                            document.getElementById('longitudeDisplay').textContent = `طول جغرافیایی: ${currentLongitude.toFixed(6)}`;
                            
                            if(!hasValidPosition) {
                                gpsStatus.innerHTML = `
                                    <i class="fas fa-check-circle text-success"></i>
                                    <div class="text">موقعیت جغرافیایی با موفقیت دریافت شد</div>
                                `;
                                gpsCoords.classList.remove('d-none');
                                submitBtn.disabled = false;
                                hasValidPosition = true;
                            }
                        },
                        function(error) {
                            // در صورت خطا، وضعیت قبلی حفظ می‌شود
                        }
                    );
                }
            }, 10000); // هر 10 ثانیه
        } else {
            gpsStatus.innerHTML = `
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <div class="text">مرورگر شما از دریافت موقعیت پشتیبانی نمی‌کند.</div>
            `;
        }
    }
    
    // ارسال فرم پس از اعتبارسنجی
    document.getElementById('submitBtn').addEventListener('click', function() {
        const form = document.getElementById('mainForm');
        const gpsStatus = document.querySelector('.gps-status');
        
        // اعتبارسنجی سایر فیلدها
        if(!validateForm()) {
            return;
        }
        
        // اگر موقعیت معتبر نداریم، فرم ارسال نشود
        if(!hasValidPosition) {
            gpsStatus.innerHTML = `
                <i class="fas fa-exclamation-triangle text-danger"></i>
                <div class="text">لطفاً صبر کنید تا موقعیت جغرافیایی دریافت شود</div>
            `;
            return;
        }
        
        // نمایش وضعیت در حال ارسال
        gpsStatus.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            <div class="text">در حال ارسال اطلاعات...</div>
        `;
        
        // ارسال فرم
        form.submit();
    });
    
    // اعتبارسنجی فرم
    function validateForm() {
        // بررسی تکراری نبودن پلاک
        const warningDiv = document.getElementById('duplicateWarning');
        if(warningDiv && warningDiv.innerHTML.includes('⚠️')) {
            alert('لطفاً پلاک تکراری را اصلاح کنید');
            return false;
        }
        
        // بررسی پر بودن فیلدهای الزامی
        const type = document.querySelector('input[name="type"]:checked').value;
        if (type === "kharej") {
            const pelak = document.getElementById('custom').value.trim();
            if(!pelak) {
                alert('لطفاً شماره پلاک را وارد کنید');
                return false;
            }
        } else if (type === "iran") {
            const part1 = document.getElementById('charstype').value.trim();
            const part2 = document.getElementById('charstype2').value.trim();
            const part3 = document.getElementById('charstype3').value.trim();
            if(!part1 || !part2 || !part3) {
                alert('لطفاً تمام بخش‌های پلاک را کامل کنید');
                return false;
            }
        }
        
        // بررسی موقعیت
        const makan = document.getElementById('makan').value.trim();
        if(!makan) {
            alert('لطفاً موقعیت را مشخص کنید');
            return false;
        }
        
        return true;
    }
    
    // بقیه توابع موجود...
    jalaliDatepicker.startWatch({
        minDate: "attr",
        maxDate: "attr",
        time: true
    });

    function checkDuplicate(pelak) {
        if(!pelak) return;
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_duplicate.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    const warningDiv = document.getElementById('duplicateWarning');
                    if(response.isDuplicate) {
                        warningDiv.innerHTML = '⚠️ این پلاک قبلاً ثبت شده است!';
                    } else {
                        warningDiv.innerHTML = '';
                    }
                } catch(e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        xhr.onerror = function() {
            console.error('Request failed');
        };
        xhr.send('pelak=' + encodeURIComponent(pelak));
    }

    function checkPelak() {
        const type = document.querySelector('input[name="type"]:checked').value;
        let pelak = '';
        
        if (type === "kharej") {
            pelak = document.getElementById('custom').value.trim().toLowerCase();
        } else if (type === "iran") {
            const part1 = document.getElementById('charstype').value.trim();
            const part2 = document.getElementById('charstype2').value.trim();
            const part3 = document.getElementById('charstype3').value.trim();
            pelak = part1 + "u" + part2 + part3;
        }
        
        if (pelak.length === 0) {
            document.getElementById('resultContainer').innerHTML = '';
            document.getElementById('duplicateWarning').innerHTML = '';
            return;
        }
        
        checkDuplicate(pelak);
        
        document.getElementById('resultContainer').innerHTML = `
            <div class="alert alert-info">
                در حال بررسی...
            </div>
        `;
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_pelak.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('resultContainer').innerHTML = this.responseText;
            } else {
                document.getElementById('resultContainer').innerHTML = `
                    <div class="alert alert-danger">
                        خطا در ارتباط با سرور
                    </div>
                `;
            }
        };
        xhr.send('pelak=' + encodeURIComponent(pelak));
    }
    
    function handleIranPelakInput() {
        const inputs = [
            document.getElementById('charstype'),
            document.getElementById('charstype2'),
            document.getElementById('charstype3')
        ];
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length >= this.maxLength && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                checkPelak();
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });
    }
    
    function togglePelakFields(isKharej) {
        document.getElementById('custom').disabled = !isKharej;
        document.getElementById('charstype').disabled = isKharej;
        document.getElementById('charstype2').disabled = isKharej;
        document.getElementById('charstype3').disabled = isKharej;
        
        if (!isKharej) {
            setTimeout(() => {
                document.getElementById('charstype').focus();
            }, 100);
        }
        
        checkPelak();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // شروع موقعیت یابی خودکار هنگام بارگذاری صفحه
        startAutoPositioning();
        
        document.getElementById('custom').addEventListener('input', function() {
            this.value = this.value.toLowerCase();
            checkPelak();
        });
        
        document.getElementById('charstype').addEventListener('input', checkPelak);
        document.getElementById('charstype2').addEventListener('input', checkPelak);
        document.getElementById('charstype3').addEventListener('input', checkPelak);
        
        handleIranPelakInput();
        
        // اگر نوع پلاک ایرانی است، فیلدهای مربوطه را فعال کنید
        if(document.querySelector('input[name="type"]:checked').value === 'iran') {
            togglePelakFields(false);
        }
    });
</script>

<?php
include_once('sb/foot.php');
?>