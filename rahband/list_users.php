<?php
session_start();
include_once('sar.php');
include_once('ca.php');

// پردازش عملیات حذف
if (isset($_POST['delete_user'])) {
    $id = $_POST['user_id'];
    $sql = "DELETE FROM ruser WHERE id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['message'] = "کاربر با موفقیت حذف شد";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "خطا در حذف کاربر";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: list_users.php");
    exit();
}

// پردازش عملیات ویرایش
if (isset($_POST['edit_user'])) {
    $id = $_POST['user_id'];
    $user = $_POST['user'];
    $color = $_POST['color'];
    $role = $_POST['role'];
    
    $sql = "UPDATE ruser SET user = ?, color = ?, role = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $user, $color, $role, $id);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['message'] = "اطلاعات کاربر با موفقیت به‌روزرسانی شد";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "خطا در به‌روزرسانی اطلاعات کاربر";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: list_users.php");
    exit();
}

// دریافت لیست کاربران
$users = [];
$sql = "SELECT id, user, color, role, created_at FROM ruser ORDER BY created_at DESC";
$result = mysqli_query($connection, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لیست کاربران</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal-rtl .modal-dialog {
            direction: rtl;
            text-align: right;
        }
        .color-preview {
            width: 20px;
            height: 20px;
            display: inline-block;
            border: 1px solid #ddd;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <div class="container mt-4">
        <!-- نمایش پیغام‌ها -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-users me-2"></i>لیست کاربران سیستم
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام کاربری</th>
                                <th>رنگ</th>
                                <th>نقش</th>
                                <th>تاریخ ایجاد</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($user['user']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($user['color']); ?>">
                                        <?php echo htmlspecialchars($user['color']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">مدیر سیستم</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">کاربر عادی</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $user['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- مودال ویرایش -->
                            <div class="modal fade modal-rtl" id="editModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">ویرایش کاربر</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="post" action="list_users.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="user" class="form-label">نام کاربری</label>
                                                    <input type="text" class="form-control" id="user" name="user" value="<?php echo htmlspecialchars($user['user']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="color" class="form-label">رنگ</label>
                                                    <div class="input-group">
                                                        <span class="color-preview" style="background-color: <?php echo htmlspecialchars($user['color']); ?>"></span>
                                                        <input type="color" class="form-control form-control-color" id="color" name="color" value="<?php echo htmlspecialchars($user['color']); ?>" title="رنگ را انتخاب کنید">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">نقش</label>
                                                    <div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="role" id="roleAdmin<?php echo $user['id']; ?>" value="admin" <?php echo ($user['role'] === 'admin') ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="roleAdmin<?php echo $user['id']; ?>">مدیر سیستم</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="role" id="roleUser<?php echo $user['id']; ?>" value="user" <?php echo ($user['role'] !== 'admin') ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="roleUser<?php echo $user['id']; ?>">کاربر عادی</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                                <button type="submit" name="edit_user" class="btn btn-primary">ذخیره تغییرات</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- مودال حذف -->
                            <div class="modal fade modal-rtl" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">حذف کاربر</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="post" action="list_users.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <p>آیا از حذف کاربر "<?php echo htmlspecialchars($user['user']); ?>" مطمئن هستید؟</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                                <button type="submit" name="delete_user" class="btn btn-danger">حذف کاربر</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // به‌روزرسانی پیش‌نمایش رنگ هنگام تغییر
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', function() {
                this.previousElementSibling.style.backgroundColor = this.value;
            });
        });
    </script>
</body>
</html>