<?php

class Mongo_info
{
    public $check_para;

    function __construct($check_para)
    {
        $this->check_para = $check_para;
    }

    function check_monitor()
    {
        require 'conn.php';
        global $ip, $tag, $port, $send_mail, $send_mail_to_list, $send_weixin, $send_weixin_to_list, $threshold_alarm_connection;
        global $version,$uptime,$connections_current,$connections_available,$opcounters_insert_persecond,
               $opcounters_query_persecond,$opcounters_update_persecond,$opcounters_delete_persecond,$repl_status,$mem_resident;

        $threshold_alarm_value = 'threshold_alarm_'.$this->check_para;

        //活动连接数报警
        if (!empty($$threshold_alarm_value) && $connections_current >= $$threshold_alarm_value) {
            if ($send_mail == 0 || empty($send_mail)) {
                echo "被监控主机：$ip  【{$tag}】关闭邮件监控报警。" . "\n";
            } else {
                $alarm_subject = "【告警】被监控主机：" . $ip . "  【{$tag}】" . $this->check_para . "活动连接数超高，请检查。 " . date("Y-m-d H:i:s");
                $alarm_info = "被监控主机：" . $ip . "  【{$tag}】" . $this->check_para . "活动连接数是： " . $connections_current . "，高于报警阀值：".$$threshold_alarm_value;
                $sendmail = new mail($send_mail_to_list, $alarm_subject, $alarm_info);
                $sendmail->execCommand();
            }

            if ($send_weixin == 0 || empty($send_weixin)) {
                echo "被监控主机：$ip  【{$tag}】关闭微信监控报警。" . "\n";
            } else {
                $alarm_subject = "【告警】被监控主机：" . $ip . "  【{$tag}】" . $this->check_para . "活动连接数超高，请检查。 " . date("Y-m-d H:i:s");
                $alarm_info = "被监控主机：" . $ip . "  【{$tag}】" . $this->check_para . "活动连接数是： " . $connections_current . "，高于报警阀值：".$$threshold_alarm_value;
                $sendweixin = new weixin($send_weixin_to_list, $alarm_subject, $alarm_info);
                $sendweixin->execCommand();
            }

            if (($send_mail == 1 || $send_weixin == 1)) {
                $mongo_status = "UPDATE mongo_status_info SET alarm_{$this->check_para}_status = 1 WHERE ip='{$ip}' AND tag like '%{$tag}%'";
                mysqli_query($con, $mongo_status);
            }
        } else {
            //恢复---------------------
            if ($send_mail == 0 || empty($send_mail)) {
                echo "被监控主机：$ip  【{$tag}】关闭邮件监控报警。" . "\n";
            }
            if ($send_weixin == 0 || empty($send_weixin)) {
                echo "被监控主机：$ip  【{$tag}】关闭微信监控报警。" . "\n";
            }
            if (($send_mail == 1 || $send_weixin == 1)) {
                $recover_sql = "SELECT alarm_{$this->check_para}_status FROM mongo_status_info WHERE ip='{$ip}' AND tag like '%{$tag}%'";
                $recover_status = mysqli_query($con, $recover_sql);
                $recover_status_row = mysqli_fetch_assoc($recover_status);
            }
            if (!empty($recover_status_row["alarm_{$this->check_para}_status"]) && $recover_status_row["alarm_{$this->check_para}_status"] == 1) {
                $recover_subject = "【恢复】被监控主机：" . $ip . "  【{$tag}】" . $this->check_para . "已恢复 " . date("Y-m-d H:i:s");
                $recover_info = "被监控主机：" . $ip . "  【{$tag}】" . $this->check_para . "已恢复，当前活动连接数是： " . $connections_current;
                if ($send_mail == 1) {
                    $sendmail = new mail($send_mail_to_list, $recover_subject, $recover_info);
                    $sendmail->execCommand();
                }
                if ($send_weixin == 1) {
                    $sendweixin = new weixin($send_weixin_to_list, $recover_subject, $recover_info);
                    $sendweixin->execCommand();
                }
                $alarm_status = "UPDATE mongo_status_info SET alarm_{$this->check_para}_status = 0 WHERE ip='{$ip}' AND tag like '%{$tag}%'";
                mysqli_query($con, $alarm_status);
            }
        }

        //入库
        $mongo_info_sql = "REPLACE INTO mongo_status(ip,tag,port,role,is_alive,threads_connected,available_connected,qps_select,qps_insert,qps_update,
                          qps_delete,mem_usage,runtime,db_version,create_time) 
                          VALUES('{$ip}','{$tag}','{$port}','{$repl_status}','online','{$connections_current}','{$connections_available}',
                          $opcounters_query_persecond,$opcounters_insert_persecond,$opcounters_update_persecond,$opcounters_delete_persecond,$mem_resident,
                          $uptime,'{$version}',now())";

        if (mysqli_query($con, $mongo_info_sql)) {
            echo "{$ip}:'{$tag}'监控数据采集入库成功\n";
            echo "---------------------------\n\n";
            mysqli_query($con,"INSERT INTO mongo_status_history(ip,tag,port,role,is_alive,threads_connected,available_connected,qps_select,qps_insert,qps_update,
                          qps_delete,mem_usage,runtime,db_version,create_time) 
                          VALUES('{$ip}','{$tag}','{$port}','{$repl_status}','online','{$connections_current}','{$connections_available}',
                          $opcounters_query_persecond,$opcounters_insert_persecond,$opcounters_update_persecond,$opcounters_delete_persecond,$mem_resident,
                          $uptime,'{$version}',now())");
        } else {
            echo "{$ip}:'{$tag}'监控数据采集入库成功\n";
            echo "Error: " . $mongo_info_sql . "\n" . mysqli_error($con);
        }

    } // end check_monitor()

} //end class Mongo_info

?>