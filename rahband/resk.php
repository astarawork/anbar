<?php
include_once('first.php'); //بررسی ورود
include_once('sar.php');
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت عملیات</title>
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        
        .btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #c0392b;
        }
        
        .alert {
            padding: 15px;
            background-color: #2ecc71;
            color: white;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <?php include_once('aval.php'); ?>
    
    <script type="text/javascript" src="/files/jalalidatepicker.min.js"></script>
    <script>
        jalaliDatepicker.startWatch({
            minDate: "attr",
            maxDate: "attr",
            time: true
        });
    </script>

    <?php
    include_once('jdf.php');
    include_once('ca.php');

    if (isset($_POST['hazf1']) && !empty($_POST['hazf1'])) {
        mysqli_query($connection, "TRUNCATE TABLE rkarbar");
       
        echo '<div class="alert">اطلاعات کاربر حذف شد</div>';
    }
	
	 if (isset($_POST['hazf2']) && !empty($_POST['hazf2'])) {
        
        mysqli_query($connection, "TRUNCATE TABLE rinfo");
		 
        echo '<div class="alert">اطلاعات ورودی سیستم حذف شد</div>';
    }
	
	 if (isset($_POST['hazf3']) && !empty($_POST['hazf3'])) {
      
		 mysqli_query($connection, "TRUNCATE TABLE rinfo2");
        echo '<div class="alert">اطلاعات خروجی های سیستم حذف شد</div>';
    }
	
	 if (isset($_POST['hazf']) && !empty($_POST['hazf'])) {
		    mysqli_query($connection, "TRUNCATE TABLE rkarbar");
       mysqli_query($connection, "TRUNCATE TABLE rinfo");
		 mysqli_query($connection, "TRUNCATE TABLE rinfo2");
        echo '<div class="alert">تمام اطلاعات حذف شدند</div>';
    }
    ?>
    
    <h2>حذف اطلاعات کاربر</h2>
	  
    <form action="resk.php" method="post">
        <input type="hidden" name="hazf" value="hazf">
        <input type="submit" value="حذف تمام ورودی ها" class="btn">
    </form>
	<hr>
    
    <form action="resk.php" method="post">
        <input type="hidden" name="hazf1" value="hazf1">
        <input type="submit" value="حذف تمام ورودی های کاربر" class="btn">
    </form>
	<hr>
	 <form action="resk.php" method="post">
        <input type="hidden" name="hazf2" value="hazf2">
        <input type="submit" value="حذف تمام ورودی های سیستم راهبند" class="btn">
    </form>
	<hr>
	 <form action="resk.php" method="post">
        <input type="hidden" name="hazf3" value="hazf3">
        <input type="submit" value="حذف تمام خروجی های سیستم راهبند" class="btn">
    </form>
</div>

<?php
if (file_exists("error_log")) {
    unlink("error_log");
}

mysqli_close($connection);
include_once('sb/foot.php');
?>