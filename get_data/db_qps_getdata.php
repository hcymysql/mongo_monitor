<?php

function index($arr1,$arr2,$arr3,$arr4){
    ini_set('date.timezone','Asia/Shanghai');
    
/*
//调试
    $host = '10.10.159.31';
    $port = '27017';
    $tag = 'MongoDB测试机1';
    $interval_time = 'DATE_SUB(now(),interval 12 hour)';    
*/

    $host = $arr1;
    $port = $arr2;
    $tag = $arr3;
    $interval_time = $arr4;

    require '../conn.php';
    $get_info="select create_time,qps_select,qps_insert,qps_update,qps_delete from mongo_status_history where ip='$host' and port='$port' and tag='$tag'
               and (create_time >=$interval_time AND create_time <=NOW())";
    $result1 = mysqli_query($con,$get_info);
    //echo $get_info."<br>";
   

  $array = array();
  class Qps{
    public $create_time;
    public $qps_select;
    public $qps_insert;
    public $qps_update;
    public $qps_delete;
  }
  while($row = mysqli_fetch_array($result1,MYSQL_ASSOC)){
    $cons=new Qps();
    $cons->create_time = $row['create_time'];
    $cons->qps_select = $row['qps_select'];
    $cons->qps_insert = $row['qps_insert'];
    $cons->qps_update = $row['qps_update'];
    $cons->qps_delete = $row['qps_delete'];        
    $array[]=$cons;
  }
  $top_data=json_encode($array);
 echo $top_data;
}

/*$fn = isset($_GET['fn']) ? $_GET['fn'] : 'main';
if (function_exists($fn)) {
  call_user_func($fn);
}
*/

    $host = $_GET['host'];
    $port = $_GET['port'];
    $tag = $_GET['tag'];
    $interval_time = $_GET['interval_time'];
	
index($host,$port,$tag,$interval_time);


?>

