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
    $get_info="select create_time,threads_connected,available_connected from mongo_status_history where ip='$host' and port='$port' and tag='$tag'
               and (create_time >=$interval_time AND create_time <=NOW())";
    $result1 = mysqli_query($con,$get_info);
    //echo $get_info."<br>";
   

  $array= array();
  class Connections{
    public $create_time;
    public $threads_connected;
    public $available_connected;
  }
  while($row = mysqli_fetch_array($result1,MYSQL_ASSOC)){
    $cons=new Connections();
    $cons->create_time = $row['create_time'];
    $cons->threads_connected = $row['threads_connected'];
    $cons->available_connected = $row['available_connected'];
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

