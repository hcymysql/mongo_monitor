<?php

class Mongo_check_alive
{
    public $is_alive;

    function __construct($para1)
    {
        $this->is_alive = $para1;
    }

    //主机存活报警
    function check_alive()
    {
        require 'conn.php';
        global $ip, $tag, $port,$send_mail,$send_mail_to_list,$send_weixin,$send_weixin_to_list;

        if ($this->is_alive == 0) {
            if ($send_mail == 0 || empty($send_mail)) {
                echo "被监控主机：$ip  【{$tag}】关闭邮件监控报警。" . "\n";
            } else {
                $alarm_subject = "【告警】被监控主机：" . $ip . "  【{$tag}】" . "tcp端口【{$port}】无法连接，请检查。 " . date("Y-m-d H:i:s");
                $alarm_info = "被监控主机：" . $ip . "  【{$tag}】" . "tcp端口【{$port}】无法连接，请检查。错误信息： " . $errstr;
                $sendmail = new mail($send_mail_to_list, $alarm_subject, $alarm_info);
                $sendmail->execCommand();
            }

            if ($send_weixin == 0 || empty($send_weixin)) {
                echo "被监控主机：$ip  【$tag】关闭微信监控报警。" . "\n";
            } else {
                $alarm_subject = "【告警】被监控主机：" . $ip . "  【{$tag}】" . "tcp端口【{$port}】无法连接，请检查。 " . date("Y-m-d H:i:s");
                $alarm_info = "被监控主机：" . $ip . "  【{$tag}】" . "tcp端口【{$port}】无法连接，请检查。错误信息： " . $errstr;
                $sendweixin = new weixin($send_weixin_to_list, $alarm_subject, $alarm_info);
                $sendweixin->execCommand();
            }

            if (($send_mail == 1 || $send_weixin == 1)) {
                $mongo_status = "UPDATE mongo_status_info SET alarm_alive_status = 1 WHERE ip='$ip' AND tag like '%{$tag}%'";
		        echo '$mongo_status: '.$mongo_status."\n";
                mysqli_query($con, $mongo_status);
            }

            $mongo_status_sql = "REPLACE INTO mongo_status(ip,tag,port,is_alive,create_time) VALUES('$ip','$tag','$port','offline',now())";

            if (mysqli_query($con, $mongo_status_sql)) {
                echo "\n{$ip}:'{$tag}' 监控数据采集入库成功\n";
            } else {
                echo "\n{$ip}:'{$tag}' 监控数据采集入库失败\n";
                echo "Error: " . $mongo_status_sql . "\n" . mysqli_error($con);
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
                $recover_sql = "SELECT alarm_alive_status FROM mongo_status_info WHERE ip='$ip' and tag='$tag'";
                $recover_status = mysqli_query($con, $recover_sql);
                $recover_status_row = mysqli_fetch_assoc($recover_status);
            }
            if (!empty($recover_status_row["alarm_alive_status"]) && $recover_status_row["alarm_alive_status"] == 1) {
                $recover_subject = "【恢复】被监控主机：" . $ip . "  【{$tag}】" . "tcp端口【{$port}】已恢复 " . date("Y-m-d H:i:s");
                $recover_info = "被监控主机：" . $ip . "  【{$tag}】" . "tcp端口【{$port}】已恢复 ";
                if ($send_mail == 1) {
                    $sendmail = new mail($send_mail_to_list, $recover_subject, $recover_info);
                    $sendmail->execCommand();
                }
                if ($send_weixin == 1) {
                    $sendweixin = new weixin($send_weixin_to_list, $recover_subject, $recover_info);
                    $sendweixin->execCommand();
                }
                $alarm_status = "UPDATE mongo_status_info SET alarm_alive_status = 0 WHERE ip='$ip'";
                mysqli_query($con, $alarm_status);
            }
        }

    } // end check_alive()

} // end class
?>
