<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronController extends CI_Controller {

    var $cron_notification_email = 'tech.support@suryaloan.com';
    var $cron_notification_cc_email = '';

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronSMS_Model', 'SMSModel');
        //$this->load->model('CronJobs/CronEmailer_Model', 'EmailModel');
    }

    
    function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 99, $cc_email = "", $reply_to = "") 
    {
        $status = 0;
        $error = "";
        $provider_name = "";
        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = INFO_EMAIL;

            $return_array = common_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to, "", "", "");

            // $return_array = lw_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);
            $status = $return_array['status'];
            $error = $return_array['error'];

            $insert_log_array = array();
            $insert_log_array['email_provider'] = $provider_name;
            $insert_log_array['email_type_id'] = $email_type_id;
            $insert_log_array['email_address'] = $email;
            $insert_log_array['email_content'] = addslashes($message);
            $insert_log_array['email_api_status_id'] = $status;
            $insert_log_array['email_errors'] = $error;
            $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

            $this->SMSModel->emaillog_insert($insert_log_array);

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }

    function checkCronJob($cron)
    {
        if(isset($cron['function']))
        {
            $functionName = $cron['function'];
            unset($cron['function']);
        }
        $cronData = $this->db->where('job_id',$cron['job_id'])->get('cron_logs')->row_array();
        if(!isset($cronData))
        {
            $this->db->insert('cron_logs',$cron);
        }
        elseif(isset($cronData) && empty($cronData['job_status']))
        {
            echo "This job is already running. Please wait till this job completes."; exit;
        }
        elseif(isset($cronData) && empty($cronData['job_status']))
        {
            echo "This job is already running. Please wait till this job completes."; exit;
        }
        elseif(isset($cronData) && $cronData['job_status'] == 1)
        {
            $time = explode('_', $cronData['job_id']);
            if(isset($time[1]))
            {
                echo "This Cron job has already ran for this hour.";
            } 
            else 
            {
                echo "This Cron job has already ran for the day.";
            }
            exit;
            
        }
        $template = $this->db->select('m_st_template_id as templateid, m_st_template_source as headerid, m_st_content as message')->where(array('m_st_description'=>$functionName, 'm_st_active' => 1, 'm_st_deleted' => 0))->get('master_sms_template')->row_array();
        if(!empty($template)){
            $cronData['template'] = $template;
        }
        return $cronData;
    }

}
