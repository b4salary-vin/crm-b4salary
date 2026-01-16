<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once (dirname(__FILE__) . "/CronController.php");

class Wishes extends CronController 
{

    public function __construct() 
    {
        parent::__construct();
    }

    public function birthdayWishes()
    {
        $cron['job_id']     = 'birthdayWishes#'.date('Y-m-d');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 2;
        $cron['job_name']   = 'birthdayWishes';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__);
        $cronData = $this->checkCronJob($cron);
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            $results = $this->db->query("SELECT first_name, sur_name, mobile, alternate_mobile, email, alternate_email from lead_customer WHERE month(dob) = ".date('m')." AND day(dob) = ".date('d'). " GROUP BY email")->result_array();
            $log['total_records'] = count($results);
            if(count($results))
            {
                foreach($results as $key => $result):
                $message = '
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Happy Birthday!</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 0;
                                padding: 0;
                                background-color: #f0f8ff;
                            }
                            .container {
                                width: 100%;
                                max-width: 600px;
                                margin: 0 auto;
                                background-color: #ffffff;
                                border-radius: 8px;
                                overflow: hidden;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                            }
                            .header {
                                background-color: #f5c964;
                                padding: 20px;
                                text-align: center;
                                font-size: 24px;
                                color: #333333;
                            }
                            .content {
                                padding: 20px;
                                background:#fef0d0;
                                text-align: center;
                            }
                            .content h2 {
                                color: #333333;
                            }
                            .birthday-image {
                                width: 100%;
                                max-width: 500px;
                                height: auto;
                                border-radius: 8px;
                            }
                            .footer {
                                background-color: #f5c964;
                                padding: 15px;
                                text-align: center;
                                font-size: 14px;
                                color: #333333;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <!-- Header Section -->
                            <div class="header">
                                Happy Birthday '.$result['first_name'].'!
                            </div>

                            <!-- Content Section -->
                            <div class="content">
                                <img src="'.PUBLIC_IMAGES.'birthday.png" alt="Birthday Cake" class="birthday-image">
                            </div>

                            <!-- Footer Section -->
                            <div class="footer">
                                <a href="' . FACEBOOK_LINK . '" target="_blank"><img src="' . FACEBOOK_ICON . '" alt="facebook" width="35" height="35" align="facebook" /></a><a href="' . LINKEDIN_LINK . '" target="_blank"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="35" height="35" align="linkdin" /></a><a href="' . TWITTER_LINK . '" target="_blank"><img src="' . TWITTER_ICON . '" width="35" height="35" alt="twitter" /></a><a href="' . INSTAGRAM_LINK . '" target="_blank"><img src="' . INSTAGRAM_ICON . '" alt="insta" width="35" height="35" align="instagram"></a> <a href="' . YOUTUBE_LINK . '" target="_blank"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="35" height="35" align="you-tube"></a>
                            </div>
                        </div>
                    </body>
                </html>';
                $subject = BRAND. ' wishes you a very Happy Birthday Dear '.$result['first_name'];
                if(!empty($result['email']))
                {
                    $return_array = $this->middlewareEmail($result['email'],$subject , $message, '', 20);
                    if ($return_array['status'] == 1) 
                    {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        $log['users']['email_sent'][] = $result['email'];
                    } 
                    else 
                    {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        $log['users']['email_failed'][] = $result['email'];
                    }
                } 
                else 
                {
                    $log['users']['email_sent'][] = 'test@gmail.com';
                    $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                }
                
                    
                endforeach;
                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } 
            else 
            {
                echo "No Data";
                $email_counter['email_sent'] = 0;
            }
            $log['email_sent'] = $email_counter['email_sent'];
            $log['email_failed'] = $email_counter['email_failed'];
            $cron_log['job_log'] = json_encode($log);
            $cron_log['job_status'] = ($log['total_records']==$email_counter['email_sent'])?1:2;
            $cron_log['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "email_sent = " . $sms_counter['email_sent'] . " | email_failed=" . $sms_counter['email_failed'] . " <br/> Campaign Name : " .$cron['job_id'];
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
        }
    }
}    
