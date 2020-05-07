<?php
//error_reporting(E_USER_WARNING | E_USER_NOTICE);
ini_set('date.timezone','Asia/Shanghai');
require 'conn.php';
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

    try {
        $mongo_conn = new MongoClient("mongodb://{$user}:{$pwd}@{$ip}:{$port}/{$authdb}", array("connectTimeoutMS" => "3000"));
        $db = $mongo_conn->admin;
    }

    catch (Exception $e) {
        echo '连接报错，错误信息是： ' .$e->getMessage()."\n";
        break;
    }

    echo '监控时间：'.date("Y-m-d H:i:s")."\n";

    $serverStatus = $db->command(array('serverStatus' => 1));
    $me = $serverStatus['repl']['me'];
    $repl_status = $serverStatus['repl']['ismaster'] ? 'Primary' : 'Secondary';


    $r = $db->command(array('replSetGetStatus' => 1));

    if ($repl_status == 'Primary') {
        $repl_status_sql="insert into mongo_repl_status(ip,tag,port,role,is_alive,create_time) values('$ip','$tag','$port','$repl_status','online',now())";
        echo "\n".'MongoDB监控主机：'.$me.' 副本集状态是：'.$repl_status."\n";
        echo '-----------------------------------------' . "\n\n";

    } else if ($repl_status == 'Secondary') {
        for ($i = 0; $i < count($r['members']); $i++) {
            if ($r['members'][$i]['stateStr'] == 'PRIMARY') {
                $op = get_object_vars($r['members'][$i]['optimeDate']);
                $primary_sec = $op['sec'];
            }

            if ($r['members'][$i]['stateStr'] == 'SECONDARY') {
                $op = get_object_vars($r['members'][$i]['optimeDate']);
                $secondary_sec = $op['sec'];
                echo "\n";
                echo 'MongoDB监控主机：'.$me.' 副本集状态是：'.$repl_status."\n";
                //$r_name = ($r['members'][$i]['name']);
                //echo '$secondary_name: ' . $r_name . "\n";
                $Seconds_Behind_Master = $primary_sec - $secondary_sec;
                echo '主从延迟：' . $Seconds_Behind_Master . ' 秒' . "\n";
                $repl_status_sql="insert into mongo_repl_status(ip,tag,port,role,is_alive,Seconds_Behind_Master,create_time) 
                                  values('$ip','$tag','$port','$repl_status','online',$Seconds_Behind_Master,now())";
                echo '-----------------------------------------' . "\n\n";
                break;
            }

        } // end for循环

    } else {
        $repl_status_sql="insert into mongo_repl_status(ip,tag,port,role,is_alive,create_time) values('$ip','$tag','$port','$repl_status','online',now())";
        echo 'MongoDB监控主机：'.$me.' 副本集状态是：'.$repl_status."\n";
    }

//print_r($r['members']);调试

} // end while

?>
