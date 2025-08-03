<?php
include_once('first.php'); //بررسی ورود
header('Content-Type: text/html; charset=utf-8');

include_once('jdf.php');
include_once('ca.php');

// تنظیمات
$uploadDir = __DIR__ . '/uploads/';
$maxFilesToKeep = 3;

// تابع پاکسازی فایل‌های قدیمی
function cleanOldFiles($dir, $keep = 3) {
    if (!is_dir($dir)) return;
    
    $files = glob($dir . '*.xlsx');
    if (empty($files)) return;
    
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    foreach (array_slice($files, $keep) as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

// تابع پردازش اکسل
function parseXlsx($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception('فایل یافت نشد');
    }

    $zip = new ZipArchive;
    if ($zip->open($filePath) !== TRUE) {
        throw new Exception('خطا در باز کردن فایل اکسل');
    }

    // پردازش sharedStrings
    $sharedStrings = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        if (strpos($filename, 'sharedStrings.xml') !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($i));
            if ($xml === false) continue;
            
            foreach ($xml->si as $si) {
                $text = (string)$si->t;
                $text = preg_replace('/[\x{0600}-\x{06FF}]/u', 'u', $text);
                $text = strtolower($text);
                $text = preg_replace('/\s+/', '', $text);
                $sharedStrings[] = $text;
            }
        }
    }

    // پردازش اولین شیت
    $worksheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
    $data = [];
    
    if ($worksheet && isset($worksheet->sheetData)) {
        $rows = $worksheet->sheetData->row;
        $rowNumber = 0;
        
        foreach ($rows as $row) {
            $rowNumber++;
            if ($rowNumber == 1) continue;
            
            $rowData = ['row_num' => $rowNumber - 1];
            
            foreach ($row->c as $cell) {
                $value = '';
                
                if (isset($cell->v)) {
                    if (isset($cell['t']) && (string)$cell['t'] === 's') {
                        $index = (int)$cell->v;
                        $value = $sharedStrings[$index] ?? '';
                    } else {
                        $value = (string)$cell->v;
                        $value = preg_replace('/[\x{0600}-\x{06FF}]/u', 'u', $value);
                        $value = strtolower($value);
                        $value = preg_replace('/\s+/', '', $value);
                    }
                }
                
                $colName = preg_replace('/[0-9]/', '', (string)$cell['r']);
                $rowData[$colName] = $value;
            }
            
            $data[] = $rowData;
        }
    }

    $zip->close();
    return $data;
}

// ایجاد پوشه آپلود
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// پاکسازی فایل‌های قدیمی
cleanOldFiles($uploadDir, $maxFilesToKeep);

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سیستم پردازش اکسل پلاک های ورودی راهبند</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .upload-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .upload-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .table-container {
            margin-top: 30px;
        }
        .file-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        th {
            white-space: nowrap;
        }
        .success-row {
            background-color: #e8f5e9;
        }
        .duplicate-row {
            background-color: #fff3e0;
        }
        .error-row {
            background-color: #ffebee;
        }
    </style>
</head>
<body>
<?php
include_once('aval.php');
?>
    <div class="upload-container">
        <div class="upload-box">
            <h1 class="text-center mb-4">سیستم پردازش اکسل پلاک های ورودی راهبند</h1>
            
            <form method="post" enctype="multipart/form-data" class="mb-4">
                <div class="mb-3">
                    <label for="excelFile" class="form-label">فایل اکسل (xlsx) را انتخاب کنید:</label>
                    <input class="form-control" type="file" id="excelFile" name="excel_file" accept=".xlsx" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">آپلود و پردازش</button>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
                try {
                    // بررسی خطاهای آپلود
                    if ($_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception('خطا در آپلود فایل: کد خطا ' . $_FILES['excel_file']['error']);
                    }

                    // بررسی نوع فایل
                    $fileExt = pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION);
                    if (strtolower($fileExt) !== 'xlsx') {
                        throw new Exception('فقط فایل‌های با پسوند xlsx مجاز هستند');
                    }

                    // ذخیره فایل
                    $fileName = 'excel_' . date('Ymd_His') . '.xlsx';
                    $filePath = $uploadDir . $fileName;
                    
                    if (!move_uploaded_file($_FILES['excel_file']['tmp_name'], $filePath)) {
                        throw new Exception('خطا در ذخیره فایل');
                    }

                    echo '<div class="alert alert-success">فایل با موفقیت آپلود شد</div>';
                    
                    // پردازش فایل
                    $processedData = parseXlsx($filePath);
                    
                    // بررسی داده‌های پردازش شده
                    if (empty($processedData)) {
                        echo '<div class="alert alert-warning">فایل اکسل خالی است یا قابل پردازش نمی‌باشد</div>';
                    } else {
                        // آرایه برای ذخیره رکوردهای معتبر
                        $validRecords = [];
                        $insertedCount = 0;
                        $duplicateCount = 0;
                        $errorCount = 0;
                        
                        echo '<div class="table-responsive table-container">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ردیف</th>';
                        
                        // نمایش هدر ستون‌ها
                        if (!empty($processedData[0])) {
                            foreach ($processedData[0] as $colName => $value) {
                                if ($colName !== 'row_num') {
                                    echo '<th>' . htmlspecialchars($colName) . '</th>';
                                }
                            }
                            echo '<th>وضعیت</th>';
                        }
                        
                        echo '            </tr>
                                    </thead>
                                    <tbody>';
                        
                        // پردازش و نمایش داده‌ها
                        foreach ($processedData as $row) {
                            $keys = array_keys($row);
                            if (isset($keys[1])) {
                                $pelak = trim($row[$keys[1]]);
                                
                                // اعتبارسنجی پلاک
                                $isValid = !is_null($pelak) && 
                                    $pelak !== '' && 
                                    strlen($pelak) >= 3 &&
                                    preg_match('/^[\p{L}\p{N}]+$/u', $pelak);
                                
                                if ($isValid && isset($keys[2]) && isset($keys[3])) {
                                    // پردازش تاریخ و زمان
                                    $tarikh2 = "14" . $row[$keys[2]];
                                    $mytar = explode('/', $tarikh2);
                                    
                                    if (count($mytar) === 3) {
                                        $sal = $mytar[0];
                                        $mah = $mytar[1];
                                        $ruz = $mytar[2];
                                        
                                        $saat = $row[$keys[3]];
                                        $mysa = explode(':', $saat);
                                        
                                        if (count($mysa) >= 2) {
                                            $saat = $mysa[0];
                                            $dag = $mysa[1];
                                            
                                            // تبدیل به تایمستاپ
                                            $fuda = jmktime($saat, $dag, 0, $mah, $ruz, $sal);
                                            
                                            // بررسی تکراری نبودن رکورد
                                            $checkQuery = "SELECT id FROM rinfo WHERE pelak = ? AND zaman = ? LIMIT 1";
                                            $checkStmt = $connection->prepare($checkQuery);
                                            $checkStmt->bind_param("si", $pelak, $fuda);
                                            $checkStmt->execute();
                                            $checkResult = $checkStmt->get_result();
                                            
                                            if ($checkResult->num_rows > 0) {
                                                // رکورد تکراری است
                                                $duplicateCount++;
                                                $rowClass = 'duplicate-row';
                                                $statusText = '⏺ تکراری (ذخیره نشد)';
                                            } else {
                                                // درج در دیتابیس
                                                $query = "INSERT INTO rinfo (pelak, zaman) VALUES (?, ?)";
                                                $stmt = $connection->prepare($query);
                                                $stmt->bind_param("si", $pelak, $fuda);
                                                
                                                if ($stmt->execute()) {
                                                    $insertedCount++;
                                                    $validRecords[] = $row;
                                                    $rowClass = 'success-row';
                                                    $statusText = '✅ ذخیره شد';
                                                } else {
                                                    $errorCount++;
                                                    $rowClass = 'error-row';
                                                    $statusText = '❌ خطا در ذخیره';
                                                }
                                                $stmt->close();
                                            }
                                            $checkStmt->close();
                                            
                                            // نمایش ردیف
                                            echo '<tr class="' . $rowClass . '">
                                                    <td>' . htmlspecialchars($row['row_num']) . '</td>';
                                            
                                            foreach ($row as $colName => $value) {
                                                if ($colName !== 'row_num') {
                                                    echo '<td>' . htmlspecialchars($value) . '</td>';
                                                }
                                            }
                                            
                                            echo '<td>' . $statusText . '</td></tr>';
                                        }
                                    }
                                }
                            }
                        }
                        
                        echo '        </tbody>
                                </table>
                              </div>';
                        
                        // نمایش خلاصه نتایج
                        echo '<div class="alert alert-success">تعداد رکوردهای ذخیره شده در دیتابیس: ' . $insertedCount . '</div>';
                        echo '<div class="alert alert-warning">تعداد رکوردهای تکراری: ' . $duplicateCount . '</div>';
                        echo '<div class="alert alert-danger">تعداد رکوردهای با خطا: ' . $errorCount . '</div>';
                        
                        // به‌روزرسانی زمان سیستم
                        $rr = time();
                        $updateQuery = "UPDATE rhas SET zamansys = ? WHERE id = 1";
                        $stmt = $connection->prepare($updateQuery);
                        $stmt->bind_param("i", $rr);
                        $stmt->execute();
                        $stmt->close();
                        
                        // حذف فایل اکسل پس از پردازش
                        if (file_exists($filePath)) {
                            unlink($filePath);
                            echo '<div class="alert alert-info">فایل اکسل پس از پردازش حذف شد</div>';
                        }
                    }

                    mysqli_close($connection);

                } catch (Exception $e) {
                    // حذف فایل در صورت خطا
                    if (isset($filePath) && file_exists($filePath)) {
                        unlink($filePath);
                    }
                    echo '<div class="alert alert-danger">خطا: ' . $e->getMessage() . '</div>';
                }
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>