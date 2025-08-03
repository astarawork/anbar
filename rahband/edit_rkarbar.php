<?php
include_once('first.php');
include_once('sar.php');
include_once('aval.php');
include_once('jdf.php');
include_once('ca.php');

// بررسی وجود id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: rep.php');
    exit;
}

$id = intval($_GET['id']);

// دریافت اطلاعات رکورد مورد نظر
$query = mysqli_query($connection, "SELECT * FROM rkarbar WHERE id = $id") or die(mysqli_error());
if (mysqli_num_rows($query) == 0) {
    header('Location: rep.php');
    exit;
}

$record = mysqli_fetch_assoc($query);

// پردازش فرم ویرایش
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $latitude = mysqli_real_escape_string($connection, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($connection, $_POST['longitude']);
    $makan = mysqli_real_escape_string($connection, $_POST['makan']);
    $barchasb = intval($_POST['barchasb']);
    $raha = intval($_POST['raha']);
    
    // بروزرسانی رکورد
    $update_query = "UPDATE rkarbar SET 
                     latitude = '$latitude',
                     longitude = '$longitude',
                     makan = '$makan',
                     barchasb = $barchasb,
                     raha = $raha
                     WHERE id = $id";
    
    if (mysqli_query($connection, $update_query)) {
        $success_message = "اطلاعات با موفقیت بروزرسانی شد";
    } else {
        $error_message = "خطا در بروزرسانی اطلاعات: " . mysqli_error($connection);
    }
    
    // دریافت اطلاعات بروزرسانی شده
    $query = mysqli_query($connection, "SELECT * FROM rkarbar WHERE id = $id");
    $record = mysqli_fetch_assoc($query);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش اطلاعات پلاک</title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Vazir', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background-color: #0d6efd;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .edit-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .readonly-field {
            background-color: #e9ecef;
            color: #6c757d;
        }
        .map-container {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }
        .alert {
            border-radius: 10px;
        }
        @font-face {
            font-family: Vazir;
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir.woff2') format('woff2');
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-edit me-2"></i>ویرایش اطلاعات پلاک</h1>
            </div>
            <div class="col-md-4 text-start">
                <a href="rep.php" class="btn btn-light">
                    <i class="fas fa-arrow-right me-1"></i>بازگشت به گزارشات
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="edit-container">
        <form method="POST" id="editForm">
            <div class="row">
                <!-- فیلدهای ثابت (فقط نمایش) -->
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-hashtag me-1"></i>شناسه</label>
                        <input type="text" class="form-control readonly-field" value="<?php echo $record['id']; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-car me-1"></i>پلاک</label>
                        <input type="text" class="form-control readonly-field" value="<?php echo $record['pelak']; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-clock me-1"></i>زمان ثبت</label>
                        <input type="text" class="form-control readonly-field" value="<?php echo tr_num(jdate('Y/n/j H:i:s', $record['zaman'])); ?>" readonly>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- فیلدهای قابل ویرایش -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>عرض جغرافیایی</label>
                        <input type="number" step="any" class="form-control" name="latitude" value="<?php echo $record['latitude']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>طول جغرافیایی</label>
                        <input type="number" step="any" class="form-control" name="longitude" value="<?php echo $record['longitude']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-map me-1"></i>موقعیت</label>
                        <input type="text" class="form-control" name="makan" value="<?php echo $record['makan']; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tag me-1"></i>وضعیت الصاقیه</label>
                        <select class="form-select" name="barchasb">
                            <option value="0" <?php echo ($record['barchasb'] == 0) ? 'selected' : ''; ?>>الصاق نشده</option>
                            <option value="1" <?php echo ($record['barchasb'] == 1) ? 'selected' : ''; ?>>الصاق شده</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-check-circle me-1"></i>وضعیت رها شدن</label>
                        <select class="form-select" name="raha">
                            <option value="0" <?php echo ($record['raha'] == 0) ? 'selected' : ''; ?>>رها نشده</option>
                            <option value="1" <?php echo ($record['raha'] == 1) ? 'selected' : ''; ?>>رها شده</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- نقشه برای نمایش موقعیت -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-map me-1"></i>نمایش موقعیت روی نقشه</label>
                <div id="map" class="map-container"></div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-save me-2"></i>ذخیره تغییرات
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="rep.php" class="btn btn-secondary btn-lg w-100">
                        <i class="fas fa-times me-2"></i>انصراف
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // مقداردهی اولیه نقشه
    let map, marker;
    let currentLat = <?php echo !empty($record['latitude']) ? $record['latitude'] : '35.6892'; ?>;
    let currentLng = <?php echo !empty($record['longitude']) ? $record['longitude'] : '51.3890'; ?>;

    // راه‌اندازی نقشه
    function initMap() {
        map = L.map('map').setView([currentLat, currentLng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        marker = L.marker([currentLat, currentLng]).addTo(map)
            .bindPopup('موقعیت فعلی')
            .openPopup();
    }

    // بروزرسانی موقعیت مارکر
    function updateMarker() {
        const lat = parseFloat(document.querySelector('input[name="latitude"]').value);
        const lng = parseFloat(document.querySelector('input[name="longitude"]').value);
        
        if (lat && lng) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 15);
            marker.bindPopup(`موقعیت جدید: ${lat.toFixed(6)}, ${lng.toFixed(6)}`).openPopup();
        }
    }

    // رویداد کلیک روی نقشه
    function onMapClick(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        document.querySelector('input[name="latitude"]').value = lat.toFixed(6);
        document.querySelector('input[name="longitude"]').value = lng.toFixed(6);
        
        marker.setLatLng([lat, lng]);
        marker.bindPopup(`موقعیت انتخاب شده: ${lat.toFixed(6)}, ${lng.toFixed(6)}`).openPopup();
    }

    // راه‌اندازی نقشه بعد از بارگذاری صفحه
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        map.on('click', onMapClick);
        
        // رویداد تغییر مختصات
        document.querySelector('input[name="latitude"]').addEventListener('change', updateMarker);
        document.querySelector('input[name="longitude"]').addEventListener('change', updateMarker);
    });

    // تأیید فرم قبل از ارسال
    document.getElementById('editForm').addEventListener('submit', function(e) {
        const lat = document.querySelector('input[name="latitude"]').value;
        const lng = document.querySelector('input[name="longitude"]').value;
        
        if (!lat || !lng) {
            e.preventDefault();
            alert('لطفاً مختصات جغرافیایی را وارد کنید');
            return false;
        }
    });
</script>

<?php
mysqli_close($connection);
?>
</body>
</html> 