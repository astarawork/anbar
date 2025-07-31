<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت | سیستم گزارش پلاک کامیون‌ها</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4ad66d;
            --warning-color: #f8961e;
            --danger-color: #f94144;
            --dark-color: #212529;
            --light-color: #f8f9fa;
            --user-create-color: #7209b7;
            --export-color: #4895ef; /* رنگ جدید برای دکمه آپلود خروجی */
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Vazir', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: all 0.4s ease;
            overflow: hidden;
        }
        
        .glass-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .btn-custom {
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            margin: 8px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .btn-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
            z-index: -1;
        }
        
        .btn-custom:hover::before {
            width: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-settings {
            background-color: #6c757d;
        }
        
        .btn-map {
            background-color: var(--secondary-color);
        }
        
        .btn-ghabz {
            background-color: #20c997;
        }
        
        .btn-user-create {
            background-color: var(--user-create-color);
        }
        
        .btn-export {
            background-color: var(--export-color); /* استایل دکمه جدید */
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            animation: rotate 15s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .feature-icon {
            font-size: 1.5rem;
            margin-left: 10px;
            transition: all 0.3s;
        }
        
        .btn-custom:hover .feature-icon {
            transform: scale(1.2);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .btn-custom {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4 py-lg-5">
        <!-- هدر -->
        <header class="header p-4 p-lg-5 mb-5 text-center animate__animated animate__fadeInDown">
            <div class="position-relative">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-truck-moving me-3"></i>پنل مدیریت کامیون‌ها
                </h1>
                <p class="lead mb-0">سیستم هوشمند رهگیری و گزارش پلاک کامیون‌ها</p>
            </div>
        </header>

        <!-- دکمه‌ها -->
        <div class="row justify-content-center animate__animated animate__fadeInUp">
            <div class="col-12">
                <div class="glass-card p-4 mb-4">
                    <div class="row g-3 text-center">
                        <!-- دکمه گزارشات -->
                        <div class="col-md-3 col-6">
                            <a href="rep.php" class="btn btn-primary btn-custom w-100">
                                <i class="fas fa-chart-bar feature-icon"></i>گزارشات جامع
                            </a>
                        </div>
                        
                        <!-- دکمه ورود داده -->
                        <div class="col-md-3 col-6">
                            <a href="vared.php" class="btn btn-success btn-custom w-100">
                                <i class="fas fa-edit feature-icon"></i>ورود اطلاعات
                            </a>
                        </div>
                        
                        <!-- دکمه آپلود -->
                        <div class="col-md-3 col-6">
                            <a href="upload.php" class="btn btn-warning btn-custom w-100">
                                <i class="fas fa-upload feature-icon"></i>آپلود فایل ورودی راهبند
                            </a>
                        </div>
                        
						 <!-- دکمه جدید: آپلود فایل خروجی -->
                        <div class="col-md-3 col-6 mt-3">
                            <a href="upload2.php" class="btn btn-export btn-custom w-100">
                                <i class="fas fa-file-export feature-icon"></i>آپلود فایل خروجی
                            </a>
                        </div>
						
                        <!-- دکمه حذف کاربران -->
                        <div class="col-md-3 col-6">
                            <a href="resk.php" class="btn btn-danger btn-custom w-100">
                                <i class="fas fa-trash-alt feature-icon"></i>مدیریت حذف
                            </a>
                        </div>
                        
                        <!-- دکمه تنظیمات -->
                        <div class="col-md-3 col-6 mt-3">
                            <a href="set.php" class="btn btn-settings btn-custom w-100">
                                <i class="fas fa-cog feature-icon"></i>تنظیمات سیستم
                            </a>
                        </div>
                        
                        <!-- دکمه مشاهده نقشه -->
                        <div class="col-md-3 col-6 mt-3">
                            <a href="mapview.php" class="btn btn-map btn-custom w-100">
                                <i class="fas fa-map-marked-alt feature-icon"></i>نقشه رهگیری
                            </a>
                        </div>
                        
                        <!-- دکمه مشاهده خودروهای قبض انبار -->
                        <div class="col-md-3 col-6 mt-3">
                            <a href="ghabz.php" class="btn btn-ghabz btn-custom w-100">
                                <i class="fas fa-clipboard-list feature-icon"></i>خودروهای انبار
                            </a>
                        </div>
                        
                        <!-- دکمه ایجاد کاربر -->
                        <div class="col-md-3 col-6 mt-3">
                            <a href="create_user.php" class="btn btn-user-create btn-custom w-100">
                                <i class="fas fa-user-plus feature-icon"></i>ایجاد کاربر جدید
                            </a>
                        </div>
                        
                       
                    </div>
                </div>
            </div>
        </div>

        <?php
        include_once('ca.php');

        $query_rinfo_count = "SELECT COUNT(*) as total FROM rinfo WHERE id > 0";
        $result_rinfo = mysqli_query($connection, $query_rinfo_count);
        $rinfo_count = mysqli_fetch_assoc($result_rinfo)['total'];

        $query_rkarbar_count = "SELECT COUNT(*) as total FROM rkarbar WHERE id > 0";
        $result_rkarbar = mysqli_query($connection, $query_rkarbar_count);
        $rkarbar_count = mysqli_fetch_assoc($result_rkarbar)['total'];

        mysqli_close($connection);
        ?>

        <!-- اطلاعات آماری -->
        <div class="row mt-4 animate__animated animate__fadeIn">
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 text-center h-100">
                    <i class="fas fa-truck fa-3x mb-3 text-primary"></i>
                    <h3 class="h4">تعداد کامیون‌های راهبند</h3>
                    <p class="display-6 fw-bold"><?php echo $rinfo_count; ?></p>
                    <small class="text-muted">آخرین بروزرسانی: امروز</small>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 text-center h-100">
                    <i class="fas fa-map-marker-alt fa-3x mb-3 text-success"></i>
                    <h3 class="h4">تعداد موقعیت‌های کاربر</h3>
                    <p class="display-6 fw-bold"><?php echo $rkarbar_count; ?></p>
                    <small class="text-muted">آخرین بروزرسانی: امروز</small>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 text-center h-100">
                    <i class="fas fa-bell fa-3x mb-3 text-warning"></i>
                    <h3 class="h4">هشدارها</h3>
                    <p class="display-6 fw-bold">0</p>
                    <small class="text-muted">نیازمند بررسی</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- فونت وزیر برای پشتیبانی از فارسی -->
    <style>
        @font-face {
            font-family: Vazir;
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: Vazir;
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir-Bold.woff2') format('woff2');
            font-weight: bold;
            font-style: normal;
        }
    </style>
</body>
</html>