<?php
include_once('ca.php');

header('Content-Type: application/json');

if(isset($_POST['pelak']) && !empty($_POST['pelak'])) {
    $pelak = strtolower(trim($_POST['pelak']));
    
    $stmt = mysqli_prepare($connection, "SELECT * FROM rkarbar WHERE pelak = ?");
    mysqli_stmt_bind_param($stmt, "s", $pelak);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    echo json_encode([
        'isDuplicate' => mysqli_stmt_num_rows($stmt) > 0
    ]);
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'isDuplicate' => false
    ]);
}
?>