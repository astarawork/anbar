```php
<?php
session_start();
include_once('sar.php');   // فایل‌های ضروری و بوت‌استرپ
include_once('aval.php');
include_once('jdf.php');   // توابع تاریخ شمسی
include_once('ca.php');    // اتصال به دیتابیس در $connection

// تابع کمکی برای بررسی وجود پلاک در rghabz
function checkPelakInRghabz($connection, $pelak) {
    $query = "SELECT id FROM rghabz WHERE pelak = '$pelak' AND act = 1 LIMIT 1";
    $result = mysqli_query($connection, $query);
    return (mysqli_num_rows($result) > 0);
}

// === پردازش فرم‌های POST برای افزودن به جدول rаha و elsagh ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // افزودن رکورد به جدول rаha (رها شده)
    if (isset($_POST['add_raha']) && !empty($_POST['pelak_raha'])) {
        $pelak = mysqli_real_escape_string($connection, $_POST['pelak_raha']);
        // مقدار زمان را از rinfo بگیریم یا 0
        $qz = mysqli_query($connection,
            "SELECT zaman FROM rinfo WHERE pelak = '$pelak' ORDER BY zaman DESC LIMIT 1"
        );
        if (mysqli_num_rows($qz) > 0) {
            $zrow = mysqli_fetch_assoc($qz);
            $zaman = intval($zrow['zaman']);
        } else {
            $zaman = 0;
        }
        // درج در tabla rаha
        mysqli_query($connection,
            "INSERT INTO raha (pelak, act, zaman, shomare) 
             VALUES ('$pelak', 1, $zaman, 0)"
        ) or die(mysqli_error($connection));
    }

    // افزودن رکورد به جدول elsagh (الصاقیه)
    if (isset($_POST['add_elsagh']) && !empty($_POST['pelak_elsagh'])) {
        $pelak = mysqli_real_escape_string($connection, $_POST['pelak_elsagh']);
        // مقدار زمان را از rinfo بگیریم یا 0
        $qz = mysqli_query($connection,
            "SELECT zaman FROM rinfo WHERE pelak = '$pelak' ORDER BY zaman DESC LIMIT 1"
        );
        if (mysqli_num_rows($qz) > 0) {
            $zrow = mysqli_fetch_assoc($qz);
            $zaman = intval($zrow['zaman']);
        } else {
            $zaman = 0;
        }
        // درج در tabla elsagh
        mysqli_query($connection,
            "INSERT INTO elsagh (pelak, act, zaman) 
             VALUES ('$pelak', 1, $zaman)"
        ) or die(mysqli_error($connection));
    }
}

// === کد موجود برای تمدید قبض انبار با GET (بدون تغییر) ===
if (isset($_GET['renew_pelak']) && !empty($_GET['renew_pelak'])) {
    $pelak = mysqli_real_escape_string($connection, $_GET['renew_pelak']);
    if (!checkPelakInRghabz($connection, $pelak)) {
        $time = time();
        mysqli_query($connection,
            "INSERT INTO rghabz (pelak, act, zaman, shomare, vaziat) 
             VALUES ('$pelak', 1, $time, 0, 0)"
        ) or die(mysqli_error($connection));
        $success_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            پلاک با موفقیت تمدید شد.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// === بارگذاری تنظیمات و داده‌ها ===
$queryfodi = mysqli_query($connection,"SELECT zamansys, has FROM rhas WHERE id=1") or die(mysqli_error($connection));
$rhasRow = mysqli_fetch_assoc($queryfodi);
$zamansys = intval($rhasRow['zamansys']);
$rhas = intval($rhasRow['has']);
$tarom = jdate('Y/m/d H:i:s', $zamansys);

$rib = $rhas * 3600;   // حساسیت بر حسب ثانیه

// داده‌های rinfo (سیستم)
$info = [];
$q = mysqli_query($connection,"SELECT pelak FROM rinfo") or die(mysqli_error($connection));
while($row = mysqli_fetch_assoc($q)) {
    $info[] = $row['pelak'];
}

// داده‌های rkarbar (کاربر)
$karbar = [];
$zaman_karbar = [];
$rkarbar_ids = [];
$pelak_coordinates = [];
$released_status = [];
$q2 = mysqli_query($connection,"SELECT * FROM rkarbar") or die(mysqli_error($connection));
while($row = mysqli_fetch_assoc($q2)) {
    $pl = $row['pelak'];
    $karbar[] = $pl;
    $zaman_karbar[$pl] = $row['zaman'];
    $rkarbar_ids[$pl] = $row['id'];
    $released_status[$pl] = ($row['raha'] == 1);
    if (!empty($row['latitude']) && !empty($row['longitude'])) {
        $pelak_coordinates[$pl] = [
            'lat' => $row['latitude'],
            'lng' => $row['longitude']
        ];
    }
}

// تفکیک آرایه‌ها
if (empty($karbar)) $karbar[] = 'NOwork';
if (empty($info)) $info[] = 'NO';
$tekrari   = array_values(array_intersect($info, $karbar));  // موجود در هر دو
$uinfo     = array_values(array_diff($info, $karbar));       // فقط سیستم
$ukarbar   = array_values(array_diff($karbar, $info));       // فقط کاربر

$koliat          = count($info);
$verified_pelak  = count($tekrari);
$expired         = 0;
$released_count  = count(array_filter($released_status));

// محاسبه پلاک‌های منقضی
foreach ($tekrari as $pelak) {
    $qr = mysqli_query($connection,"SELECT zaman FROM rinfo WHERE pelak='$pelak' ORDER BY zaman DESC LIMIT 1");
    if (mysqli_num_rows($qr)>0) {
        $zr = mysqli_fetch_assoc($qr);
        $ekht = time() - intval($zr['zaman']);
        if ($ekht > $rib) $expired++;
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Vazir', sans-serif; }
        .renew-btn { background-color: #fd7e14; color: white; border: none; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer; transition: all 0.3s;}
        .renew-btn:hover { background-color: #e36209; transform: scale(1.05); box-shadow: 0 0 5px rgba(0,0,0,0.2);}
        /* … سایر استایل‌ها بدون تغییر … */
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
  <?php if(isset($success_message)) echo $success_message; ?>

  <div class="row mb-4">
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
          <p class="card-text display-4"><?php echo $verified_pelak; ?></p>
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
            <th width="8%" class="copy-column-header"><i class="fas fa-check-circle me-1"></i>رهاشده ها</th>
            <th width="12%" class="copy-column-header"><i class="fas fa-clock me-1"></i>زمان ثبت</th>
            <th width="14%" class="copy-exit-header"><i class="fas fa-door-open me-1"></i>زمان خروج</th>
            <th width="16%" class="copy-status-header"><i class="fas fa-file-invoice me-1"></i>وضعیت قبض انبار</th>
            <th width="12%" class="copy-column-header"><i class="fas fa-tag me-1"></i>وضعیت الصاقیه</th>
            <th width="12%" class="copy-column-header"><i class="fas fa-eye me-1"></i>موقعیت</th>
            <th width="12%" class="copy-column-header"><i class="fas fa-check-circle me-1"></i>موجود در هر دو سیستم</th>
            <th width="12%" class="copy-column-header"><i class="fas fa-database me-1"></i>موجود فقط در سیستم راهبند</th>
            <th width="12%" class="copy-column-header"><i class="fas fa-eye me-1"></i>مشاهده شده در محوطه</th>
            <th width="4%"><i class="fas fa-edit me-1"></i>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // تابع نمایش زمان خروج به شمسی
          function getPelakExitJalali($connection, $pelak) {
              $q = mysqli_query($connection,
                  "SELECT zaman FROM rinfo2 WHERE pelak='$pelak' ORDER BY zaman DESC LIMIT 1"
              );
              if (mysqli_num_rows($q)) {
                  $r = mysqli_fetch_assoc($q);
                  return tr_num(jdate('Y/m/d - H:i:s', $r['zaman']));
              }
              return '<span class="no-data">خارج نشده</span>';
          }

          // نمایش ردیف‌ها برای array1 ، array2 و array3
          $allArrays = [ $tekrari, $uinfo, $ukarbar ];
          foreach ($allArrays as $arr) {
            foreach ($arr as $pelak):
          ?>
          <tr>
            <!-- ستون رهاشده ها -->
            <td class="copy-column">
              <?php
                // اگر کاربر راها کرده باشد
                if (!empty($released_status[$pelak]) && $released_status[$pelak]) {
                    // بررسی وجود در جدول raha
                    $qRa = mysqli_query($connection,
                        "SELECT act FROM raha WHERE pelak='$pelak' ORDER BY id DESC LIMIT 1"
                    );
                    $canInsert = true;
                    if (mysqli_num_rows($qRa) > 0) {
                        $rRa = mysqli_fetch_assoc($qRa);
                        if (intval($rRa['act']) === 1) {
                            echo '<span class="badge badge-released"><i class="fas fa-check-circle me-1"></i>رها شده</span>';
                            $canInsert = false;
                        }
                    }
                    if ($canInsert) {
                        echo '<form method="post" style="display:inline">';
                        echo '<input type="hidden" name="pelak_raha" value="'.htmlspecialchars($pelak).'">';
                        echo '<button type="submit" name="add_raha" class="renew-btn"><i class="fas fa-plus me-1"></i>ثبت رها</button>';
                        echo '</form>';
                    }
                } else {
                    echo '<span class="no-data">--</span>';
                }
              ?>
            </td>
            <!-- ستون زمان ثبت کاربر -->
            <td class="copy-column">
              <?php
                if (isset($zaman_karbar[$pelak]) && $zaman_karbar[$pelak]) {
                    echo tr_num(jdate('H:i:s', $zaman_karbar[$pelak]));
                } else {
                    echo '<span class="no-data">--</span>';
                }
              ?>
            </td>
            <!-- ستون زمان خروج -->
            <td class="copy-exit-column">
              <?= getPelakExitJalali($connection, $pelak); ?>
            </td>
            <!-- ستون وضعیت قبض انبار -->
            <td class="status-cell copy-status-column">
              <?php
                $q3 = mysqli_query($connection,
                    "SELECT zaman FROM rinfo WHERE pelak='$pelak' ORDER BY zaman DESC LIMIT 1"
                );
                if (mysqli_num_rows($q3)) {
                    $r3 = mysqli_fetch_assoc($q3);
                    $ekht = time() - intval($r3['zaman']);
                    $hours = floor($ekht/3600);
                    echo '<span class="copy-text">'.$hours.' ساعت </span>';
                    if ($ekht > $rib) {
                        echo '<span class="badge badge-expired"><i class="fas fa-exclamation-triangle me-1"></i>منقضی</span>';
                        if (!checkPelakInRghabz($connection, $pelak)) {
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
            <!-- ستون وضعیت الصاقیه -->
            <td class="copy-column">
              <?php
                // وضعیت بarchasb در rkarbar
                $q4 = mysqli_query($connection,"SELECT barchasb FROM rkarbar WHERE pelak='$pelak' LIMIT 1");
                $barchasb = 0;
                if (mysqli_num_rows($q4)) {
                    $b4 = mysqli_fetch_assoc($q4);
                    $barchasb = intval($b4['barchasb']);
                }
                if ($barchasb === 1) {
                    // بررسی وجود در tabel elsagh
                    $qEs = mysqli_query($connection,
                        "SELECT act FROM elsagh WHERE pelak='$pelak' ORDER BY id DESC LIMIT 1"
                    );
                    $canIns2 = true;
                    if (mysqli_num_rows($qEs) > 0) {
                        $rEs = mysqli_fetch_assoc($qEs);
                        if (intval($rEs['act']) === 1) {
                            echo '<span class="badge badge-attached"><i class="fas fa-tag me-1"></i>الصاق شده</span>';
                            $canIns2 = false;
                        }
                    }
                    if ($canIns2) {
                        echo '<form method="post" style="display:inline">';
                        echo '<input type="hidden" name="pelak_elsagh" value="'.htmlspecialchars($pelak).'">';
                        echo '<button type="submit" name="add_elsagh" class="renew-btn"><i class="fas fa-plus me-1"></i>ثبت الصاق</button>';
                        echo '</form>';
                    }
                } else {
                    echo '<span class="badge badge-not-attached"><i class="fas fa-times-circle me-1"></i>الصاق نشده</span>';
                }
              ?>
            </td>
            <!-- ستون موقعیت -->
            <td class="copy-column">
              <?php if (isset($pelak_coordinates[$pelak])): ?>
                <span class="pelak-link"
                      onclick="showOnMap('<?php echo $pelak;?>',
                                        <?php echo $pelak_coordinates[$pelak]['lat'];?>,
                                        <?php echo $pelak_coordinates[$pelak]['lng'];?>)">
                  <?php echo $pelak; ?>
                </span>
              <?php else: ?>
                <?php echo $pelak; ?>
              <?php endif; ?>
            </td>
            <!-- ستون‌های بعدی بدون تغییر… -->
            <td class="copy-column"><span class="no-data">--</span></td>
            <td class="copy-column"><span class="no-data">--</span></td>
            <td>
              <?php if (isset($rkarbar_ids[$pelak])): ?>
                <a href="edit_rkarbar.php?id=<?php echo $rkarbar_ids[$pelak]; ?>"
                   class="edit-btn" title="ویرایش"><i class="fas fa-edit"></i></a>
              <?php else: ?>
                <span class="no-data">--</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php
            endforeach;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- مودال نقشه و اسکریپت‌ها بدون تغییر -->
<div class="modal fade" id="map-modal" tabindex="-1" aria-labelledby="map-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="map-modal-label">موقعیت پلاک: <span id="modal-pelak"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="map-container" style="height:500px;"></div>
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
    document.getElementById('location-info').innerHTML =
      `<strong>عرض:</strong> ${lat.toFixed(6)} <br><strong>طول:</strong> ${lng.toFixed(6)}`;
    const mapModal = new bootstrap.Modal(document.getElementById('map-modal'));
    mapModal.show();
    setTimeout(() => {
        if (window.map) window.map.remove();
        window.map = L.map('map-container').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(window.map);
        L.marker([lat, lng]).addTo(window.map)
         .bindPopup(`<b>پلاک:</b> ${pelak}<br><b>مختصات:</b> ${lat.toFixed(6)}, ${lng.toFixed(6)}`)
         .openPopup();
    }, 500);
}
function copyColumn(columnIndex) {
    // کد کپی ستون (unchanged) …
}
function renewPelak(pelak) {
    if (confirm('آیا از ثبت دایم قبض انبار برای پلاک ' + pelak + ' اطمینان دارید؟')) {
        window.location.href = '?renew_pelak=' + encodeURIComponent(pelak);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    // کد رویداد ضمیمه تیترها برای کپی ستون…
});
</script>
<?php
if (file_exists("error_log")) unlink("error_log");
mysqli_close($connection);
include_once('sb/foot.php');
?>
```