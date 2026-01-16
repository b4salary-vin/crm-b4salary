<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once (dirname(__FILE__) . "/CronController.php");

class Automate extends CronController {

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
    }


    public function autoLeadAssignSms()
    {
        $cron['job_id']     = 'autoLeadAssignSms#'.date('Y-m-d_H');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 0;
        $cron['job_name']   = 'autoLeadAssignSms';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__);
        $cron['function']   = str_replace(__CLASS__.'::','',__METHOD__);
        $cronData = $this->checkCronJob($cron);
        if(isset($cronData['template']))
        {
            $template = $cronData['template'];
        }
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            $results  = $this->db->query("SELECT l.lead_id, l.lead_screener_assign_datetime, l.first_name, l.lead_screener_assign_user_id, l.mobile, lc.alternate_mobile, u.name as u_name, u.mobile as u_mobile FROM leads l INNER JOIN lead_customer lc ON lc.customer_lead_id = l.lead_id INNER JOIN users u ON u.user_id = l.lead_screener_assign_user_id WHERE l.status = 'LEAD-INPROCESS' AND l.lead_screener_assign_user_id is not null AND l.lead_credit_assign_user_id is null AND lead_screener_assign_datetime >= NOW() - INTERVAL 90 MINUTE")->result_array();
            $log['total_records'] = count($results)??0;
            $sms_counter = array('sms_sent' => 0, 'sms_failed' => 0);
            require(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();
            $sms_counter['sms_sent'] = 0;
            if(!empty($results))
            {
                foreach ($results as $key =>  $result)
                {
                    $name       = $result['first_name']??'customer';
                    $message    = str_replace('#customer#',rtrim($name), $template['message']);
                    $message    = str_replace('#screener#',rtrim($result['u_name']), $message);
                    $message    = str_replace('#screener_mobile#',rtrim($result['u_mobile']), $message);
                    $request['templateid'] = $template['templateid']??'1707173088554772578';
                    $request['headerid']   = $template['headerid']??'RFINSL';
                    $request['sms']        = $message ?? "Dear P " . $name . ", your lead is allocated to " . $result['u_name'] . ". Please call/WhatsApp them at " . $result['u_mobile'] . " or email at info@suryaloan.com for further assistance. Team RFINSL";
                    $request['mobile']     = $result['mobile'];
                    $request['alternate_mobile'] = $result['alternate_mobile'];
                    $msg_already_sent = $this->db->where('sms_content',$request['sms'])->get('api_sms_logs')->row_array();
                    if(empty($msg_already_sent))
                    {
                        echo $message."</br>";
                        $send_sms = $CommonComponent->payday_sms_api(2, $result['lead_id'], $request);
                        if($send_sms['status'] == 1)
                        {
                            $request['msg_send'] = $request['mobile'];
                            $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                        }
                        else
                        {
                            $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                            if (!empty($request['alternate_mobile']))
                            {
                                $send_sms = $CommonComponent->payday_sms_api(2, $result['lead_id'], $request);
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

                    //$log['response'][$key] = $request;
                }
            }
            $log['final_msg_sent'] = $sms_counter['sms_sent'];
            $cron_log['job_log'] = json_encode($log);
            $cron_log['job_status'] = ($log['total_records']==$sms_counter['sms_sent'])?1:2;
            $cron_log['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'] . " <br/> Campaign Name : " .$cron['job_id'];
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
            echo "Total Records ".$log['total_records']." Found. Total sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'];

        }
    }

    public function autoBlackListCustomer()
    {
        $cron['job_id']     = 'autoBlackListCustomer#'.date('Y-m-d');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 0;
        $cron['job_name']   = 'autoBlackListCustomer';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__);
        $cronData = $this->checkCronJob($cron);
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            //$day_counter = 15;
            $this->db->trans_start();
            $results = $this->db->query("SELECT l.lead_id, l.loan_no, l.first_name, lc.middle_name, lc.sur_name, l.mobile, l.pancard, l.email, lc.alternate_email, lc.alternate_mobile, lc.dob, l.city_id, l.state_id, l.created_on, l.updated_on, l.lead_active, l.lead_deleted, cam.repayment_date, cam.repayment_amount, SUM(coll.received_amount) as total_received, (cam.repayment_amount -SUM(coll.received_amount)) as total_pending  FROM leads as l INNER JOIN lead_customer as lc ON lc.customer_lead_id = l.lead_id INNER JOIN  `credit_analysis_memo` as cam ON cam.lead_id = l.lead_id LEFT JOIN collection as coll ON coll.lead_id = cam.lead_id WHERE cam.repayment_date <= (NOW() - INTERVAL 15 DAY) AND STATUS in (14,16) AND l.lead_black_list_flag = 0 GROUP by cam.lead_id;")->result_array();
            $log['recordsFound'] = count($results);
            if(count($results))
            {
                foreach($results as $key => $result):
                    $log['users'][$key]['lead_id']                = $result['lead_id'];
                    $exist = $this->db->where('bl_customer_pancard',$result['pancard'])->get('customer_black_list')->row_array();
                    if(!count($exist))
                    {
                        $blacklist['bl_lead_id']                = $result['lead_id'];
                        $blacklist['bl_loan_no']                = $result['loan_no'];
                        $blacklist['bl_customer_first_name']    = $result['first_name'];
                        $blacklist['bl_customer_middle_name']   = $result['middle_name'];
                        $blacklist['bl_customer_sur_name']      = $result['sur_name'];
                        $blacklist['bl_customer_mobile']        = $result['mobile']??'9891914101';
                        $blacklist['bl_customer_alternate_mobile'] = $result['alternate_mobile'];
                        $blacklist['bl_customer_pancard']       = $result['pancard'];
                        $blacklist['bl_customer_dob']           = $result['dob'];
                        $blacklist['bl_customer_email']         = $result['email'];
                        $blacklist['bl_customer_alternate_email']= $result['alternate_email'];
                        $blacklist['bl_city_id']                 = $result['city_id'];
                        $blacklist['bl_state_id']                = $result['state_id'];
                        $blacklist['bl_created_on']              = $result['created_on'];
                        $blacklist['bl_updated_on']              = $result['updated_on'];
                        $blacklist['bl_created_user_id']         = 1;
                        $blacklist['bl_active']                  = $result['lead_active'];
                        $blacklist['bl_deleted']                 = $result['lead_deleted'];
                        $blacklist['bl_reason_id']               = 3;
                        $blacklist['bl_reason_remark']           = 'Hard Recovery. Payment is not made within 15 days of repayment date.';
                        $this->db->insert('customer_black_list',$blacklist);
                        $this->db->where('lead_id',$result['lead_id'])->update('leads',array('lead_black_list_flag'=>1));
                        $insert_id[] = $this->db->insert_id();
                    }
                endforeach;
            }
        }
        $log['recordsBlacklist'] = count($insert_id);
        $cron_log['job_log'] = json_encode($log);
        $cron_log['completed_at'] = date('Y-m-d H:i:s');
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            $cron_log['job_status'] = 2;
            echo "Customer Blacklisting Failed.";
        }
        else
        {
            $this->db->trans_commit();
            $cron_log['job_status'] = 1;
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "Total Records found: ".$log['recordsFound'];
            $email_data['message'] .= "<br>Total Records Blacklisted : ".$log['recordsBlacklist'] ;
            $email_data['message'] .= "<br>Campaign Name : ".$cron['job_id'] ;
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
            echo "Total ".count($insert_id)." Records Blacklisted";
        }

        $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);         exit;
    }


    public function autoHoldToReject()
    {
        $cron['job_id']     = 'autoHoldToReject#'.date('Y-m-d');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 0;
        $cron['job_name']   = 'autoHoldToReject';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__);
        $cronData = $this->checkCronJob($cron);
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            $query = "SELECT lead_id FROM leads WHERE lead_screener_assign_datetime <= (NOW() - INTERVAL 3 DAY) AND ((stage = 'S3' AND lead_status_id = '3') OR (stage = 'S6' AND lead_status_id = '6'))";
            $results = $this->db->query($query)->result_array();
            $log['holdRecordsFound'] = count($results);
            if ($results) {
                $data = [
                    'status' => 'REJECT',
                    'stage' => 'S9',
                    'lead_status_id' => '9',
                    'otp' => 990099,
                    'updated_on'    => date('Y-m-d H:i:s')
                ];
                $leads = implode(',',array_column($results,'lead_id'));
                $this->db->where("lead_id IN (".$leads.")")->update('leads',$data);
                $log['recordsRejected'] = $this->db->affected_rows();
                $cron_log['job_status'] = ($log['holdRecordsFound'] == $log['recordsRejected'])?1:2;
                $cron_log['job_log'] = json_encode($log);
                $cron_log['completed_at'] = date('Y-m-d H:i:s');
                $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);
                echo "Total records" .count($results). "found and ". $log['recordsRejected'] . " records rejected";
                $email_data['email'] = $this->cron_notification_email;
                $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
                $email_data['message'] = "Total Hold Records found: ".$log['holdRecordsFound'];
                $email_data['message'] .= "<br>Total Records Rejected : ".$log['recordsRejected'] ;
                $email_data['message'] .= "<br>Campaign Name : ".$cron['job_id'] ;
                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
            } else {
                echo "No records found.";
            }
        }
    }


    public function autoAllocateLeads()
    {
        $cron['job_id']     = 'autoAllocateLeads#'.date('Y-m-d_H');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 0;
        $cron['job_name']   = 'autoAllocateLeads';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__);
        $cronData = $this->checkCronJob($cron);
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            $log = $this->allocateLeads();
            $cron_log['job_status'] = 1;
            $cron_log['job_log']    = json_encode($log);
            $cron_log['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "Total Records found: ".$log['total_records'];
            $email_data['message'] .= "<br>Total Records Updated : ".$log['recordsUpdated'] ;
            $email_data['message'] .= "<br>Campaign Name : ".$cron['job_id'] ;
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
        }
    }

    function allocateLeads($log = array())
    {
        $maxLead = 15;
        $sqlTotalLeads = "SELECT count(lead_id) as total FROM `leads` l WHERE l.status IN ('LEAD-NEW', 'LEAD-PARTIAL') AND l.first_name is NOT NULL";
        $newLeads = $this->db->query($sqlTotalLeads)->row_array();
        $newLeadsTotal = $newLeads['total'];
        $sqlActiveUsers = "SELECT user_role_type_id as screener_role, ual_user_id FROM user_activity_log ual INNER JOIN user_roles ur ON ur.user_role_id = ual.ual_role_id WHERE ual_id IN (SELECT max(ual_id) as ual_id FROM user_roles INNER JOIN user_activity_log ON ual_role_id = user_role_id INNER JOIN users ON user_id = user_role_user_id WHERE user_role_user_id NOT IN (SELECT users.user_id FROM user_roles INNER JOIN users ON users.user_id = user_role_user_id WHERE (user_role_type_id = 1 OR user_role_type_id > 3) AND user_role_active = 1 AND user_role_deleted = 0 GROUP BY user_role_user_id ORDER BY user_role_user_id) AND user_role_active = 1 AND user_role_deleted = 0 AND date(ual_datetime) = '".date('Y-m-d')."' GROUP BY user_role_user_id);";



        $activeusersList = $this->db->query($sqlActiveUsers)->result_array();
        $usersTotal = 0;
        foreach($activeusersList as $ak => $active)
        {
            if($active['screener_role'] == 2 )
            {
                $usersTotal = $usersTotal + 1;
            }
        }
        $leadsPerUser  = floor($newLeadsTotal/$usersTotal);
        $minLead        = !empty($leadsPerUser)?$leadsPerUser:1;
        $remainingLeads = $newLeadsTotal - ($leadsPerUser*$usersTotal);
        $log['total_records'] = $log['total_records'] + $newLeadsTotal;
        $log['recordsUpdated'] = 0;

        foreach($activeusersList as $ak => $active)
        {
            if($active['screener_role'] == 2 )
            {
                $newLeads = $this->db->query("SELECT lead_id FROM leads WHERE status IN ('LEAD-NEW','LEAD-PARTIAL') AND first_name
                IS NOT null")->result_array();
                $user = $this->db->query("SELECT count(lead_id) as total FROM leads WHERE status IN ('LEAD-INPROCESS') AND lead_screener_assign_user_id =  ".$active['ual_user_id'])->row_array();
                if(count($user))
                {
                    $total = $user['total'];
                }
                else
                {
                    $total = 0;
                }
                $log[$active['ual_user_id']]['finalInProcess'] = $total;
                $pending = $maxLead - $total;
                $pending = ($pending > $minLead)?$minLead:$pending;
                for($i = 0; $i<$pending; $i++)
                {
                    if(isset($newLeads[$i]['lead_id']))
                    {
                        if($log[$active['ual_user_id']]['pendingInProcess'] != 0)
                        {
                            $log[$active['ual_user_id']]['newAssigned'] = $i+1;
                        }
                        $assign['lead_screener_assign_user_id']  = $active['ual_user_id'];
                        $assign['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        $assign['status']                        = 'LEAD-INPROCESS';
                        $assign['stage']                         = 'S2';
                        $assign['lead_status_id']                = '2';
                        $assign['otp']                           = '220022';

                        //echo "<pre>"; print_r($assign);
                        $this->db->where('lead_id',$newLeads[$i]['lead_id'])->update('leads',$assign);
                        $lf['lead_id'] = $newLeads[$i]['lead_id'];
                        $lf['status']                       = 'LEAD-INPROCESS';
                        $lf['stage']                        = 'S2';
                        $lf['lead_followup_status_id']      = '2';
                        $lf['lead_followup_active']         = '1';
                        $lf['lead_followup_deleted']        = '0';
                        $lf['user_id']                      = $active['ual_user_id'];
                        $lf['remarks']                      = 'Lead Allocate By Auto Allocation Running at '.date('Y-m-d H:i:s');
                        $lf['created_on']                   = date('Y-m-d H:i:s');
                        $this->db->insert('lead_followup',$lf);
                        $log[$active['ual_user_id']]['inProcessLeads']    = $user['total'];
                        $log[$active['ual_user_id']]['pendingInProcess']  = $pending;
                        $log[$active['ual_user_id']]['finalInProcess']    = $log[$active['ual_user_id']]['finalInProcess']+1;
                        $log[$active['ual_user_id']]['newAssignedLeadId'][]       = $newLeads[$i]['lead_id'];
                        $log['recordsUpdated']++;
                    }
                }
            }
        }
        $log['recordsUpdated'] = $log['recordsUpdated'];
        return $log;
    }

    public function autoConvertLeadsNewS()
    {
        $cron['job_id']     = 'autoConvertLeadsNew#'.date('Y-m-d_H');
        $cron['started_at'] = date('Y-m-d H:i:s');
        $cron['job_type']   = 0;
        $cron['job_name']   = 'autoConvertLeadsNew';
        $cron['job_url']    = str_replace('::','/',LMS_URL.'CronJobs/'.__METHOD__);
        $cronData = $this->checkCronJob($cron);
        //$cronData['job_status'] = 2;
        if(!isset($cronData['job_status']) || ($cronData['job_status'] == 2))
        {
            $sqlUsersList = "SELECT count(lead_id) as total, lead_screener_assign_user_id as user_id FROM leads WHERE status IN ('LEAD-INPROCESS','LEAD-HOLD') AND lead_screener_assign_user_id NOT IN (SELECT user_id FROM users u INNER JOIN user_roles ur ON ur.user_role_user_id = u.user_id WHERE ur.user_role_type_id IN (2) AND ur.user_role_active = 1 AND ur.user_role_deleted = 0 AND u.user_id NOT IN (SELECT user_role_user_id FROM user_roles WHERE user_role_type_id in (1) AND user_role_type_id < 4 AND user_role_active = 1 AND user_role_deleted = 0)) GROUP BY lead_screener_assign_user_id ORDER BY lead_screener_assign_user_id;";
            $usersList = $this->db->query($sqlUsersList)->result_array();
            $log['total_records'] = array_sum(array_column($usersList,'total'));
            $log['total_records_updated'] = 0;
            foreach($usersList as $uk => $user)
            {
                $leads = $this->db->query("SELECT lead_id FROM leads WHERE status IN ('LEAD-INPROCESS', 'LEAD-HOLD') AND lead_screener_assign_user_id = ".$user['user_id'])->result_array();
                foreach($leads as $lk => $lead)
                {
                    $update['status']                   = 'LEAD-NEW';
                    $update['stage']                    = 'S1';
                    $update['lead_status_id']           = '1';
                    $update['lead_screener_assign_user_id']  = null;
                    $update['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                    $update['otp']                      = 110011;
                    $this->db->where('lead_id',$lead['lead_id'])->update('leads',$update);
                    $lf['lead_id']                      = $lead['lead_id'];
                    $lf['status']                       = 'LEAD-NEW';
                    $lf['stage']                        = 'S1';
                    $lf['lead_followup_status_id']      = '1';
                    $lf['lead_followup_active']         = '1';
                    $lf['lead_followup_deleted']        = '0';
                    $lf['user_id']                      = null;
                    $lf['remarks']                      = 'Lead Converted to Lead New By Cron Running at '.date('Y-m-d H:i:s');
                    $lf['created_on']                   = date('Y-m-d H:i:s');
                    $this->db->insert('lead_followup',$lf);
                    $log['total_records_updated'] = $log['total_records_updated']+$this->db->affected_rows();
                    $log['users'][$user['user_id']][] = $lead['lead_id'];
                }
            }
            $cron_log['job_status'] = $log['total_records']==$log['total_records_updated']?1:2;
            $cron_log['job_log']    = json_encode($log);
            $cron_log['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('job_id',$cron['job_id'])->update('cron_logs',$cron_log);
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = $cron['job_name'] ." - start time :" . $cron['started_at'] . " | end time : " . $cron_log['completed_at'];
            $email_data['message'] = "Total Hold Records found: ".$log['holdRecordsFound'];
            $email_data['message'] .= "<br>Total Records Rejected : ".$log['recordsRejected'] ;
            $email_data['message'] .= "<br>Campaign Name : ".$cron['job_id'] ;
            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);
        }
    }
}
