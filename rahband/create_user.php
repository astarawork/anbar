<?php
session_start();



include_once('sar.php');
include_once('ca.php');

$error = '';
$success = '';
$username = '';
$color = '#007bff';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $color = trim($_POST['color']);
    $role = trim($_POST['role']);
    
    // اعتبارسنجی ورودی‌ها
    if(empty($username) || empty($password)) {
        $error = "لطفا نام کاربری و رمز عبور را وارد کنید";
    } elseif(strlen($password) < 6) {
        $error = "رمز عبور باید حداقل 6 کاراکتر باشد";
    } elseif($password !== $confirm_password) {
        $error = "رمز عبور و تکرار آن مطابقت ندارند";
    } else {
        // بررسی تکراری نبودن نام کاربری
        $sql = "SELECT id FROM ruser WHERE user = ?";
        if($stmt = mysqli_prepare($connection, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) > 0) {
                $error = "این نام کاربری قبلا ثبت شده است";
            } else {
                // ایجاد کاربر جدید
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO ruser (user, pass, color, role) VALUES (?, ?, ?, ?)";
                
                if($stmt = mysqli_prepare($connection, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $color, $role);
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $success = "کاربر با موفقیت ایجاد شد";
                        $username = '';
                        $color = '#007bff';
                    } else {
                        $error = "خطا در ایجاد کاربر. لطفا مجددا تلاش کنید";
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ایجاد کاربر جدید</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .user-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .user-title {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
        }
        .color-preview {
            width: 30px;
            height: 30px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 10px;
            border: 1px solid #dee2e6;
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <div class="container">
        <div class="user-container">
            <h2 class="user-title"><i class="fas fa-user-plus me-2"></i>ایجاد کاربر جدید</h2>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">نام کاربری</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">رمز عبور</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">تکرار رمز عبور</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="mb-3">
                    <label for="color" class="form-label">رنگ کاربر</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-palette"></i></span>
                        <input type="color" class="form-control form-control-color" id="color" name="color" 
                               value="<?php echo htmlspecialchars($color); ?>" title="رنگ کاربر را انتخاب کنید">
                        <span class="input-group-text">
                            <span class="color-preview" style="background-color: <?php echo htmlspecialchars($color); ?>"></span>
                            <?php echo htmlspecialchars($color); ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">نقش کاربر</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_user" value="user" checked>
                        <label class="form-check-label" for="role_user">
                            کاربر عادی
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_admin" value="admin">
                        <label class="form-check-label" for="role_admin">
                            مدیر سیستم
                        </label>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>ذخیره کاربر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // نمایش رنگ انتخاب شده در پیش‌نمایش
        document.getElementById('color').addEventListener('input', function() {
            document.querySelector('.color-preview').style.backgroundColor = this.value;
            document.querySelector('.color-preview').nextSibling.textContent = this.value;
        });
    </script>
</body>
</html>