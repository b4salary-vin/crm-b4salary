<?php

defined('BASEPATH') or exit('No direct script access allowed');

class LoginController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Admin_Model');
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Users/Menu_Model', 'Menus');
        $this->load->model('Users/User_Activity_Model', 'UAM');
        date_default_timezone_set('Asia/Kolkata');
        //        define("updated_at", date('Y-m-d H:i:s'));
    }

    public function islogin() 
    {
        if (!isset($_SESSION['isUserSession']['email']) && $_SESSION['isUserSession']['email'] == NULL) {
            return redirect(base_url());
        } else {
            $isEmail = MD5(date('Y-m-d h:i:s') . $_SESSION['isUserSession']['email']);
            echo json_encode($isEmail);
        }
    }

    public function login() 
    {
        if(!empty($_SESSION['isUserSession']))
        {

            return redirect(base_url('home' .DS. $this->encrypt->encode($_SESSION['isUserSession']['labels'])), 'refresh');
        }
        else 
        {
            $data['url'] = 'dashboard';
            $this->load->view('UMS/login', $data);
        }
    }

    function old_password($str) {
        if (md5($str) === $_SESSION['user_password']) {
            return false;
        } else {
            return TRUE;
        }
    }

    public function home($labels) {
        // error_reporting(E_ALL);
        // ini_set("display_errors", 1);
        $login = new IsLogin();
        $login->index();
        if (true) {
            $data = array();
            $labelname = $_SESSION['isUserSession']['labels'];
            $where = "company_id=" . company_id . " AND product_id=" . product_id;
            $userscrId = [22, 56, 59, 98, 75, 107, 109, 112, 70];
            $usersMrId = [51, 91];
            $userId = $_SESSION['isUserSession']['user_id'];
            switch ($labelname) {
                case 'SA': // Super Admin
                    break;
                case 'CA': // Client Admin Add  OR user_labels='SA' - MIS-REPORT, EXPORT-REPORT AND SCR1 Screen Role (OR user_labels='SCR1')
                    //$where .= " AND is_active='1' OR user_labels='SA' OR user_labels='EN1'";
                    $where .= " AND is_active='1' AND user_labels in('AU','OL','CR1','SCR1','CR2','CR3','DS1','DS2','AC1','AC2','CO1','CO2','CO3','CC','REJ','SA','EN1')";
                    break;
                case 'CR1': // Credit (Screener)
                    if (!in_array($userId, $userscrId)) {
                        $where .= " AND is_active='1' AND (user_labels='CR1' OR user_labels='REJ' OR user_labels='SCR1' OR user_labels='EN1')";
                    } else {
                        $where .= " AND is_active='1' AND (user_labels='CR1' OR user_labels='REJ' )";
                    }
                    break;
                case 'CR2': // Credit (Credit Manager)
                    $where .= " AND is_active='1' AND ( user_labels='CR2' OR user_labels='CR3' OR user_labels='REJ') OR id=63";
                    break;
                case 'CR3': // Credit (Credit Head)
                    $where .= " AND is_active='1' AND (user_labels='CC' OR user_labels='CR1' OR user_labels='CR2' OR user_labels='CR3' OR user_labels='DS1' OR user_labels='DS2' OR user_labels='REJ' OR user_labels='SCR1' OR user_labels='SA' ) OR id=63";
                    break;
                case 'DS1': // Disbursal (Disbursal Team)
                    $where .= " AND is_active='1' AND (user_labels='DS1' OR user_labels='DS2')";
                    break;
                case 'DS2': // Disbursal (Disbursal Team)
                    $where .= " AND is_active='1' AND (user_labels='CC' OR user_labels='DS1' OR user_labels='DS2')";
                    break;
                case 'CFE1': // collection (collection Field Executive CFE1)
                    $where .= " AND is_active='1' AND (user_labels='CFE1' OR user_labels='CO1' OR user_labels='CO2' OR user_labels='CO3 OR user_labels='AC2')";
                    break;
                case 'CO1': // collection (collection Executive CO1)
                    $where .= " AND is_active='1' AND (user_labels='CO1' OR user_labels='CO2' OR user_labels='CO3')";
                    break;
                case 'CO2': // collection (collection SCM)
                    $where .= " AND is_active='1' AND (user_labels='CFE1' OR user_labels='CO1' OR user_labels='CO2' OR user_labels='CO3')";
                    break;
                case 'CO3': // collection (collection Head)
                    $where .= " AND is_active='1' AND (user_labels='CC' OR user_labels='CFE1' OR user_labels='CO1' OR user_labels='CO2' OR user_labels='CO3' OR user_labels='SA')";
                    break;
                case 'AC1': // collection (Closing Team)
                    $where .= " AND is_active='1' AND (user_labels='AC1' OR user_labels='CO3' OR user_labels='AC2' OR user_labels='CA')";
                    break;
                case 'AC2': // collection (Closing Team)
                    $where .= " AND is_active='1' AND (user_labels='CC' OR user_labels='AC1' OR user_labels='CO3' OR user_labels='AC2' OR user_labels='DS2' OR user_labels='CA')";
                    break;
                case 'MR': // Marekting
                    if (in_array($userId, $usersMrId)) {
                        $where .= " AND is_active='1' AND (user_labels='MR' OR user_labels='CA' OR user_labels='SA' OR user_labels='EN1')";
                    } else {
                        $where .= " AND is_active='1' AND (user_labels='MR' OR user_labels='CA' OR user_labels='SA')";
                    }
                    break;
                case 'OL': // Other
                    $where .= " AND is_active='1' AND (user_labels='OL')";
                    break;
                case 'CC': // Customer Care
                    $where .= " AND is_active='1' AND (user_labels='CC' OR user_labels='CO1' OR user_labels='CR1' OR user_labels='CR2' OR user_labels='CR3' OR user_labels='AC2' OR user_labels='DS2' OR user_labels='REJ') OR user_labels='EN1' OR user_labels='SCR1'";
                    break;
                case 'AU': // Audit
                    $where .= " AND is_active='1' AND user_labels in('AU','OL','CR1','SCR1','CR2','CR3','DS1','DS2','AC1','AC2','CO1','CO2','CO3','CC','REJ','SA', 'EN1')";
                    break;
                case 'ST': //Support Pannel
                    $where .= " AND is_active='1' AND (user_labels='ST')";
                    break;
                case 'AM': //Audit Manager
                    $where .= " AND is_active='1' AND user_labels in('AM') OR id in(8, 59, 60, 61, 62, 7)";
                    break;
                case 'AH': //Audit Head
                    $where .= " AND is_active='1' AND user_labels in('AM','AH') OR id in(8, 59, 60, 61, 62, 9, 7)";
                    break;
                default:
                    $where .= " AND is_active='1' AND  user_labels='OL'";
                    break;
            }

            if (in_array($_SESSION['isUserSession']['user_id'], [204, 203])) {
                $where .= " OR id=9 ";
            }

            $data['menusList'] = $this->Menus->menusList($where);
            return $this->load->view('UMS/home', $data);
        }
    }

    function getLocation() 
    {
        if ($this->input->post('latitude')) 
        {

            if (empty($this->input->post('latitude')) || empty($this->input->post('longitude'))) {
                unset($_SESSION['isUserSession']);
                redirect(base_url());
            } else {
                $_SESSION['login']  = $_POST;
                return redirect(base_url('dashboard'), 'refresh');
            }
        } elseif ($this->input->post('email') && $this->input->post('password')) {
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('err', validation_errors());
                return redirect(base_url(), 'refresh');
            } else {
                $input_email = strtoupper(trim($_POST['email']));
                $input_password = MD5(trim($_POST['password']));
                $conditions['U.email'] = $input_email;
                $conditions['U.password'] = $input_password;
                $isValidUser = $this->Admin_Model->user_authentication($conditions);
                if (empty($isValidUser['status'])) {
                    $this->session->set_flashdata('err', '<p>Email or Password is invalid.</p>');
                    return redirect(base_url(), 'refresh');
                } else {
                    $data['email']      = $_POST['email'];
                    $data['password']   = $_POST['password'];
                    $this->load->view('location', $data);
                }
            }
        } else {
            redirect(base_url());
        }
    }

    public function dashboard() 
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') 
        {
            //echo "<pre>"; print_r($_POST); exit;
            $bcc_email = CTO_EMAIL;
            $team_leader_email = "";
            $conditions['U.email'] = $_POST['email'];;
            $conditions['U.password'] = MD5(trim($_POST['password']));
            $isValidUser = $this->Admin_Model->user_authentication($conditions);
            if (!empty($isValidUser['status'])) 
            {
                if (!empty($isValidUser['user_data']['user_logins_failed_count']) && $isValidUser['user_data']['user_logins_failed_count'] >= 3) 
                {
                    $this->session->set_flashdata('err', "Your account has been locked. Please reset your password using forgot password feature..");
                    return redirect(base_url(), 'refresh');
                }
                if (!empty($_POST['latitude'] && !empty($_POST['longitude']))) 
                {
                    $_SESSION['latitude']           = $_POST['latitude'];
                    $_SESSION['longitude']          = $_POST['longitude'];
                }
                $isValidUser['user_data']['current_login_time'] = date("Y-m-d H:i:s");
                $user_total_login_count = $isValidUser['user_data']['user_total_login_count'] + 1;
                $this->session->set_userdata('isUserSession', $isValidUser['user_data']);
                $date1 = new DateTime($isValidUser['user_data']['user_last_password_reset_datetime']);
                $date2 = new DateTime();
                // Calculate the difference
                $interval = $date1->diff($date2);

                if ($interval->days > 14 || empty($isValidUser['user_data']['user_last_password_reset_datetime'])) {
                    redirect(base_url('password-expired'));
                }
                $this->session->sess_regenerate(true);
                $update_user_data = array(
                    'user_last_login_datetime' => date('Y-m-d H:i:s'),
                    'user_last_login_ip' => $this->input->ip_address(),
                    'user_total_login_count' => $user_total_login_count,
                    'user_token' =>  session_id(),
                    'user_logins_failed_count' => 0
                );
                $this->db->where('user_id', $isValidUser['user_data']['user_id'])->update('users', $update_user_data);
                $this->Admin_Model->insertUserActivity($isValidUser['user_data']['user_id'], $isValidUser['user_data']['user_role_id'], 1);
                $email_subject = BRAND_NAME . " FINTECH USER LOGIN | " . $isValidUser['user_data']['name'] . " | LOGIN TIME : " . date("d-m-Y H:i:s");

                $email_message = '<!DOCTYPE html>
                                <html>
                                <head>
                                    <title>' . $email_subject . '</title>
                                </head>
                                <body style="font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px;">
                                    <table role="presentation" width="100%" height="100vh" cellspacing="0" cellpadding="0" border="0" style="width: 100%; text-align: center;">
                                        <tr>
                                            <td align="center" valign="middle">
                                                <div style="max-width: 650px; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); text-align: center;">
                                                    <div style="font-weight: bold; background: #2575fc; color: white; text-align: center; padding: 15px; border-radius: 10px 10px 0 0; font-size: 20px;">
                                                        Welcome, ' . $isValidUser['user_data']['name'] . '!
                                                    </div>
                                                    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #ddd;">
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">URL</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . base_url() . '</td></tr>
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;User</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . ucwords($isValidUser['user_data']['email']) . '</td></tr>
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;Status</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;">Success</td></tr>
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;IP</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->input->ip_address() . '</td></tr>
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Platform</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->platform() . '</td></tr>
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Browser&nbsp;&&nbsp;Version</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->browser() . ' ' . $this->agent->version() . '</td></tr>
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Agent&nbsp;String</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->agent_string() . '</td></tr>
                                                        <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Last&nbsp;Activity</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . date('d-m-Y H:i A') . '</td></tr>
                                                    </table>
                                                    <div style="background: #f9f9f9; color: #666; text-align: center; font-size: 14px; padding: 20px; margin-top: 20px; border-radius: 0 0 10px 10px; border-top: 1px solid #ddd;">
                                                        <p style="margin: 0;">&copy; 2025 ' . BRAND_NAME . '. All rights reserved.</p>
                                                        <p style="margin: 0;">
                                                            <a href="' . WEBSITE_URL . 'privacypolicy' . '" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                                                            <a href="' . WEBSITE_URL . 'termsandconditions' . '" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                                                            <a href="' . WEBSITE_URL . 'contact' . '" target="_blank" style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                                                        </p>
                                                        <div style="margin-top: 10px;">
                                                            <a href="' . FACEBOOK_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . FACEBOOK_ICON . '" alt="Facebook" style="width: 30px; height: 30px;"></a>
                                                            <a href="' . TWITTER_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . TWITTER_ICON . '" alt="Twitter" style="width: 30px; height: 30px;"></a>
                                                            <a href="' . LINKEDIN_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . LINKEDIN_ICON . '" alt="LinkedIn" style="width: 30px; height: 30px;"></a>
                                                            <a href="' . INSTAGRAM_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . INSTAGRAM_ICON . '" alt="Instagram" style="width: 30px; height: 30px;"></a>
                                                            <a href="' . YOUTUBE_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . YOUTUBE_ICON . '" alt="YouTube" style="width: 30px; height: 30px;"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </body>
                                </html>';

                if (!empty($isValidUser['user_data']['email'])) 
                {
                    // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
                    // common_send_email($isValidUser['user_data']['email'], $email_subject, $email_message, $bcc_email, $team_leader_email, NOREPLY_EMAIL);
                }

                $redirect_home_url = "home/";
                if (isset($isValidUser['user_data']['labels']) && $isValidUser['user_data']['labels'] == "LD1") {
                    $redirect_home_url = "loan-kyc-docs/";
                }

                return redirect(base_url($redirect_home_url . $this->encrypt->encode($isValidUser['user_data']['labels'])), 'refresh');
            } else {

                $userDetails = $this->Admin_Model->getUserDetailsByEmail($input_email);

                if ($userDetails['status'] == 1) {

                    if ($userDetails['user_data']['user_logins_failed_count'] >= 3) {
                        $this->session->set_flashdata('err', "Your account has been locked. Please reset your password using forgot password feature.");

                        $email_subject = BRAND_NAME . " FINTECH LOGIN LOCKED | " . $userDetails['user_data']['name'] . "| LOGIN TIME : " . date("d-m-Y H:i:s");

                        $email_message = '<!DOCTYPE html>
                        <html>
                        <head>
                            <title>' . $email_subject . '</title>
                        </head>
                        <body style="font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px;">
                            <table role="presentation" width="100%" height="100vh" cellspacing="0" cellpadding="0" border="0" style="width: 100%; text-align: center;">
                                <tr>
                                    <td align="center" valign="middle">
                                        <div style="max-width: 650px; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); text-align: center;">
                                            <div style="font-weight: bold; background: #2575fc; color: white; text-align: center; padding: 15px; border-radius: 10px 10px 0 0; font-size: 20px;">
                                                Welcome, ' . $isValidUser['user_data']['name'] . '!
                                            </div>
                                            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #ddd;">
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">URL</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . base_url() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;User</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . ucwords($isValidUser['user_data']['email']) . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;Status</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd; color: rgb(255, 0, 0)">Locked</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;IP</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->input->ip_address() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Platform</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->platform() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Browser&nbsp;&&nbsp;Version</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->browser() . ' ' . $this->agent->version() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Agent&nbsp;String</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->agent_string() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Last&nbsp;Activity</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . date('d-m-Y H:i A') . '</td></tr>
                                            </table>
                                            <div style="background: #f9f9f9; color: #666; text-align: center; font-size: 14px; padding: 20px; margin-top: 20px; border-radius: 0 0 10px 10px; border-top: 1px solid #ddd;">
                                                <p style="margin: 0;">&copy; 2025 ' . BRAND_NAME . '. All rights reserved.</p>
                                                <p style="margin: 0;">
                                                    <a href="' . WEBSITE_URL . 'privacypolicy' . '" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                                                    <a href="' . WEBSITE_URL . 'termsandconditions' . '" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                                                    <a href="' . WEBSITE_URL . 'contact' . '" target="_blank" style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                                                </p>
                                                <div style="margin-top: 10px;">
                                                    <a href="' . FACEBOOK_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . FACEBOOK_ICON . '" alt="Facebook" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . TWITTER_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . TWITTER_ICON . '" alt="Twitter" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . LINKEDIN_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . LINKEDIN_ICON . '" alt="LinkedIn" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . INSTAGRAM_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . INSTAGRAM_ICON . '" alt="Instagram" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . YOUTUBE_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . YOUTUBE_ICON . '" alt="YouTube" style="width: 30px; height: 30px;"></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>';

                        if (!empty($userDetails['user_data']['email'])) {
                            // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
                            // common_send_email($input_email, $email_subject, $email_message, $bcc_email, $team_leader_email, NOREPLY_EMAIL);
                        }

                        if (!empty($isValidUser['user_data']['user_logins_failed_count']) && $isValidUser['user_data']['user_logins_failed_count'] >= 3) {
                            $this->session->set_flashdata('err', "Your account has been locked. Please reset your password using forgot password feature..");
                            return redirect(base_url(), 'refresh');
                        }
                    } else {
                        $this->db->where('email', $input_email);
                        $this->db->set('user_logins_failed_count', 'user_logins_failed_count+1', FALSE);
                        $this->db->set('user_token', session_id());
                        $this->db->update('users');

                        $this->session->set_flashdata('err', "Invalid credentails, Please try with correct details..");
                        $email_subject = BRAND_NAME . " FINTECH UNSUCCESSFUL LOGIN | " . $userDetails['user_data']['name'] . "| LOGIN TIME : " . date("d-m-Y H:i:s");
                        $email_message = '<!DOCTYPE html>
                        <html>
                        <head>
                            <title>' . $email_subject . '</title>
                        </head>
                        <body style="font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px;">
                            <table role="presentation" width="100%" height="100vh" cellspacing="0" cellpadding="0" border="0" style="width: 100%; text-align: center;">
                                <tr>
                                    <td align="center" valign="middle">
                                        <div style="max-width: 650px; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); text-align: center;">
                                            <div style="font-weight: bold; background: #2575fc; color: white; text-align: center; padding: 15px; border-radius: 10px 10px 0 0; font-size: 20px;">
                                                Welcome, ' . $isValidUser['user_data']['name'] . '!
                                            </div>
                                            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #ddd;">
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">URL</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . base_url() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;User</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . ucwords($isValidUser['user_data']['email']) . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;Status</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd; color: rgb(255, 0, 0)">Failed</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Login&nbsp;IP</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->input->ip_address() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Platform</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->platform() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Browser&nbsp;&&nbsp;Version</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->browser() . ' ' . $this->agent->version() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Agent&nbsp;String</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . $this->agent->agent_string() . '</td></tr>
                                                <tr><td style="font-weight: bold; background: #2575fc; color: white; padding: 12px; border: 1px solid #ddd;">Last&nbsp;Activity</td><td style="padding: 12px; background-color: #f9f9f9; border: 1px solid #ddd;"> ' . date('d-m-Y H:i A') . '</td></tr>
                                            </table>
                                            <div style="background: #f9f9f9; color: #666; text-align: center; font-size: 14px; padding: 20px; margin-top: 20px; border-radius: 0 0 10px 10px; border-top: 1px solid #ddd;">
                                                <p style="margin: 0;">&copy; 2025 ' . BRAND_NAME . '. All rights reserved.</p>
                                                <p style="margin: 0;">
                                                    <a href="' . WEBSITE_URL . 'privacypolicy' . '" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                                                    <a href="' . WEBSITE_URL . 'termsandconditions' . '" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                                                    <a href="' . WEBSITE_URL . 'contact' . '" target="_blank" style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                                                </p>
                                                <div style="margin-top: 10px;">
                                                    <a href="' . FACEBOOK_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . FACEBOOK_ICON . '" alt="Facebook" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . TWITTER_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . TWITTER_ICON . '" alt="Twitter" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . LINKEDIN_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . LINKEDIN_ICON . '" alt="LinkedIn" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . INSTAGRAM_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . INSTAGRAM_ICON . '" alt="Instagram" style="width: 30px; height: 30px;"></a>
                                                    <a href="' . YOUTUBE_LINK . '" target="_blank" style="margin: 0 5px;"><img src="' . YOUTUBE_ICON . '" alt="YouTube" style="width: 30px; height: 30px;"></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>';

                        if (!empty($userDetails['user_data']['email'])) {
                            // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
                            // common_send_email($input_email, $email_subject, $email_message, $bcc_email, $team_leader_email, NOREPLY_EMAIL);
                        }
                    }
                } else {
                    $this->session->set_flashdata('err', "Invalid credentails, Please try with correct details.");
                }
                return redirect(base_url(''), 'refresh');
            }
        } else {
            $redirect_home_url = "home/";

            if (isset($_SESSION['isUserSession']['labels']) && $_SESSION['isUserSession']['labels'] == "LD1") {
                $redirect_home_url = "loan-kyc-docs/";
            }
            return redirect(base_url($redirect_home_url . $this->encrypt->encode($_SESSION['isUserSession']['labels'])), 'refresh');
        }
    }

    public function defaultLoginRole($permission_user_id) {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        } else if ($permission_user_id != $_SESSION['isUserSession']['user_id']) {
            $json['err'] = 'Invalid access. Please login again.';
            echo json_encode($json);
            return false;
        } else if (empty($_POST['role_id'])) {
            $json['err'] = 'Invalid access. Please login again..';
            echo json_encode($json);
            return false;
        }

        if (!empty($_POST['role_id'])) {

            $role_id = intval($_POST['role_id']);

            $conditions = array();
            $conditions['U.user_id'] = $permission_user_id;
            $conditions['UR.user_role_type_id'] = $role_id;

            $isValidUser = $this->Admin_Model->user_authentication($conditions);

            if (!empty($isValidUser['status'])) {
                $this->session->set_userdata('isUserSession', $isValidUser['user_data']);
                $this->Admin_Model->insertUserActivity($permission_user_id, $isValidUser['user_data']['user_role_id'], 2);
                $json['msg'] = "Role has been changed successfully.";
                echo json_encode($json);
            } else {
                $json['err'] = 'Invalid access. Please login again...';
                echo json_encode($json);
                return false;
            }
        }
    }

    public function myProfile() {
        $data = array();
        if (!empty($_SESSION['isUserSession']['user_id'])) {
            $user_id = $_SESSION['isUserSession']['user_id'];

            $type_id = 0;

            if (agent == "CR1") {
                $type_id = 1;
            } else if (agent == "CO1") {
                $type_id = 2;
            }

            $data = $this->Admin_Model->getUserProfileById($user_id);
            if (in_array(agent, ['CR1', 'CR2', 'CO1'])) {
                $allocation_data = $this->UAM->get_user_allocation_data($user_id);
                $achieve_data = $this->UAM->get_user_achieve_data($user_id, $type_id);
                $collection_history = $this->UAM->get_user_collection_history_data($user_id, $type_id);
                $target_history = $this->UAM->get_user_target_history_data($user_id, $type_id);
            }


            if (!empty($data['status'])) {
                $data['user_data']['add_ifsc_flag'] = 0;
                $data['user_data']['target_flag'] = 0;
                $data['user_data']['target_data'] = [];
                $data['user_data']['achieved_target'] = [];
                $data['user_data']['collection_history'] = [];
                $data['user_data']['target_history'] = [];
                $data['user_data']['allocation_data'] = [];

                if (in_array(agent, ['CR2', 'CR3'])) {
                    $data['user_data']['add_ifsc_flag'] = 1;
                }

                if (in_array(agent, ['CR1', 'CR2', 'CO1'])) {
                    $data['user_data']['allocation_data'] = $allocation_data;

                    if ($achieve_data['status'] == 1) {
                        $data['user_data']['achieved_target'] = $achieve_data['data'];
                    }

                    if ($collection_history['status'] == 1 && agent == 'CR1') {
                        $data['user_data']['collection_history'] = $collection_history['data'];
                    }

                    $data['user_data']['target_flag'] = $target_history['target_flag'];

                    if ($target_history['status'] == 1) {
                        $data['user_data']['target_history'] = $target_history['data'];
                    }
                }
                $this->load->view('profile', $data['user_data']);
            }
        } else {
            $this->session->set_flashdata('err', "Session Expired, Try once more.");
            return redirect(base_url(), 'refresh');
        }
    }

    public function generatePassword() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('password', 'Password', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('err', validation_errors());
                return redirect(base_url('changePassword'), 'refresh');
            } else {
                if (!empty($_SESSION['isUserSession']['user_id'])) {
                    $user_id = $_SESSION['isUserSession']['user_id'];
                    $input_password = $this->input->post('password');
                    $hash = MD5($input_password);

                    $sql = $this->db->select('user_id, name, email, mobile')
                        ->where('user_id', $user_id)
                        ->get('users')->row();
                    $name = $sql->name;
                    $email = $sql->email;
                    $mobile = $sql->mobile;

                    $this->db->where('user_id', $user_id)->update('users', ['password' => $hash]);

                    $this->notification($name, $mobile, $email, $input_password);
                    $this->logout();
                } else {
                    $this->session->set_flashdata('err', "Session Expired, Try once more.");
                    return redirect(base_url(), 'refresh');
                }
            }
        }
    }

    public function notification($fullName, $mobile, $email, $pass) {
        $msg = "Dear " . ucfirst($fullName) . ", \n CRM Login details are. \n Username - " . $email . "\n Password - " . $pass . " \n URL - " . base_url() . " \n Please don't share it with anyone. \n Thanks - Authorised by, Organization \n";

        $username = urlencode("namanfinl");
        $password = urlencode("6I1c0TdZ");
        $message = urlencode($msg);
        $destination = $mobile;
        $source = "LOANPL";
        $type = "0";
        $dlr = "1";

        $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message";
        $url = "http://sms6.rmlconnect.net/bulksms/bulksms";

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));
        $output = curl_exec($ch);

        curl_close($ch);
    }

    public function logout() {
        if (!empty($_SESSION['isUserSession']['user_id'])) {
            $user_id = $_SESSION['isUserSession']['user_id'];
            $user_role_id = $_SESSION['isUserSession']['user_role_id'];
            $query = $this->db->where('user_id', $user_id)->get('users')->row_array();
            $query_email = $query['email'];

            if (!empty($this->session->flashdata('login_err'))) {
                $error = $this->session->flashdata('login_err');
            } else {
                $error = 'Session Expired!';
            }

            $data = array(
                'updated_on' => date('Y-m-d H:i:s'),
            );

            $this->db->where('user_id', $user_id)->update('users', $data);
            $this->Admin_Model->insertUserActivity($user_id, $user_role_id, 3);
            session_destroy();
            // print_r($error);die;
            $this->session->set_flashdata('err', $error);
            return redirect(base_url(), 'refresh');
        } else {
            // session_destroy();
            $this->session->set_flashdata('err', 'Session Expired!');
            return redirect(base_url(), 'refresh');
        }
    }

    public function editProfile($user_id) {
        $query = $this->User_Model->getUser($user_id);
        $data['user'] = $query->row();
        $this->load->view('Users/editProfile', $data);
    }

    public function updateProfile($user_id) {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('dob', 'DOB', 'required|trim');
            $this->form_validation->set_rules('gender', 'Gender', 'required|trim');
            $this->form_validation->set_rules('marital_status', 'Marital Status', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                echo "if called : ";
                print_r($_POST);
                exit;
                $this->session->set_flashdata('err', validation_errors());
                return redirect(base_url('editProfile/' . $user_id), 'refresh');
            } else {
                $data = [
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'mobile' => $this->input->post('mobile'),
                    'gender' => $this->input->post('gender'),
                    'dob' => $this->input->post('dob'),
                    'marital_status' => $this->input->post('marital_status'),
                    'father_name' => $this->input->post('father_name'),
                ];
                $this->User_Model->updateUser($user_id, $data);

                $this->session->set_flashdata('msg', 'Updated Successfully.');
                return redirect(base_url('myProfile'), 'refresh');
            }
        } else {
            $this->session->set_flashdata('err', 'Updated Successfully.');
            return redirect(base_url('editProfile/' . $user_id), 'refresh');
        }
    }

    public function forgetPassword() {
        unset($_SESSION['msg']);
        $this->load->view('UMS/forget_password');
    }

    public function verifyUser() {

        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('err', validation_errors());
            return redirect(base_url('forgetPassword', 'refresh'));
        } else {

            $input_email = strtoupper($_POST['email']);

            $conditions = [
                'U.email' => $input_email,
                'U.user_status_id' => 1,
                'U.user_active' => 1,
                'U.user_deleted' => 0,
                'UR.user_role_active' => 1,
                'UR.user_role_deleted' => 0,
            ];

            $select = 'U.user_id, U.mobile,U.user_last_login_datetime, U.company_id, U.product_id, U.name, U.email, RM.role_type_id as role_id, RM.role_type_name as role, RM.role_type_labels as labels';

            $result = $this->db->select($select)
                ->where($conditions)
                ->from('users U')
                ->join('user_roles UR', 'UR.user_role_user_id=U.user_id', 'inner')
                ->join('master_role_type RM', 'UR.user_role_type_id=RM.role_type_id', 'inner')
                ->get();

            if ($result->num_rows() > 0) {

                $sql = $result->row();
                $user_id = $sql->user_id;
                $name = $sql->name;
                $email = $sql->email;
                $otp = mt_rand(100000, 999999);
                $this->db->set('otp', $otp)->where('user_id', $user_id)->update('users');

                $subject = BRAND_NAME . " FINTECH FORGOT PASSWORD | $name | Reset Datetime : " . date("d-m-Y H:i:s");

                $message = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
                        <html xmlns='http://www.w3.org/1999/xhtml'>
                            <head>
                            <link href='https://allfont.net/allfont.css?fonts=courier' rel='stylesheet' type='text/css' />
                                <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                                <title>' . $subject . '</title>
                            </head><table width='650' border='1' cellspacing='0' cellpadding='0' style='border:1px solid #000'>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top' colspan='2'><strong>&nbsp;Dear " . $name . ",</strong></td>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;URL</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . base_url() . "</strong></td>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Login User</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;$email</strong></td>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;One Time Password (OTP)</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $otp . "</strong></td>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;User IP</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . ip . "</strong></td>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Platform</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->platform() . "</strong></td>
                                    </tr>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Browser & Version</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->browser() . ' ' . $this->agent->version() . "</strong></td>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Agent String</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->agent_string() . "</strong></td>
                                    </tr>
                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Password Change Activity</strong></td>
                                      <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s'))) . "</strong></td>
                                    </tr>

                                    <tr bgcolor='#ededed'>
                                      <td height='20' style='color:#000;' valign='top' colspan='3'><strong>&nbsp;If you have any query regarding lms.<br>Contact us on email - " . TECH_EMAIL . " (IT-Support)</strong></td>
                                    </tr>
                                  </table></html>";

                require_once(COMPONENT_PATH . 'includes/functions.inc.php');
                common_send_email($email, $subject, $message);

                $this->session->set_flashdata('msg', "OTP Sent To Registered mail Please Verify.");

                $data['user_id'] = $user_id;
                $this->load->view('UMS/otp_verify', $data);
            } else {
                $this->session->set_flashdata('err', 'Plaese enter valid email address.');
                return redirect(base_url('forgetPassword'));
            }
        }
    }

    public function verifyOtp() {

        $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');
        $this->form_validation->set_rules('otp', 'OTP', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim');

        $user_id = $this->input->post('user_id');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('err', validation_errors());
            $data['user_id'] = $user_id;
            $this->load->view('UMS/otp_verify', $data);
        } else {
            $data['user_id'] = $user_id;
            $otp = $this->input->post('otp');
            $password = $this->input->post('password');
            $confirm_password = $this->input->post('confirm_password');

            if ($password == $confirm_password) {

                $result = $this->db->select('user_id, email, otp')->from('users')->where('user_id', $user_id)->get()->row_array();

                if (!empty($result)) {


                    if ($otp == $result['otp']) {
                        $ency_password = md5($password);
                        $id = $result['user_id'];
                        $data = array(
                            'password' => $ency_password,
                            'updated_on' => date("Y-m-d H:i:s"),
                            'user_last_password_reset_datetime' => date("Y-m-d H:i:s"),
                            'user_logins_failed_count' => 0
                        );

                        $this->db->where('user_id', $id)->update('users', $data);

                        $this->session->set_flashdata('msg', 'Password reset successfully...!');
                        $this->login();
                    } else {
                        $this->session->set_flashdata('err', 'OTP Not Match...!');
                        $this->load->view('UMS/otp_verify', $data);
                    }
                } else {
                    $this->session->set_flashdata('err', 'Invalid Access.');
                }
            } else {
                $this->session->set_flashdata('err', 'New password and confirm password does not matched.');
                $this->load->view('UMS/otp_verify', $data);
            }
        }
    }

    public function changePassword() {
        unset($_SESSION['err']);
        unset($_SESSION['msg']);
        $user_id = $_SESSION['isUserSession']['user_id'];
        if (!empty($user_id)) {
            $data['user_id'] = $_SESSION['isUserSession']['user_id'];
            $this->load->view('UMS/change_password', $data);
        }
    }

    public function updatePassword() {
        unset($_SESSION['err']);
        unset($_SESSION['msg']);

        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('UMS/change_password');
        } else {
            $current_password = $this->input->post('current_password');
            $password = $this->input->post('password');
            $confirm_password = $this->input->post('confirm_password');
            $encrypt = md5($password);
            $user_id = $this->input->post('user_id');

            if ($password == $confirm_password) {

                $user_id = $_SESSION['isUserSession']['user_id'];
                $result = $this->db->select('user_id, password')->from('users')->where('user_id', $user_id)->get()->row_array();
                if ($result['user_id'] == $user_id && $result['password'] == md5($current_password)) {
                    $this->session->set_flashdata('msg', 'Password Changed Succesfully..!');
                    $this->db->set('password', $encrypt)->where('user_id', $user_id)->update('users');
                    $this->logout();
                } else {
                    $this->session->set_flashdata('err', 'Current Password miss match..!');
                    $this->load->view('UMS/change_password');
                }
            } else {
                $this->session->set_flashdata('err', 'Password miss match..!');
                return redirect(base_url('updatePassword'), 'refresh');
            }
        }
    }

    public function leadAllocation() {

        if (!empty($_SESSION['isUserSession']['user_id'])) {

            $this->form_validation->set_rules('user_status', 'User Status', 'required|trim|numeric');
            $this->form_validation->set_rules('user_type', 'User Type', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $error = array('errormessage' => 'Please choose options.', 'status' => 0);
                echo json_encode($error);
            } else {
                if (!in_array(agent, ["CR1", "CR2"])) {
                    $error = array('errormessage' => 'Only Screener/Credit can save the allocation.', 'status' => 0);
                    echo json_encode($error);
                } else {

                    $user_id = $_SESSION['isUserSession']['user_id'];
                    $status = $_POST['user_status'];
                    $user_type = $_POST['user_type'];

                    $insertAllocation = array(
                        'ula_user_id' => $user_id,
                        'ula_user_status' => $status,
                        'ula_user_case_type' => $user_type,
                        'ula_created_on' => date('Y-m-d H:i:s')
                    );

                    $this->db->insert('user_lead_allocation_log', $insertAllocation);
                    $insertAllocation['status'] = 1;
                    echo json_encode($insertAllocation);
                }
            }
        } else {
            $error = array('errormessage' => 'Session Expired, Please login again', 'status' => 0);
            echo json_encode($error);
        }
    }

    public function targetAllocation() {

        if (!empty($_SESSION['isUserSession']['user_id'])) {
            $this->form_validation->set_rules('no_of_cases', 'No of Cases', 'required|trim|numeric');
            $this->form_validation->set_rules('target_amount', 'Amount', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $error = array('errormessage' => 'Invalid Input.', 'status' => 0);
                echo json_encode($error);
            } else {

                if (empty(agent)) {
                    $error = array('errormessage' => 'Session Expired, please re-login.', 'status' => 0);
                    echo json_encode($error);
                } else if (!in_array(agent, ["CR1", "CO1"])) {
                    $error = array('errormessage' => 'Only Screener and Collection can save the allocation.', 'status' => 0);
                    echo json_encode($error);
                } else {

                    $user_id = $_SESSION['isUserSession']['user_id'];
                    $target_cases = $_POST['no_of_cases'];
                    $target_amount = $_POST['target_amount'];
                    $type_id = 0;

                    if ($target_cases <= 0) {
                        $error = array('errormessage' => 'No of Cases is greater than 0.', 'status' => 0);
                        echo json_encode($error);
                    } else if ($target_amount <= 0) {
                        $error = array('errormessage' => 'Amount is greater than 0.', 'status' => 0);
                        echo json_encode($error);
                    } else {

                        $insertTarget = array(
                            'uta_user_id' => $user_id,
                            'uta_user_target_amount' => $target_amount,
                            'uta_created_on' => date('Y-m-d H:i:s')
                        );

                        if (agent == "CR1") {
                            $insertTarget['uta_type_id'] = 1;
                            $insertTarget['uta_user_target_cases'] = $target_cases;
                        } else if (agent == "CO1") {
                            $insertTarget['uta_type_id'] = 2;
                            $insertTarget['uta_user_target_followups'] = $target_cases;
                        }

                        $this->db->insert('user_target_allocation_log', $insertTarget);

                        $insertTarget['status'] = 1;
                        echo json_encode($insertTarget);
                    }
                }
            }
        } else {
            $error = array('errormessage' => 'Session Expired, Please login again', 'status' => 0);
            echo json_encode($error);
        }
    }

    public function expirePassword() 
    {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $this->session->set_flashdata('err', 'Session expired. Please login again.');
            return redirect(base_url('login'), 'refresh');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|trim');
            $this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'required|matches[password]');
            //$this->form_validation->set_rules('user_id', 'User ID', 'required');

            if ($this->form_validation->run() === false) {
                $this->session->set_flashdata('err', validation_errors('<div>', '</div>'));
                return $this->load->view('UMS/expired_password');
            }

            $password = $this->input->post('password', true); // XSS Filtering
            $updateData = [
                'user_last_password_reset_datetime' => date('Y-m-d H:i:s'),
                'password' => md5($password)
            ];

            $last_password = $this->db->select('password')
                ->where('user_id', $_SESSION['isUserSession']['user_id'])
                ->get('users')->row_array();

            if ($last_password['password'] == md5($password)) {
                $this->session->set_flashdata('err', 'New password cannot be same as the old password.');
                return redirect(base_url('password-expired'), 'refresh');
            }

            $this->db->where('user_id', $_SESSION['isUserSession']['user_id'])->update('users', $updateData);

            session_destroy();
            $this->session->set_flashdata('msg', 'Password updated successfully. Please log in again.');
            return redirect(base_url('login'), 'refresh');
        }

        $this->load->view('UMS/expired_password');
    }
}
