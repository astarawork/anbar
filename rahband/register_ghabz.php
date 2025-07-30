<?php
session_start();
include_once('sar.php');
include_once('aval.php');
include_once('jdf.php');
include_once('ca.php');

// تنظیم header برای JSON
header('Content-Type: application/json');

// بررسی درخواست POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر']);
    exit;
}

// دریافت پلاک
$pelak = isset($_POST['pelak']) ? trim($_POST['pelak']) : '';

if (empty($pelak)) {
    echo json_encode(['success' => false, 'message' => 'پلاک وارد نشده']);
    exit;
}

try {
    // بررسی وجود قبض فعال برای این پلاک
    $check_query = "SELECT id FROM rghabz WHERE pelak = ? AND act = 1";
    $stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $pelak);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'این پلاک قبلاً قبض شده است']);
        exit;
    }
    
    // ثبت قبض جدید
    $current_time = time();
    $insert_query = "INSERT INTO rghabz (pelak, act, zaman) VALUES (?, 1, ?)";
    $stmt = mysqli_prepare($connection, $insert_query);
    mysqli_stmt_bind_param($stmt, "si", $pelak, $current_time);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true, 
            'message' => 'قبض با موفقیت ثبت شد',
            'time' => tr_num(jdate('Y/n/j H:i:s', $current_time))
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطا در ثبت قبض']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطا در پردازش درخواست']);
}

mysqli_close($connection);
?>