<?php
session_start();
include_once('sar.php');
include_once('aval.php');
include_once('jdf.php');
include_once('ca.php'); // اتصال دیتابیس

// ***********************
// بروزرسانی vaziat پلاک بر اساس دکمه
if(isset($_GET['set_vaziat']) && isset($_GET['rid']) && is_numeric($_GET['rid'])) {
    $rid = intval($_GET['rid']); // id رکورد rghabz
    $vaziat = intval($_GET['set_vaziat']); // مقدار تعیین شده (1 یا 2)
    // حتما فقط روی رکوردهای فعال و وضعیت 0 اجازه بده؛ امنیت!
    $q = "UPDATE rghabz SET vaziat = $vaziat WHERE id = $rid AND act=1 AND vaziat=0 LIMIT 1";
    if(mysqli_query($connection, $q)) {
        $success_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        وضعیت رکورد با موفقیت بروزرسانی شد!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
// ***********************

function checkPelakInRghabz($connection, $pelak) {
    $query = "SELECT id FROM rghabz WHERE pelak = '$pelak' AND act = 1 LIMIT 1";
    $result = mysqli_query($connection, $query);
    return (mysqli_num_rows($result) > 0);
}

if(isset($_GET['renew_pelak']) && !empty($_GET['renew_pelak'])) {
    $pelak = mysqli_real_escape_string($connection, $_GET['renew_pelak']);
    if(!checkPelakInRghabz($connection, $pelak)) {
        $time = time();
        $query = "INSERT INTO rghabz (pelak, act, zaman, vaziat) VALUES ('$pelak', 1, $time, 0)";
        mysqli_query($connection, $query) or die(mysqli_error($connection));
        $success_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            پلاک با موفقیت تمدید شد.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
?>
