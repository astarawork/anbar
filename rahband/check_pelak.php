<?php
include_once('sar.php');
include_once('jdf.php');
include_once('ca.php');

if(isset($_POST['pelak']) && !empty($_POST['pelak'])) {
    $pir = strtolower($_POST['pelak']);
    
    $queryfodi = mysqli_query($connection,"SELECT * FROM rinfo WHERE pelak = '$pir'") or die(mysqli_error());
    $countf = mysqli_num_rows($queryfodi);
    
    if($countf > 0) {
        while($fodstk = mysqli_fetch_array($queryfodi)) { 
            $zaman = $fodstk['zaman'];    
        }
        $ekht = time() - $zaman;
        $ehkch = floor($ekht/3600);
        
        if($ekht > $rib) {
            echo '<div class="text-bg-danger p-3">هشدار!!! قبض انبار '.htmlspecialchars($pir).' منقضی شده است</div>';
        }
        echo '<div class="text-bg-success p-3">این پلاک موجود است: '.htmlspecialchars($pir).'</div>';
    } else {
        echo '<div class="text-bg-danger p-3">این پلاک موجود نیست: '.htmlspecialchars($pir).'</div>';
    }
} else {
    echo '<div class="text-bg-warning p-3">لطفاً پلاک را وارد کنید</div>';
}
?>