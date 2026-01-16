<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once (dirname(__FILE__) . "/CronController.php");

class Reminders extends CronController {

    public function __construct() 
    {
        parent::__construct();
    }

    public function smsReapplyloan()
    {
        $cron['job_id']     = 'smsReapplyloan#' . date('Y-m-d_H');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 0;
        $cron['job_name']   = 'smsReapplyloan';
        $cron['job_url']    = str_replace('::', '/', LMS_URL . 'CronJobs/' . __METHOD__);
        $cron['function']   = str_replace(__CLASS__ . '::', '', __METHOD__);
        $cronData = $this->checkCronJob($cron);
        $date = date('Y-m-d');
        if (isset($cronData['template'])) {
            $template = $cronData['template'];
        }
        if (!isset($cronData['job_status']) || ($cronData['job_status'] == 2)) 
        {
            $results  = $this->db->query("SELECT collection.closure_payment_updated_on, leads.lead_id, collection.id, LC.first_name, LC.middle_name,LC.sur_name,leads.mobile,leads.email,leads.alternate_email, LC.alternate_mobile FROM leads JOIN collection ON leads.lead_id = collection.lead_id JOIN lead_customer as LC ON LC.customer_lead_id = leads.lead_id WHERE leads.status = 'CLOSED' AND collection.payment_verification = '1' AND collection.id IN (SELECT MAX(id) FROM collection WHERE payment_verification = '1' GROUP BY lead_id) AND collection.closure_payment_updated_on >= now() - INTERVAL 2 DAY AND collection.repayment_type = 16 ORDER BY collection.closure_payment_updated_on DESC;")->result_array();

            $log['total_records'] = count($results) ?? 0;
            $sms_counter = array('sms_sent' => 0, 'sms_failed' => 0);
            require(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();
            $sms_counter['sms_sent'] = 0;
            if (!empty($results)) 
            {
                foreach ($results as $key =>  $result) 
                {
                    $name                   = $result['first_name'] ?? 'customer';
                    $message                = str_replace('#customer#', rtrim($name), $template['message']);
                    $request['templateid'] = '1707174098110009448';//$template['templateid'] ?? '1707174065818885421';
                    $request['headerid']   = '155005';//$template['headerid'] ?? 'RFINSL';
                    $request['sms']        = $messages  ?? "Dear Piyush, Thank you for choosing Surya Loan! Complete your application and see the funds in your account today Apply - https://suryaloan.com/apply-now Team RFINSL";
                    $request['mobile']     = '9891914101'; //$result['mobile']
                    $request['alternate_mobile'] = '9891914161'; // $result['alternate_mobile']
                    //$msg_already_sent = $this->db->where('sms_content', $request['sms'])->get('api_sms_logs')->row_array(); 
                    $request['msg_sent_api'] = $CommonComponent->payday_sms_api(2, $customer_data['lead_id'], $request);
                    echo "<pre>"; print_r($request); die;
                }
            }
            $log['final_msg_sent'] = $sms_counter['sms_sent'];
            $cron_log['job_log'] = json_encode($log);
            $cron_log['job_status'] = ($log['total_records'] == $sms_counter['sms_sent']) ? 1 : 2;
            $cron_log['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('job_id', $cron['job_id'])->update('cron_logs', $cron_log);
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] . " - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'] . " <br/> Campaign Name : " . $cron['job_id'];
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
            echo "Total Records " . $log['total_records'] . " Found. Total sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'];
        }
    }

    public function smsRepayment($days = 0)
    {
        if($days > 5)
        {
            echo 'Not a valid day range'; exit;
        }
        $cron['job_id']     = 'smsRepayment'.$days.'Days#'.date('Y-m-d');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 1;
        $cron['job_name']   = 'smsRepayment'.$days.'Days';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__.'/'.$days);
        $cron['function']   = str_replace(__CLASS__.'::','',__METHOD__);
        $cronData = $this->checkCronJob($cron);
        if(isset($cronData['template']))
        {
            $template = $cronData['template'];
            unset($cronData['template']);
        }
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            $tempDetails = $this->SMSModel->getAllRepaymentReminderSMS(true, $days);
            $log['total_records'] = count($tempDetails);
            $start_datetime = date("d-m-Y H:i:s");
            $sms_counter = array('sms_sent' => 0, 'sms_failed' => 0);
            require(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();
            $sms_counter['sms_sent'] = 0;
            if (!empty($tempDetails)) 
            {
                foreach ($tempDetails as $key => $customer_data) 
                {
                    $name                   = $customer_data['first_name']??'Customer';
                    $message    = str_replace('#customer#',rtrim($name), $template['message']);
                    $message    = str_replace('#amount#',rtrim($customer_data['loan_total_outstanding_amount']), $message);
                    $message    = str_replace('#repayment_date#',rtrim(date('d-m-Y',strtotime($customer_data['repayment_date']))), $message);
                    $message    = str_replace('#loan_no#',rtrim($customer_data['loan_no']), $message);
                    $message    = str_replace('#loan_repay_link#',rtrim(LOAN_REPAY_LINK), $message);
                    $request['templateid']  = $template['templateid'];
                    $request['headerid']    = $template['headerid'];
                    $request['sms']         = $message;
                    $request['lead_id']     = $customer_data['lead_id'];
                    $request['mobile']         =   $customer_data['mobile'];
                    $request['alternate_mobile'] = $customer_data['alternate_mobile'];
                    $msg_already_sent = 
                    $this->db->where(array('sms_content'=>$request['sms']))->where("date(sms_created_on) = '".date('Y-m-d')."'")->get('api_sms_logs')->row_array();
                    if(empty($msg_already_sent))
                    {
                        echo $key+1 .'. '.$message."</br>";
                        $send_sms = $CommonComponent->payday_sms_api(2, $customer_data['lead_id'], $request);
                        if($send_sms['status'] == 1) 
                        {
                            $request['msg_send'] = $request['mobile'];     
                            $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                        }
                        else 
                        {
                            $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                            if (!empty($customer_data['alternate_mobile'])) 
                            {
                                $send_sms = $CommonComponent->payday_sms_api(2, $customer_data['lead_id'], $request);
                                if($send_sms['status'] == 1) 
                                {
                                    $request['msg_send'] = $request['alternate_mobile'];
                                    $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                                }
                                else 
                                {
                                    $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                                }
                            }
                        }
                            
                    }
                    else 
                    {
                        $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                    }   
                }    
            } 
            $log['final_sms_sent'] = $sms_counter['sms_sent'];
            $cron_log['job_log'] = json_encode($log);
            $cron_log['job_status'] = ($log['total_records']==$sms_counter['sms_sent'])?1:2;
            $cron_log['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'] . " <br/> Campaign Name : " .$cron['job_id'];
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
            echo "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'];
        }    
    }

    public function repaymentEmail($days) {

        if($days > 5)
        {
            echo 'Not a valid day range'; exit;
        }
        $cron['job_id']     = 'repaymentEmail_'.$days.'Days#'.date('Y-m-d');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 1;
        $cron['job_name']   = 'repaymentEmail_'.$days.'Days';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__.'/'.$days);
        $cronData = $this->checkCronJob($cron);
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            $tempDetails = $this->SMSModel->getAllRepaymentReminderSMS(true, $days);
            $log['total_records'] = count($tempDetails);
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);
            if (!empty($tempDetails)) 
            {
                foreach ($tempDetails as $customer_data) 
                {

                    if (!empty($customer_data['email'])) 
                    {

                        $lead_id = $customer_data['lead_id'];
                        $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                        $loan_no = $customer_data['loan_no'];
                        $repayment_amount = number_format($customer_data['repayment_amount']);
                        $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));
                        $to_email = $customer_data['email'];
                        $email_subject = "Reminder: $days Days Left for Loan Repayment - Application No: $loan_no";
                        $email_message = 
                        '<!DOCTYPE html>
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>Thank You | '.BRAND_NAME.'</title>
                            </head>
                            <body>
                                <table width="100%" border="0" style="font-family:Arial, Helvetica, sans-serif; padding:20px; background: #fff; color:#383535; ">
                                    <tr align="left">
                                        <td colspan=2>
                                            <img src="' . EMAIL_BRAND_LOGO . '" width="20%">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h3>Dear ' . $cust_full_name . ',</h3>
                                            <p style="line-height:25px; margin:0px; text-align: justify;"> Your loan payment of <b>&#8377;' . $repayment_amount . '</b>  is due on ' . $repayment_date . ' against Application No: <b>'.$loan_no.'</b>. This is a friendly reminder that you have ' . $days . ' days left to make the payment. Kindly ensure the payment is made on or before the due date to avoid any late fees or penalties. Additionally you can also visit <a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "color:#0463a3"> ' . LOAN_REPAY_LINK . ' </a> to pay loan or call us at <a href = "tel:' . COLLECTION_PHONE . '" style = "color:#0463a3">' . COLLECTION_PHONE . '</a>.
                                            <br>If you have already made the payment, please ignore this message. For any questions, feel free to reply this message.</p>
                                        </td>
                                        <td  style="align:center;">
                                            <img src="' . PUBLIC_IMAGES . $days.'Days.png" width="40%">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan=2>
                                            <p style="line-height:25px; margin:0px;">
                                                <b> Thank you, <br><br>
                                                    '.BRAND.' Collection Department </b><br>
                                                    <b>Email:</b> <span style="font-size:16px;">' . COLLECTION_EMAIL . '</span> <br>
                                                    <b>Phone:</b> <span style="font-size:16px;">' . COLLECTION_PHONE . '</span>
                                                </br>
                                            </p>
                                        </td>
                                    </tr>    
                                    <tr>
                                        <td colspan=2>
                                        <hr>
                                        <!-- Facebook Icon -->
                                        <a href="' . FACEBOOK_LINK . '" target="_blank" style="margin: 0 10px; text-decoration: none;">
                                            <img src="' . FACEBOOK_ICON . '" class="socil-t" alt="facebook"
                                                style="width:30px;">
                                        </a>
                                        <!-- LinkedIn Icon -->
                                        <a href="' . LINKEDIN_LINK . '" target="_blank"
                                            style="margin: 0 10px; text-decoration: none;">
                                            <img src="' . LINKEDIN_ICON . '" class="socil-t" alt="linkedin"
                                                style="width:30px;">
                                        </a>
                                        <!-- Instagram Icon -->
                                        <a href="' . INSTAGRAM_LINK . '" target="_blank"
                                            style="margin: 0 10px; text-decoration: none;">
                                            <img src="' . INSTAGRAM_ICON . '" class="socil-t" alt="instagram"
                                                style="width:30px;">
                                        </a>
                                        <!-- Twitter Icon -->
                                        <a href="' . TWITTER_LINK . '" target="_blank" style="margin: 0 10px; text-decoration: none;">
                                            <img src="' . TWITTER_ICON . '" class="socil-t" alt="twitter"
                                                style="width:30px;">
                                        </a>
                                        <!-- YouTube Icon -->
                                        <a href="' . YOUTUBE_LINK . '" target="_blank" style="margin: 0 10px; text-decoration: none;">
                                            <img src="' . YOUTUBE_ICON . '" class="socil-t" alt="youtube"
                                                style="width:30px;">
                                        </a>
                                        </td>
                                    </tr>
                                </table>
                            </body>
                        </html>';
                        $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 13, CRON_EMAIL, CRON_EMAIL);
                        if ($return_array['status'] == 1) 
                        {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                            $log['users']['mail_sent'][] = $to_email;
                        } 
                        else 
                        {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                            $log['users']['mail_failed'][] = $to_email;
                        }
                    }
                }
                
            } else {
                echo "No Data";
            }

            $cron_log['job_log'] = json_encode($log);
            $cron_log['job_status'] = ($log['total_records']==$email_counter['email_sent'])?1:2;
            $cron_log['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);

            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'] . " <br/> Campaign Name : " .$cron['job_id'];
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        }
    }    
}
