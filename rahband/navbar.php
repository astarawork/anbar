<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="ax.php">پنل مدیریت</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="ax.php"><i class="fas fa-home me-1"></i>داشبورد</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="create_user.php"><i class="fas fa-user-plus me-1"></i>ایجاد کاربر</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="list_users.php"><i class="fas fa-users me-1"></i>لیست کاربران</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>پروفایل</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>خروج</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>