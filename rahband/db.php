


  <?php
  
include_once('sar.php');

?>

<style>
  
  .input-group>input.someInput {flex: 0 1 60px;}
  </style>
    
    <title> ثبت عملیات </title>
	

	
  </head>
  <body>
  
  
 <?php

//include_once('sb/index2.php');

?> 
  
    <script type="text/javascript" src="/files/jalalidatepicker.min.js">
    </script>
    <script>
            jalaliDatepicker.startWatch({
                minDate: "attr",
                maxDate: "attr",
                time:true
            }); 
    </script>
  
  <br>
  
  

  
  <?php
  
include_once('jdf.php');
include_once('ca.php');

//echo date("y:m:d");

//echo jdate('Y/n/j');
 //echo time();
 
//$ft= jalali_to_gregorian(1389,11,22,"/");
//print_r($ft);
//echo $ft;
    // <input type="text" data-jdp placeholder="لطفا یک تاریخ وارد نمایید" data-jdp-only-date/>
   // <input type="text" data-jdp data-jdp-min-date="today" placeholder="نمایش تاریخ از امروز" data-jdp-only-date/>
 //jmktime(6,15,34,11,22,1389)

//$pemroz=tr_num(jdate('Y/n/j'));



//echo $rok;



 //print_r($kol);
 
 echo '<br>';
 

 
 
 
 echo "---"."<hr>";
 
  if(isset($_POST['pelak']) && !empty($_POST['pelak'])){
		
		$pelak=$_POST['pelak'];
		
		//echo $pelak;
		
		$par=explode(" ",$pelak);
		
		//print_r($pelar);
	mysqli_query( $connection,"TRUNCATE TABLE rinfo" );
	
		foreach ($par as $value) {
		
			//echo $value."<br>";
			
			
			
			mysqli_query($connection,"INSERT INTO rinfo (pelak,zaman) 
VALUES('$value','1')") or die(mysqli_error());


		}
   // echo $value."<br>";

		
  }
 
?>
  
 
  
 <h2>ثبت عملیات</h2> 
  <?php
  



  ?>
  

<form action="db.php" method="post">

<input type="text" name="pelak">
<input type="submit">

</form>
 
  
 

		<?php
		
		
		

		
		
		
		
		
		
		/*
				$queryfodi = mysqli_query($connection,"SELECT * FROM aghlam WHERE vaziat=1")
								or die(mysqli_error());
								
									//$countfaq2a = mysqli_num_rows($queryfodi);
									$countf = mysqli_num_rows($queryfodi);
							if($countf>0){

									while($fodstk = mysqli_fetch_array($queryfodi))
									{ 
								
									$qid = $fodstk['id'];
									$qname = $fodstk['name'];	
									print '<option value="'.$qname.'"></option>';
									//print '<option value="'.$qid.'">'.$qname.'</option>';
										//$arkol[] = $fodstk['id'];
										//echo $myfdid."<hr>";
										//$arkol[] = array($fodstk['id'],$fodstk['price'],$fodstk['zaman']);
									}
									
							}

				?>








  <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">تعداد</span>
  </div>
  <input type="number" name="tedad" class="form-control" placeholder="تعداد" aria-label="Username" aria-describedby="basic-addon1" required>
</div> 

  <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">توضیحات</span>
  </div>
  <input type="text" name="tozih" class="form-control" placeholder="توضیحات" aria-label="Username" aria-describedby="basic-addon1" >
</div> 




<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">تحویل به</span>
  </div>

<input class="form-control" name="ashkhas" list="datalistOptions2" id="exampleDataList2" placeholder="نام را وارد کنید" required>
<datalist id="datalistOptions2">
 
		<?php
				$queryfodi2 = mysqli_query($connection,"SELECT * FROM asami ")
								or die(mysqli_error());
								

									$countf2 = mysqli_num_rows($queryfodi2);
							if($countf2>0){

									while($fodstk2 = mysqli_fetch_array($queryfodi2))
									{ 
								
									$qid2 = $fodstk2['id'];
									$qname2 = $fodstk2['name'];	
									print '<option value="'.$qname2.'"></option>';

									}
									
							}

				?>
</datalist>
</div>




  <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">تاریخ ثبت</span>
  </div>
  <input type="text" id="dd2" name="tarikh" class="form-control" data-jdp value="<?php echo $pemroz; ?>" aria-label="Username" aria-describedby="basic-addon1" data-jdp-only-date required>
</div> 

  <div>
<input type="submit" value="ثبت ..." >
</div>

</form>





    
    <script type="text/javascript" src="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js">
    </script>
    <script>
            jalaliDatepicker.startWatch({
                minDate: "attr",
                maxDate: "attr",
                time:true
            }); 
    </script>



<?php
mysqli_close($connection);

*/




if (file_exists("error_log")) {
	unlink("error_log");
}	


mysqli_close($connection);


include_once('sb/foot.php');

?>



