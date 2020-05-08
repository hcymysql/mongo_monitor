<?php

class Mongo_repl
{
    public $check_para;

    function __construct($check_para)
    {
        $this->check_para = $check_para;
    }

    function check_repl_status(){
        $alarm_subject = "【告警】被监控主机：" . $me . "  【{$tag}】副本集状态是：" .$repl_status." " .date("Y-m-d H:i:s");
        $alarm_info = "被监控主机：" . $me . "  【{$tag}】副本集状态是：".$repl_status;
        $sendweixin = new weixin($send_weixin_to_list, $alarm_subject, $alarm_info);
        $sendweixin->execCommand();
    }

    function check_repl_lag()
    {
        require 'conn.php';
        global $ip, $tag, $port, $send_mail, $send_mail_to_list, $send_weixin, $send_weixin_to_list, $threshold_alarm_repl;
        global $me,$Seconds_Behind_Master,$repl_status;

        $threshold_alarm_value = 'threshold_alarm_'.$this->check_para;

        //同步延迟报警
        if (!empty($$threshold_alarm_value) && $Seconds_Behind_Master >= $$threshold_alarm_value) {
            if ($send_mail == 0 || empty($send_mail)) {
                echo "被监控主机：$ip  【{$tag}】关闭邮件监控报警。" . "\n";
            } else {
                $alarm_subject = "【告警】被监控主机：" . $me . "  【{$tag}】" . $this->check_para . "主从同步延迟{$Seconds_Behind_Master}秒 " . date("Y-m-d H:i:s");
                $alarm_info = "被监控主机：" . $me . "  【{$tag}】" . $this->check_para . "主从同步延迟{$Seconds_Behind_Master}秒";
                $sendmail = new mail($send_mail_to_list, $alarm_subject, $alarm_info);
                $sendmail->execCommand();
            }

            if ($send_weixin == 0 || empty($send_weixin)) {
                echo "被监控主机：$ip  【{$tag}】关闭微信监控报警。" . "\n";
            } else {
                $alarm_subject = "【告警】被监控主机：" . $me . "  【{$tag}】" . $this->check_para . "主从同步延迟{$Seconds_Behind_Master}秒 " . date("Y-m-d H:i:s");
                $alarm_info = "被监控主机：" . $me . "  【{$tag}】" . $this->check_para . "主从同步延迟{$Seconds_Behind_Master}秒";
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
                $recover_subject = "【恢复】被监控主机：" . $me . "  【{$tag}】" . $this->check_para . "主从同步延迟已恢复 " . date("Y-m-d H:i:s");
                $recover_info = "被监控主机：" . $me . "  【{$tag}】" . $this->check_para . "已恢复，主从同步延迟" .$Seconds_Behind_Master."秒";
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
    } // end check_repl_lag()
} //end class Mongo_repl

?>
