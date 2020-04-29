<?php
//error_reporting(E_USER_WARNING | E_USER_NOTICE);
ini_set('date.timezone','Asia/Shanghai');
require 'conn.php';
require 'check_alive_class.php';
include 'mail/mail.php';
include 'weixin/weixin.php';

$result1 = mysqli_query($con,"select ip,tag,user,pwd,port,authdb,monitor,send_mail,
send_mail_to_list,send_weixin,send_weixin_to_list,threshold_alarm_connection from mongo_status_info");

while( list($ip,$tag,$user,$pwd,$port,$authdb,$monitor,$send_mail,$send_mail_to_list,$send_weixin,
	          $send_weixin_to_list,$threshold_alarm_connection) = mysqli_fetch_array($result1))
{
	
	if($monitor==0 || empty($monitor)){
		echo "\n被监控主机：$ip  【{$tag}】【端口{$port}】未开启监控，跳过不检测。"."\n";
		continue;
	}
	
	try{
		$mongo_conn = new MongoClient("mongodb://{$user}:{$pwd}@{$ip}:{$port}/{$authdb}" , array("connectTimeoutMS" => "3000"));
		$db = $mongo_conn->admin;
   		$is_alive = 1;
	}
          
	catch(Exception $e) 
	{
		$is_alive=0;
		echo '连接报错，错误信息是： ' .$e->getMessage()."\n";
	}

    // Mongo tcp端口连接存活检测
	if($is_alive==0) {
        $check = new Mongo_check_alive($is_alive);
        $check->check_alive();
        break;
    } else {
		//echo 'ok'."\n";
        $check = new Mongo_check_alive($is_alive);
        $check->check_alive();
	}

    //收集监控信息
	$serverStatus = $db->command( array('serverStatus'  =>  1 ) );
	sleep(1);  //等待1秒，相减得到QPS数值
	$serverStatus_2 = $db->command( array('serverStatus'  =>  1 ) );
	//print_r($s2); //调试

    $version = $serverStatus['version'];
	$uptime = round($serverStatus['uptime']/86400,1); //天
    $connections_current = $serverStatus['connections']['current'];
    $connections_available = $serverStatus['connections']['available'];
    $opcounters_insert_persecond = round($serverStatus_2['opcounters']['insert'] - $serverStatus['opcounters']['insert']);
	$opcounters_query_persecond = round($serverStatus_2['opcounters']['query'] - $serverStatus['opcounters']['query']);
	$opcounters_update_persecond = round($serverStatus_2['opcounters']['update'] - $serverStatus['opcounters']['update']);
	$opcounters_delete_persecond = round($serverStatus_2['opcounters']['delete'] - $serverStatus['opcounters']['delete']);
    $repl=$serverStatus['repl']['ismaster'] ? 'Primary' : 'Secondary';


    // Mongo 连接数报警检测
    $check = new Mongo_info('connection');
    $check -> check_monitor();


} // end while

?>

