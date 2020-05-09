<?php         
//https://github.com/hcymysql/mongo_monitor

     $con = mysqli_connect("127.0.0.1","admin","hechunyang","mongo_monitor","3306") or die("数据库链接错误".mysqli_error($con));
     mysqli_query($con,"set names utf8");  
?> 
