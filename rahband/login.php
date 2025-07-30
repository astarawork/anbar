<?php
session_start();



// اتصال به دیتابیس
include_once('sar.php');
include_once('aval.php');
include_once('jdf.php');
include_once('ca.php');


$error = '';
$username = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // اعتبارسنجی ورودی‌ها
    if(empty($username) || empty($password)) {
        $error = "لطفا نام کاربری و رمز عبور را وارد کنید";
    } else {
        // جستجوی کاربر در دیتابیس
        $sql = "SELECT id, user, pass FROM ruser WHERE user = ?";
        if($stmt = mysqli_prepare($connection, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                mysqli_stmt_fetch($stmt);
                
                if(password_verify($password, $hashed_password)) {
                    // رمز عبور صحیح است، session ایجاد می‌کنیم
                    session_start();
                    
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $id;
                    $_SESSION['username'] = $username;
                    
               
                    header("Location: vared.php");
                    exit;
                } else {
                    $error = "رمز عبور وارد شده صحیح نیست";
                }
            } else {
                $error = "نام کاربری یافت نشد";
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
    <title>ورود به سیستم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-title {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
        }
        .form-control {
            padding: 12px;
            margin-bottom: 15px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            font-weight: 600;
            background-color: #0d6efd;
            border: none;
        }
        .btn-login:hover {
            background-color: #0b5ed7;
        }
        .input-group-text {
            background-color: #e9ecef;
            border-right: none;
        }
        .form-floating>label {
            right: auto;
            left: 12px;
        }
        .form-floating>.form-control, .form-floating>.form-select {
            padding-right: 12px;
            padding-left: 3.5rem;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="login-title"><i class="fas fa-user-shield me-2"></i>ورود به سیستم</h2>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="نام کاربری" value="<?php echo htmlspecialchars($username); ?>" required>
                    <label for="username"><i class="fas fa-user me-2"></i>نام کاربری</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="رمز عبور" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>رمز عبور</label>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>ورود
                    </button>
                </div>
            </form>
            
            <div class="login-footer mt-3">
                <p>سیستم ثبت عملیات پلاک خودروها</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>