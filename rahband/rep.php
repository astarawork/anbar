<?php
session_start();
include_once('sar.php'); //ادرس فایل های بوت استرپ و ضروری
include_once('aval.php');
include_once('jdf.php'); //تابع امکانات تاریخ شمسی
include_once('ca.php'); //پایگاه داده

function checkPelakInRghabz($connection, $pelak) {
    $query = "SELECT id FROM rghabz WHERE pelak = '$pelak' AND act = 1 LIMIT 1";
    $result = mysqli_query($connection, $query);
    return (mysqli_num_rows($result) > 0);
}

if(isset($_GET['renew_pelak']) && !empty($_GET['renew_pelak'])) {
    $pelak = mysqli_real_escape_string($connection, $_GET['renew_pelak']);
    if(!checkPelakInRghabz($connection, $pelak)) {
        $time = time();
        $query = "INSERT INTO rghabz (pelak, act, zaman) VALUES ('$pelak', 1, $time)";
        mysqli_query($connection, $query) or die(mysqli_error($connection));
        $success_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            پلاک با موفقیت تمدید شد.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارشات پلاک ها</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Vazir', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header { background-color: #0d6efd; color: white; padding: 20px 0; margin-bottom: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .report-container { background-color: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 30px; }
        .table-custom { margin-top: 20px; }
        .table-custom th { background-color: #2c3e50; color: white; text-align: center; vertical-align: middle; font-weight: 600; position: relative; cursor: pointer;}
        .table-custom td { vertical-align: middle; text-align: center; user-select: text; }
        .badge-expired { background-color: #dc3545; font-size: 0.85rem; }
        .badge-attached { background-color: #28a745; font-size: 0.85rem; }
        .badge-not-attached { background-color: #6c757d; font-size: 0.85rem; }
        .badge-released { background-color: #ffc107; color: #212529; font-size: 0.85rem; }
        .badge-valid { background-color: #28a745; font-size: 0.85rem; }
        .status-cell { font-weight: 600; }
        .no-data { color: #6c757d; font-style: italic; }
        .pelak-link { color: #0d6efd; cursor: pointer; text-decoration: underline; }
        .pelak-link:hover { color: #0b5ed7; }
        .edit-btn { background-color: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 5px; font-size: 0.8rem; cursor: pointer; transition: all 0.3s; }
        .edit-btn:hover { background-color: #138496; color: white; transform: scale(1.05); }
        .renew-btn { background-color: #fd7e14; color: white; border: none; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer; transition: all 0.3s;}
        .renew-btn:hover { background-color: #e36209; transform: scale(1.05); box-shadow: 0 0 5px rgba(0,0,0,0.2);}
        #map-modal .modal-dialog { max-width: 90%; height: 90vh;}
        #map-modal .modal-content { height: 100%;}
        #map-container { height: 100%; min-height: 500px;}
        .copy-column-header, .copy-exit-header { position: relative; padding-right: 25px !important;}
        .copy-column-header::after, .copy-exit-header::after { content: "⧉"; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); opacity: 0.7; transition: opacity 0.2s; cursor: pointer;}
        .copy-column-header:hover::after, .copy-exit-header:hover::after { opacity: 1;}
        .column-highlight { background-color: rgba(13, 110, 253, 0.2) !important; transition: background-color 0.3s; }
        .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 1100;}
        @media (max-width: 768px) {
            .table-responsive { overflow-x: auto;}
            #map-modal .modal-dialog { max-width: 100%; height: 80vh; margin: 10px;}
            .table-custom th { font-size: 0.9rem; padding: 8px;}
            .renew-btn { margin-top: 5px; display: block; width: 100%;}
        }
        @font-face { font-family: Vazir; src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir.woff2') format('woff2');}
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-car me-2"></i>گزارشات پلاک های محوطه</h1>
            </div>
            <div class="col-md-4 text-start">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?php echo tr_num(jdate('Y/n/j')); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container">
<?php
if(isset($success_message)) echo $success_message;

// ... (باقی کدهای آماده‌سازی متغیرها هیچ تغییری نکرده و سرجایش است تا پایین جدول)

$queryfodi = mysqli_query($connection,"SELECT * FROM rhas WHERE id=1") or die(mysqli_error());
$countf = mysqli_num_rows($queryfodi);
if($countf>0){
    while($fodstk = mysqli_fetch_array($queryfodi)) { 
        $zamansys = $fodstk['zamansys'];    
    }
}
$tarom= jdate('Y/m/d H:i:s', $zamansys);

$queryfodi = mysqli_query($connection,"SELECT * FROM rinfo WHERE id>0") or die(mysqli_error());
$countf = mysqli_num_rows($queryfodi);
if($countf>0){
    while($fodstk = mysqli_fetch_array($queryfodi)) { 
        $info[] = $fodstk['pelak'];    
    }
}

$queryfodi = mysqli_query($connection,"SELECT * FROM rhas WHERE id=1") or die(mysqli_error());
$countf = mysqli_num_rows($queryfodi);
if($countf>0){
    while($fodstk = mysqli_fetch_array($queryfodi)) { 
        $rhas = $fodstk['has'];    
    }
}

$rib = $rhas * 3600;
$vib = time();
$ha = $vib - $rib;
$time = date('H:i:s', $ha);

$pelak_coordinates = array();
$released_status = array();
$zaman_karbar = array();
$rkarbar_ids = array();

$queryfodi2 = mysqli_query($connection,"SELECT * FROM rkarbar WHERE id>0") or die(mysqli_error());
$countf2 = mysqli_num_rows($queryfodi2);
if($countf2>0){
    while($fodstk2 = mysqli_fetch_array($queryfodi2)) { 
        $karbar[] = $fodstk2['pelak'];
        $zaman_karbar[$fodstk2['pelak']] = $fodstk2['zaman'];
        $rkarbar_ids[$fodstk2['pelak']] = $fodstk2['id'];
        if(!empty($fodstk2['latitude']) && !empty($fodstk2['longitude'])) {
            $pelak_coordinates[$fodstk2['pelak']] = array(
                'lat' => $fodstk2['latitude'],
                'lng' => $fodstk2['longitude']
            );
        }
        $released_status[$fodstk2['pelak']] = ($fodstk2['raha'] == 1) ? true : false;
    }
}

if (empty($karbar)) { $karbar[] = 'NOwork'; }
if (empty($info)) { $info[] = 'NO'; }
$tekrari = array_intersect($info, $karbar);
$uinfo = array_diff($info, $karbar);
$ukarbar = array_diff($karbar, $info);

$array1 = array_merge($tekrari);
$array2 = array_merge($uinfo);
$array3 = array_merge($ukarbar);
$koliat=count($info);

$max_length = max(count($array1), count($array2), count($array3));
$total_pelak = $max_length;
$verified_pelak = count($array1);
$expired_pelak = 0;
$released_count = count(array_filter($released_status));

if(isset($array1)) {
    foreach($array1 as $pelak) {
        $query = mysqli_query($connection,"SELECT zaman FROM rinfo WHERE pelak='$pelak'");
        if(mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            $ekht = time() - $row['zaman'];
            if($ekht > $rib) $expired++;
        }
    }
}
?>

    <div class="row mb-4">
        <!-- ... کارت‌های آمار ... (تغییر نکرده) ... -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-car me-2"></i>تعداد پلاک ها</h5>
                    <p class="card-text display-4"><?php echo $koliat; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-check-circle me-2"></i>پلاک های تایید شده</h5>
                    <p class="card-text display-4"><?php echo count($array1); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>پلاک های منقضی</h5>
                    <p class="card-text display-4"><?php echo $expired; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-check-circle me-2"></i>رهاشده ها</h5>
                    <p class="card-text display-4"><?php echo $released_count; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo "زمان بارگذاری اطلاعات راهبند : ".$tarom; ?>
    
    <div class="report-container">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-custom" id="pelak-table">
                <thead>
                    <tr>
                        <th width="8%" class="copy-column-header"><i class="fas fa-check-circle me-1"></i> رهاشده ها</th>
                        <th width="12%" class="copy-column-header"><i class="fas fa-clock me-1"></i> زمان ثبت</th>
                        <!-- ستونی که اضافه شد: زمان خروج -->
                        <th width="14%" class="copy-exit-header"><i class="fas fa-door-open me-1"></i> زمان خروج</th>
                        <th width="16%" class="copy-status-header"><i class="fas fa-file-invoice me-1"></i> وضعیت قبض انبار</th>
                        <th width="12%" class="copy-column-header"><i class="fas fa-tag me-1"></i> وضعیت الصاقیه</th>
                        <th width="12%" class="copy-column-header"><i class="fas fa-eye me-1"></i> موقعیت</th>
                        <th width="12%" class="copy-column-header"><i class="fas fa-check-circle me-1"></i> موجود در هر دو سیستم</th>
                        <th width="12%" class="copy-column-header"><i class="fas fa-database me-1"></i> موجود فقط در سیستم راهبند</th>
                        <th width="12%" class="copy-column-header"><i class="fas fa-eye me-1"></i> مشاهده شده در محوطه</th>
                        <th width="4%"><i class="fas fa-edit me-1"></i> عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // تابع زمان خروج یک پلاک
                function getPelakExitJalali($connection, $pelak) {
                    $q = mysqli_query($connection,"SELECT zaman FROM rinfo2 WHERE pelak='$pelak' ORDER BY zaman DESC LIMIT 1");
                    if(mysqli_num_rows($q)) {
                        $row = mysqli_fetch_assoc($q);
                        return tr_num(jdate('Y/m/d - H:i:s', $row['zaman']));
                    }
                    return '<span class="no-data">خارج نشده</span>';
                }
                ?>
                <?php foreach($array1 as $pelak): ?>
                    <tr>
                        <td class="copy-column">
                            <?php if(isset($released_status[$pelak]) && $released_status[$pelak]): ?>
                                <span class="badge badge-released"><i class="fas fa-check-circle me-1"></i> رها شده</span>
                            <?php else: ?>
                                <span class="no-data">--</span>
                            <?php endif; ?>
                        </td>
                        <td class="copy-column">
                            <?php 
                                if(isset($zaman_karbar[$pelak])) {
                                    echo tr_num(jdate('H:i:s', $zaman_karbar[$pelak]));
                                } else {
                                    echo '<span class="no-data">--</span>';
                                }
                            ?>
                        </td>
                        <!--  ستون زمان خروج -->
                        <td class="copy-exit-column">
                            <?= getPelakExitJalali($connection, $pelak); ?>
                        </td>
                        <td class="status-cell copy-status-column">
                            <?php
                            $queryfodi = mysqli_query($connection,"SELECT * FROM rinfo WHERE pelak='$pelak'") or die(mysqli_error());
                            $countf = mysqli_num_rows($queryfodi);
                            if($countf>0){
                                while($fodstk = mysqli_fetch_array($queryfodi)) { 
                                    $zaman = $fodstk['zaman'];    
                                }
                                $ekht = time() - $zaman;
                                $ehkch = floor($ekht/3600);
                                echo '<span class="copy-text">' . $ehkch . ' ساعت </span>';
                                if($ekht > $rib){
                                    echo '<span class="badge badge-expired"><i class="fas fa-exclamation-triangle me-1"></i>منقضی</span>';
                                    $is_in_rghabz = checkPelakInRghabz($connection, $pelak);
                                    if(!$is_in_rghabz) {
                                        echo '<button class="renew-btn" onclick="renewPelak(\''.$pelak.'\')">
                                              <i class="fas fa-redo me-1"></i>ثبت دائم
                                              </button>';
                                    }
                                } else {
                                    echo '<span class="badge badge-valid"><i class="fas fa-check-circle me-1"></i>معتبر</span>';
                                }
                            }
                            ?>
                        </td>
                        <td class="copy-column">
                            <?php
                            $queryfodi = mysqli_query($connection,"SELECT * FROM rkarbar WHERE pelak='$pelak'") or die(mysqli_error());
                            $countf = mysqli_num_rows($queryfodi);
                            if($countf>0){
                                while($fodstk = mysqli_fetch_array($queryfodi)) { 
                                    $barchasb = $fodstk['barchasb'];    
                                }
                                if($barchasb == 1){
                                    echo '<span class="badge badge-attached"><i class="fas fa-tag me-1"></i>الصاق شده</span>';
                                } else {
                                    echo '<span class="badge badge-not-attached"><i class="fas fa-times-circle me-1"></i>الصاق نشده</span>';
                                }
                            }
                            ?>
                        </td>
                        <td class="copy-column">
                            <?php
                            $queryfodi = mysqli_query($connection,"SELECT * FROM rkarbar WHERE pelak='$pelak'") or die(mysqli_error());
                            $countf = mysqli_num_rows($queryfodi);
                            if($countf>0){
                                while($fodstk = mysqli_fetch_array($queryfodi)) { 
                                    $makan = $fodstk['makan'];    
                                }
                               echo $makan;
                            }
                            ?>
                        </td>
                        <td class="copy-column">
                            <?php if(isset($pelak_coordinates[$pelak])): ?>
                                <span class="pelak-link" onclick="showOnMap('<?php echo $pelak; ?>', <?php echo $pelak_coordinates[$pelak]['lat']; ?>, <?php echo $pelak_coordinates[$pelak]['lng']; ?>)">
                                    <?php echo $pelak; ?>
                                </span>
                            <?php else: ?>
                                <?php echo $pelak; ?>
                            <?php endif; ?>
                        </td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td><?php if(isset($rkarbar_ids[$pelak])): ?>
                            <a href="edit_rkarbar.php?id=<?php echo $rkarbar_ids[$pelak]; ?>" class="edit-btn" title="ویرایش"><i class="fas fa-edit"></i></a>
                        <?php else: ?><span class="no-data">--</span>
                        <?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                <!-- سایر foreach های array2 و array3 عوض نمی‌شوند... -->
                <!-- ... (باقی foreach ها دقیقاً مثل قبل) ... -->
                <?php foreach($array2 as $pelak): ?>
                    <tr>
                        <td class="copy-column"><?php if(isset($released_status[$pelak]) && $released_status[$pelak]): ?><span class="badge badge-released"><i class="fas fa-check-circle me-1"></i> رها شده</span><?php else: ?><span class="no-data">--</span><?php endif; ?></td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <!-- ستونی که اضافه شد: زمان خروج -->
                        <td class="copy-exit-column"><?= getPelakExitJalali($connection, $pelak); ?></td>
                        <td class="status-cell copy-status-column">
                        <?php
                        $queryfodi = mysqli_query($connection,"SELECT * FROM rinfo WHERE pelak='$pelak'") or die(mysqli_error());
                        $countf = mysqli_num_rows($queryfodi);
                        if($countf>0){
                            while($fodstk = mysqli_fetch_array($queryfodi)) { 
                                $zaman = $fodstk['zaman'];    
                            }
                            $ekht = time() - $zaman;
                            $ehkch = floor($ekht/3600);
                            echo '<span class="copy-text">' . $ehkch . ' ساعت </span>';
                            if($ekht > $rib){
                                echo '<span class="badge badge-expired"><i class="fas fa-exclamation-triangle me-1"></i>منقضی</span>';
                                $is_in_rghabz = checkPelakInRghabz($connection, $pelak);
                                if(!$is_in_rghabz) {
                                    echo '<button class="renew-btn" onclick="renewPelak(\''.$pelak.'\')"><i class="fas fa-redo me-1"></i>تمدید</button>';
                                }
                            } else {
                                echo '<span class="badge badge-valid"><i class="fas fa-check-circle me-1"></i>معتبر</span>';
                            }
                        }
                        ?>
                        </td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td class="copy-column">
                            <?php if(isset($pelak_coordinates[$pelak])): ?>
                                <span class="pelak-link" onclick="showOnMap('<?php echo $pelak; ?>', <?php echo $pelak_coordinates[$pelak]['lat']; ?>, <?php echo $pelak_coordinates[$pelak]['lng']; ?>)">
                                    <?php echo $pelak; ?>
                                </span>
                            <?php else: ?>
                                <?php echo $pelak; ?>
                            <?php endif; ?>
                        </td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td><?php if(isset($rkarbar_ids[$pelak])): ?>
                            <a href="edit_rkarbar.php?id=<?php echo $rkarbar_ids[$pelak]; ?>" class="edit-btn" title="ویرایش"><i class="fas fa-edit"></i></a>
                        <?php else: ?><span class="no-data">--</span>
                        <?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach($array3 as $pelak): ?>
                    <tr>
                        <td class="copy-column"><?php if(isset($released_status[$pelak]) && $released_status[$pelak]): ?><span class="badge badge-released"><i class="fas fa-check-circle me-1"></i> رها شده</span><?php else: ?><span class="no-data">--</span><?php endif; ?></td>
                        <td class="copy-column"><?php if(isset($zaman_karbar[$pelak])) { echo tr_num(jdate('H:i:s', $zaman_karbar[$pelak])); } else { echo '<span class="no-data">--</span>'; } ?></td>
                        <!-- ستونی که اضافه شد: زمان خروج -->
                        <td class="copy-exit-column"><?= getPelakExitJalali($connection, $pelak); ?></td>
                        <td class="status-cell"><span class="no-data">--</span></td>
                        <td class="copy-column">
                        <?php
                        $queryfodi = mysqli_query($connection,"SELECT * FROM rkarbar WHERE pelak='$pelak'") or die(mysqli_error());
                        $countf = mysqli_num_rows($queryfodi);
                        if($countf>0){
                            while($fodstk = mysqli_fetch_array($queryfodi)) { $barchasb = $fodstk['barchasb']; }
                            if($barchasb == 1){
                                echo '<span class="badge badge-attached"><i class="fas fa-tag me-1"></i>الصاق شده</span>';
                            } else {
                                echo '<span class="badge badge-not-attached"><i class="fas fa-times-circle me-1"></i>الصاق نشده</span>';
                            }
                        }
                        ?>
                        </td>
                        <td class="copy-column">
                        <?php
                        $queryfodi = mysqli_query($connection,"SELECT * FROM rkarbar WHERE pelak='$pelak'") or die(mysqli_error());
                        $countf = mysqli_num_rows($queryfodi);
                        if($countf>0){
                            while($fodstk = mysqli_fetch_array($queryfodi)) { $makan = $fodstk['makan']; }
                           echo $makan;
                        }
                        ?>
                        </td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td class="copy-column"><span class="no-data">--</span></td>
                        <td class="copy-column">
                            <?php if(isset($pelak_coordinates[$pelak])): ?>
                                <span class="pelak-link" onclick="showOnMap('<?php echo $pelak; ?>', <?php echo $pelak_coordinates[$pelak]['lat']; ?>, <?php echo $pelak_coordinates[$pelak]['lng']; ?>)">
                                    <?php echo $pelak; ?>
                                </span>
                            <?php else: ?>
                                <?php echo $pelak; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php if(isset($rkarbar_ids[$pelak])): ?>
                            <a href="edit_rkarbar.php?id=<?php echo $rkarbar_ids[$pelak]; ?>" class="edit-btn" title="ویرایش"><i class="fas fa-edit"></i></a>
                        <?php else: ?><span class="no-data">--</span>
                        <?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مودال نمایش نقشه (تغییر ندادم) -->
<div class="modal fade" id="map-modal" tabindex="-1" aria-labelledby="map-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="map-modal-label">موقعیت پلاک: <span id="modal-pelak"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="map-container"></div>
            </div>
            <div class="modal-footer">
                <div id="location-info" class="text-start"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
<div class="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function showOnMap(pelak, lat, lng) {
    document.getElementById('modal-pelak').textContent = pelak;
    document.getElementById('location-info').innerHTML = `<strong>عرض جغرافیایی:</strong> ${lat.toFixed(6)} <br><strong>طول جغرافیایی:</strong> ${lng.toFixed(6)}`;
    const mapModal = new bootstrap.Modal(document.getElementById('map-modal'));
    mapModal.show();
    setTimeout(() => {
        if (window.map) window.map.remove();
        window.map = L.map('map-container').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(window.map);
        const marker = L.marker([lat, lng]).addTo(window.map).bindPopup(`<b>پلاک:</b> ${pelak}<br><b>مختصات:</b> ${lat.toFixed(6)}, ${lng.toFixed(6)}`).openPopup();
    }, 500);
}
// تابع کپی ستون‌ها (index = ایندکس تیتر) 
function copyColumn(columnIndex) {
    const table = document.getElementById('pelak-table');
    const rows = table.querySelectorAll('tbody tr');
    let columnContent = '';
    rows.forEach(row => {
        const cells = row.cells;
        if (cells.length > columnIndex) {
            const cell = cells[columnIndex].cloneNode(true);
            if (columnIndex === 3) { // وضعیت قبض انبار (copy-status)
                const copyText = cell.querySelector('.copy-text');
                const badges = cell.querySelectorAll('.badge');
                let statusText = copyText ? copyText.textContent.trim() : '';
                badges.forEach(badge => { statusText += ' ' + badge.textContent.trim(); });
                columnContent += statusText + '\n';
            }
            else {
                const badges = cell.querySelectorAll('.badge'); badges.forEach(badge => { badge.parentNode.replaceChild(document.createTextNode(badge.textContent.trim()), badge); });
                const links = cell.querySelectorAll('.pelak-link'); links.forEach(link => { link.parentNode.replaceChild(document.createTextNode(link.textContent), link); });
                columnContent += cell.textContent.trim() + '\n';
            }
        }
    });
    navigator.clipboard.writeText(columnContent.trim()).then(() => {
        const headerCells = table.querySelectorAll('thead th');
        if (headerCells[columnIndex]) { headerCells[columnIndex].classList.add('column-highlight'); setTimeout(() => { headerCells[columnIndex].classList.remove('column-highlight'); }, 1000);}
        showToast('ستون با موفقیت کپی شد');
    }).catch(err => { showToast('خطا در کپی کردن', 'error'); });
}
function renewPelak(pelak) {
    if(confirm('آیا از ثبت دایم قبض انبار برای پلاک ' + pelak + ' اطمینان دارید؟')) {
        window.location.href = '?renew_pelak=' + encodeURIComponent(pelak);
    }
}
function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container');
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast show'; toast.setAttribute('role', 'alert'); toast.setAttribute('aria-live', 'assertive'); toast.setAttribute('aria-atomic', 'true');
    const bgClass = type === 'error' ? 'bg-danger' : 'bg-primary';
    toast.innerHTML = `<div class="toast-header ${bgClass} text-white"><strong class="me-auto">اطلاع رسانی</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">${message}</div>`;
    toastContainer.appendChild(toast);
    setTimeout(() => { const toastElement = document.getElementById(toastId); if (toastElement) { toastElement.classList.remove('show'); setTimeout(() => { toastElement.remove(); }, 300); } }, 3000);
}
// رویداد کلیک به تیترهای قابل کپی
document.addEventListener('DOMContentLoaded', function() {
    const copyColumnHeaders = document.querySelectorAll('.copy-column-header, .copy-status-header, .copy-exit-header');
    copyColumnHeaders.forEach((header, index) => { header.addEventListener('click', function(e) { e.stopPropagation(); copyColumn(index); }); });
});
</script>
<?php if (file_exists("error_log")) { unlink("error_log"); } mysqli_close($connection); include_once('sb/foot.php'); ?>
</body>
</html>
