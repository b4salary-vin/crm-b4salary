
<?php

use ParagonIE\ConstantTime\Base64;
use Safe\Exceptions\ExecException;

function bureau_api_call($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(2, "BUREAU API started | $lead_id");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "GET_BUREAU_JSON_SCORE" => 1,
        // "GET_CIBIL_SCORE_PDF" => 2
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = crif_bureau_api_call($lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    return $responseArray;
}

function crif_bureau_api_call($lead_id = 0, $request_array = array()) {

    $whitelist = array('FTSPR8837J', 'ITJPS6448N', 'FNGPB2698K', 'DHFPK0445A');
    ini_set('max_execution_time', 3600);
    ini_set("memory_limit", "1024M");

    common_log_writer(2, "crif_inquiry_agent_request started | $lead_id");

    require_once(COMP_PATH . 'includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "", "cibil_score" => "");

    //INIT VAR(s)

    $envSet = COMP_ENVIRONMENT;
    $customer_id = "";
    $lead_status_id = "";
    $cibil_score = "";
    $cibil_html = "";
    $s3_flag = 0;
    $number_of_account = 0;
    $number_of_active_account = 0;
    $number_of_overdue_account = 0;
    $current_balance = 0;
    $sanctioned_amount = 0;
    $report_id = 0;
    $request_json2 = "";
    $response_json2 = "";

    $apiStatusId = 0;
    $apiRequestXml = "";
    $apiResponseXml = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SUREPASS_CRIF_CALL";
    $sub_type = "CRIF_FETCH_REPORT";
    
    // traceObject($sub_type); exit;

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";

    $leadModelObj = new LeadModel();

    $REQ_VOL_TYP = "C01";
    $REQ_ACTN_TYP = "SUBMIT";
    $AUTH_FLG = "Y";
    $AUTH_TITLE = "USER";
    $RES_FRMT = "XML/HTML";
    $MEMBER_PRE_OVERRIDE = "N";
    $RES_FRMT_EMBD = "Y";
    $LOS_NAME = "abc";
    $LOS_VENDER = "cde";
    $LOS_VERSION = "1.0";
    $MFI_INDV = "true";
    $MFI_SCORE = "false";
    $MFI_GROUP = "true";

    $CONSUMER_INDV = "true";
    $CONSUMER_SCORE = "true";
    $IOI = "true";

    $INQUIRY_UNIQUE_REF_NO = "";
    $CREDT_RPT_ID = "345";
    $CREDT_REQ_TYP = "INDV";
    $CREDT_RPT_TRN_ID = "";
    $CREDT_INQ_PURPS_TYP = "ACCT-ORIG";
    $CREDT_INQ_PURPS_TYP_DESC = "A12";
    $CREDIT_INQUIRY_STAGE = "PRE-DISB";
    $KENDRA_ID = "1234";
    $BRANCH_ID = "3008";

    $APPLICATION_DATETIME = "";
    $LOAN_AMOUNT = "";
    $TEST_FLG = "";
    $NAME1 = $NAME2 = $NAME3 = $NAME4 = "";
    $DOB_DATE = $AGE_AS_ON = $AGE = "";

    $ID_TYPE_PAN = "N";
    $ID_TYPE_DL = "N";
    $ID_TYPE_PP = "N";
    $PAN_VALUE = "";
    $PP_VALUE = "";
    $DL_VALUE = "";

    $ADDR_TYPE_PERMANENT = "N";
    $PER_ADDR_VALUE = "";
    $PER_ADDR_CITY = "";
    $PER_ADDR_STATE = "";
    $PER_ADDR_PINCODE = "";

    $ADDR_TYPE_RESIDENCE = "N";
    $RES_ADDR_CITY = "";
    $RES_ADDR_STATE = "";
    $RES_ADDR_PINCODE = "";
    $RES_ADDR_VALUE = "";

    //RELATIONS
    $REL_TYPE_FATHER = "N";
    $REL_TYPE_MOTHER = "N";
    $REL_TYPE_SPOUSE = "N";

    $FATHER_VALUE = "";
    $MOTHER_VALUE = "";
    $SPOUSE_VALUE = "";

    //PHONES
    $PHONE_TYPE_MOBILE = "N";
    $PHONE_TYPE_COMPANY = "N";
    $MOBILE_VALUE = "";

    $EMAIL = "";
    $GENDER = "";

    try {


        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $apiToken = $apiConfig["ApiToken"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];

        $customer_id = !empty($app_data['customer_id']) ? $app_data['customer_id'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $NAME1 = $app_data['first_name'];

        if (!empty($app_data['middle_name'])) {
            $NAME2 = $app_data["middle_name"];
        }
        $NAME3 = !empty($app_data['sur_name']) ? $app_data['sur_name'] : "";

        if (!empty($app_data['pancard'])) {
            $ID_TYPE_PAN = "Y";
            $PAN_VALUE = $app_data['pancard'];
        }

        if (in_array($PAN_VALUE, $whitelist)) {
            throw new Exception("Don't use this pancard. This is internal PAN Card");
        }

        if (!empty($app_data['mobile'])) {
            $PHONE_TYPE_MOBILE = "Y";
            $MOBILE_VALUE = $app_data['mobile'];
        }

        $GENDER = ""; //G03
        if (!empty($app_data['gender'])) {
            $gender = strtoupper($app_data['gender']);

            if ($gender == "MALE") {
                $GENDER = "G01";
            } else if ($gender == "FEMALE") {
                $GENDER = "G02";
            }
        }

        if (!empty($app_data['father_name'])) {
            $father_type = "K01";
            $father_name = $app_data['father_name'];
        }

        $EMAIL = $app_data['email'];

        //ADDRESSES
        //RESIDENCE ADDR
        if (!empty($app_data['current_house']) || in_array($lead_status_id, array(2, 3))) {
            $ADDR_TYPE_RESIDENCE = "Y";
            $RES_ADDR_VALUE = $app_data['current_house'];

            if (!empty($app_data['current_locality'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_locality'];
            }

            if (!empty($app_data['current_landmark'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_landmark'];
            }

            $res_city_id = $app_data['city_id'];

            $res_city_res = $leadModelObj->getCityDetails($res_city_id);

            if ($res_city_res['status'] == 1) {
                $res_city_details = $res_city_res['city_data'];
                $RES_ADDR_CITY = $res_city_details["m_city_name"];
            }

            $res_state_id = $app_data["state_id"];

            $res_state_res = $leadModelObj->getStateDetails($res_state_id);

            if ($res_state_res['status'] == 1) {
                $res_state_details = $res_state_res['state_data'];
                //$RES_ADDR_STATE = $res_state_details["m_state_name"];
                $RES_ADDR_STATE = $res_state_details["m_state_code"];
            }

            $RES_ADDR_PINCODE = $app_data['cr_residence_pincode'];
            if (in_array($lead_status_id, array(2, 3))) {
                $RES_ADDR_VALUE = $RES_ADDR_CITY . " " . $res_state_details["m_state_name"];
            }

            $RES_ADDR_VALUE = trim($RES_ADDR_VALUE);
        }

        //PERMANENT ADDR
        if (!empty($app_data['aa_current_house'])) {
            $ADDR_TYPE_PERMANENT = "Y";

            $PER_ADDR_VALUE = $app_data['aa_current_house'];

            if (!empty($app_data['aa_current_locality'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_locality'];
            }
            if (!empty($app_data['aa_current_landmark'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_landmark'];
            }

            $perm_city_id = $app_data['aa_current_city_id'];

            $per_city_res = $leadModelObj->getCityDetails($perm_city_id);
            if ($per_city_res['status'] == 1) {
                $per_city_details = $per_city_res['city_data'];
                $PER_ADDR_CITY = $per_city_details["m_city_name"];
            }

            $perm_state_id = $app_data["aa_current_state_id"];

            $per_state_res = $leadModelObj->getStateDetails($perm_state_id);

            if ($per_state_res['status'] == 1) {
                $per_state_details = $per_state_res['state_data'];
                //                $PER_ADDR_STATE = $per_state_details["m_state_name"];
                $PER_ADDR_STATE = $per_state_details["m_state_code"];
            }

            $PER_ADDR_PINCODE = $app_data['aa_cr_residence_pincode'];
        }


        if (empty($NAME1) || empty($PAN_VALUE) || empty($MOBILE_VALUE)) {
            throw new Exception("Missing mandatory fields to call bureau api.");
        }

        $apiRequestJson = '{
            "first_name": "'. $NAME1 .'",
            "last_name": "'. $NAME3 .'",
            "mobile": "'. $MOBILE_VALUE .'",
            "pan": "'. $PAN_VALUE .'",
            "consent": "Y"
        }';


        // $apiRequestJson = '{
        //     "first_name": "Vikrant",
        //     "last_name": "Bargoti",
        //     "mobile": "9917778780",
        //     "pan": "DLYPB7458D",
        //     "consent": "Y"
        // }';

        // $apiRequestJson = '{
        //     "first_name": "PANKAJ",
        //     "last_name": "BHARTI",
        //     "mobile": "7733866660",
        //     "pan": "DETPB8773M",
        //     "consent": "Y"
        // }';


        if ($debug) {
            echo "<br/><br/>=======Request XML=========<br/><br/>";
            echo $apiRequestJson;
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $cibil_details = $leadModelObj->getCibilData($lead_id);

        if ($cibil_details['status'] == 1) {
            $apiResponseJson = $cibil_details['cibil_data'][0]['response'];
        }

        $curl = curl_init();

        $apiRequestHeader = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiToken .''
        );
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => $apiRequestHeader,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));

        $apiResponseJson = curl_exec($curl);

        curl_close($curl);

        if ($debug) {
            echo "<br/><br/><br/><br/><br/><br/>=======Response XML=========<br/><br/>";
            echo $apiResponseJson;
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        // if (!$hardcode_response && curl_errno($curl)) { // CURL Error
        //     $curlError = curl_error($curl);
        //     curl_close($curl);
        //     throw new RuntimeException("API Error : " . $curlError);
        // } else {
        //     if (!$hardcode_response) {
        //         curl_close($curl);
        //     }
        // }

        if (!empty($apiResponseJson)) {


            $tempApiResponseJson = json_decode($apiResponseJson, true);
            
            if (!empty($tempApiResponseJson['success']) && $tempApiResponseJson['success'] == 1) {
                $apiStatusId = 1;
                $cibil_score = $tempApiResponseJson['data']['credit_score'];
                $account_summary = $tempApiResponseJson['data']['credit_report']['ACCOUNTS-SUMMARY'];
                $number_of_account = $account_summary['PRIMARY-ACCOUNTS-SUMMARY']['PRIMARY-NUMBER-OF-ACCOUNTS'];
                $number_of_active_account = $account_summary['PRIMARY-ACCOUNTS-SUMMARY']['PRIMARY-ACTIVE-NUMBER-OF-ACCOUNTS'];
                $number_of_overdue_account = $account_summary['PRIMARY-ACCOUNTS-SUMMARY']['PRIMARY-OVERDUE-NUMBER-OF-ACCOUNTS'];
                $current_balance = $account_summary['PRIMARY-ACCOUNTS-SUMMARY']['PRIMARY-CURRENT-BALANCE'];
                $sanctioned_amount = $account_summary['PRIMARY-ACCOUNTS-SUMMARY']['PRIMARY-SANCTIONED-AMOUNT'];
                $report_id = $tempApiResponseJson['data']['credit_report']['HEADER']['REPORT-ID'];

                $report_pdf_response = crif_bureau_report_pdf_api_call($lead_id);
                
                if (!empty($report_pdf_response['status'])) {
                    $cibil_html = $report_pdf_response['cibil_html'];
                    $request_json2 = $report_pdf_response['request_json'];
                    $response_json2 = $report_pdf_response['response_json'];
                    $s3_flag = 1;
                }

            } else {
                $tmp_error_msg = !empty($tempApiResponseJson['message']) ? "API Error : No Data Found from BUREAU => " . $tempApiResponseJson['message'] : "NO SCORE FOUND IN BUREAU. PLEASE CHECK THE REPORT.";
                throw new ErrorException($tmp_error_msg);

            }

        } else {
            throw new ErrorException("Empty response from CRIF API");
        }
    } catch (ErrorException $le) {
        $apiStatusId = 2;
        $errorMessage = $le->getMessage();
    } catch (RuntimeException $re) {
        $apiStatusId = 3;
        $errorMessage = $re->getMessage();
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['cibil_score'] = $cibil_score;
    $response_array['errors'] = $errorMessage;

    if (!empty($lead_id)) {
        if ($apiStatusId == 1) {
            $lead_remarks = "CRIF API CALL(Success) | Score : " . $cibil_score;
        } else {
            $lead_remarks = "CRIF API CALL(Failed) | Error : " . $errorMessage;
        }
        $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
    }

    if ($apiStatusId == 1) {

        $cibil_data = [
            'cibil_bureau_type' => 2, //CRIF
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'cibil_pancard' => $PAN_VALUE,
            // 'memberCode' => $apiMemberId,
            'cibilScore' => $cibil_score,
            'cibil_file' => $cibil_html,
            'applicationId' => !empty($report_id) ? $report_id : "",
            'cibil_created_by' => !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0,
            'created_at' => date("Y-m-d H:i:s"),
            'totalAccount' => $number_of_account,
            'totalBalance' => $current_balance,
            'highCrSanAmt' => $sanctioned_amount,
            'overDueAccount' => $number_of_overdue_account,
            'overDueAmount' => $number_of_overdue_account
        ];

        $leadModelObj->insertTable('tbl_cibil', $cibil_data);
    }

    if ($apiStatusId == 1 || $apiStatusId == 2) {
        $cibil_log = [
            'cibil_bureau_type' => 2, //CRIF
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'customer_name' => implode(" ", array($NAME1, $NAME2, $NAME3)),
            'customer_mobile' => $MOBILE_VALUE,
            'pancard' => $PAN_VALUE,
            'loan_amount' => $LOAN_AMOUNT,
            'dob' => $DOB_DATE,
            'gender' => $gender,
            'customer_email' => $EMAIL,
            'city' => $RES_ADDR_CITY,
            'state_id' => $res_state_id,
            'pincode' => $RES_ADDR_PINCODE,
            'api1_request' => $apiRequestJson,
            'api1_response' => $apiResponseJson,
            'api2_request' => $request_json2,
            'api2_response' => $response_json2,
            'cibilScore' => $cibil_score,
            'applicationId' => !empty($report_id) ? $report_id : "",
            'cibil_file' => $cibil_html,
            's3_flag' => $s3_flag,
        ];
        
        $leadModelObj->insertTable('tbl_cibil_log', $cibil_log);
    }

    if ($apiStatusId == 1 && isset($cibil_score)) {
        $leadModelObj->updateLeadTable($lead_id, ['check_cibil_status' => 1, 'cibil' => $cibil_score]);
    }

    return $response_array;
}

function crif_bureau_report_pdf_api_call($lead_id = 0, $request_array = array()) {
    ini_set('max_execution_time', 3600);
    ini_set("memory_limit", "1024M");

    common_log_writer(2, "crif_inquiry_json_agent_request started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "", "cibil_score" => "");

    //INIT VAR(s)

    $envSet = COMP_ENVIRONMENT;
    $customer_id = "";
    $lead_status_id = "";
    $cibil_score = "";
    $cibil_html = "";

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SUREPASS_CRIF_CALL";
    $sub_type = "CRIF_FETCH_REPORT_PDF";

    $hardcode_response = false;
    //if($envSet == 'production') {
    //   $hardcode_response = true;
    //}

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";

    $leadModelObj = new LeadModel();

    try {

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }
        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $apiToken = $apiConfig["ApiToken"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];


        $customer_id = !empty($app_data['customer_id']) ? $app_data['customer_id'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $INQUIRY_UNIQUE_REF_NO = date("YmdHis") . $apiMemberId . rand(100, 999);

        $CREDT_RPT_TRN_ID = "BLUAT" . date("YmdHis") . rand(100, 999);

        if ($envSet == 'production') {
            $CREDT_RPT_TRN_ID = "BLPRD" . date("YmdHis") . rand(100, 999);
        }

        $APPLICATION_DATETIME = date('d-m-Y H:i:s');
        $TEST_FLG = ($envSet == 'production') ? 'HMLIVE' : 'HMTEST';
        $LOAN_AMOUNT = round($app_data['loan_amount'], 0);

        $NAME1 = $app_data['first_name'];

        if (!empty($app_data['middle_name'])) {
            $NAME2 = $app_data["middle_name"];
        }
        $NAME3 = !empty($app_data['sur_name']) ? $app_data['sur_name'] : "";


        $DOB_DATE = date('d-m-Y', strtotime($app_data['dob']));
        $AGE_AS_ON = date('d-m-Y');
        $AGE = (strtotime($AGE_AS_ON) - strtotime($DOB_DATE));
        $AGE = intval(($AGE / (60 * 60 * 24 * 365.25)));

        //IDS

        if (!empty($app_data['pancard'])) {
            $ID_TYPE_PAN = "ID07";
            $PAN_VALUE = $app_data['pancard'];
        }

        if (!empty($app_data['mobile'])) {
            $PHONE_TYPE_MOBILE = "P04";
            $MOBILE_VALUE = $app_data['mobile'];
        }

        if (!empty($app_data['father_name'])) {
            $father_type = "K01";
            $father_name = $app_data['father_name'];
        }

        $EMAIL = $app_data['email'];

        $GENDER = "";
        if (!empty($app_data['gender'])) {
            $gender = strtoupper($app_data['gender']);

            if ($gender == "MALE") {
                $GENDER = "G01";
            } else if ($gender == "FEMALE") {
                $GENDER = "G02";
            }
        }

        //ADDRESSES
        //RESIDENCE ADDR
        if (!empty($app_data['current_house']) || in_array($lead_status_id, array(2, 3))) {
            $ADDR_TYPE_RESIDENCE = "Y";
            $RES_ADDR_VALUE = $app_data['current_house'];

            if (!empty($app_data['current_locality'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_locality'];
            }

            if (!empty($app_data['current_landmark'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_landmark'];
            }

            $res_city_id = $app_data['city_id'];

            $res_city_res = $leadModelObj->getCityDetails($res_city_id);

            if ($res_city_res['status'] == 1) {
                $res_city_details = $res_city_res['city_data'];
                $RES_ADDR_CITY = $res_city_details["m_city_name"];
            }

            $res_state_id = $app_data["state_id"];

            $res_state_res = $leadModelObj->getStateDetails($res_state_id);

            if ($res_state_res['status'] == 1) {
                $res_state_details = $res_state_res['state_data'];
                //$RES_ADDR_STATE = $res_state_details["m_state_name"];
                $RES_ADDR_STATE = $res_state_details["m_state_code"];
            }

            $RES_ADDR_PINCODE = $app_data['cr_residence_pincode'];
            if (in_array($lead_status_id, array(2, 3))) {
                $RES_ADDR_VALUE = $RES_ADDR_CITY . " " . $res_state_details["m_state_name"];
            }

            $RES_ADDR_VALUE = trim($RES_ADDR_VALUE);
        }

        //PERMANENT ADDR
        if (!empty($app_data['aa_current_house'])) {
            $ADDR_TYPE_PERMANENT = "Y";

            $PER_ADDR_VALUE = $app_data['aa_current_house'];

            if (!empty($app_data['aa_current_locality'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_locality'];
            }
            if (!empty($app_data['aa_current_landmark'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_landmark'];
            }

            $perm_city_id = $app_data['aa_current_city_id'];

            $per_city_res = $leadModelObj->getCityDetails($perm_city_id);
            if ($per_city_res['status'] == 1) {
                $per_city_details = $per_city_res['city_data'];
                $PER_ADDR_CITY = $per_city_details["m_city_name"];
            }

            $perm_state_id = $app_data["aa_current_state_id"];

            $per_state_res = $leadModelObj->getStateDetails($perm_state_id);

            if ($per_state_res['status'] == 1) {
                $per_state_details = $per_state_res['state_data'];
                //                $PER_ADDR_STATE = $per_state_details["m_state_name"];
                $PER_ADDR_STATE = $per_state_details["m_state_code"];
            }

            $PER_ADDR_PINCODE = $app_data['aa_cr_residence_pincode'];
        }

        if (empty($NAME1) || empty($PAN_VALUE) || empty($MOBILE_VALUE)) {
            throw new Exception("Missing mandatory fields to call bureau api.");
        }

        $apiRequestJson = '{
            "first_name": "'. $NAME1 .'",
            "last_name": "'. $NAME3 .'",
            "mobile": "'. $MOBILE_VALUE .'",
            "pan": "'. $PAN_VALUE .'",
            "consent": "Y",
            "raw": false
        }';
        
        // $apiRequestJson = '{
        //     "first_name": "Vikrant",
        //     "last_name": "Bargoti",
        //     "mobile": "9917778780",
        //     "pan": "DLYPB7458D",
        //     "consent": "Y",
        //     "raw": false
        // }';
        
        // $apiRequestJson = '{
        //     "first_name": "PANKAJ",
        //     "last_name": "BHARTI",
        //     "mobile": "7733866660",
        //     "pan": "DETPB8773M",
        //     "consent": "Y"
        // }';

        if ($debug) {
            echo "<br/><br/>=======Request XML=========<br/><br/>";
            echo $apiRequestJson;
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $cibil_details = $leadModelObj->getCibilData($lead_id);

        if ($cibil_details['status'] == 1) {
            $apiResponseJson = $cibil_details['cibil_data'][0]['response'];
        }

        $curl = curl_init();

        $apiRequestHeader = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiToken .''
        );
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => $apiRequestHeader,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));

        $apiResponseJson = curl_exec($curl);

        curl_close($curl);

        if ($debug) {
            echo "<br/><br/><br/><br/><br/><br/>=======Response XML=========<br/><br/>";
            echo $apiResponseJson;
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!empty($apiResponseJson)) {
            $tempApiResponseJson = json_decode($apiResponseJson, true);

            $report_link = $tempApiResponseJson['data']['credit_report_link'];
            
            require_once(COMP_PATH . '/CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $upload_file = array();
            $upload_file['flag'] = 1;
            $upload_file['file'] = base64_encode(file_get_contents($report_link));
            $upload_file['new_file_name'] = 'cibil';
            $upload_file['ext'] = 'pdf';

            $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

            if ($upload_status['status'] == 1) {
                $cibil_html = $upload_status['file_name'];
            }
            $apiStatusId = 1;
        } else {
            throw new ErrorException("Empty response from CRIF API");
        }
    } catch (ErrorException $le) {
        $apiStatusId = 2;
        $errorMessage = $le->getMessage();
    } catch (RuntimeException $re) {
        $apiStatusId = 3;
        $errorMessage = $re->getMessage();
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    // $response_array['cibil_score'] = $cibil_score;
    $response_array['cibil_html'] = $cibil_html;
    $response_array['errors'] = $errorMessage;
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}

function extractFinalResponse($apiResponseJson) {
    $extractedInfo = explode('}<!DOCTYPE', $apiResponseJson);
    $extractedInfo[1] = "<!DOCTYPE" . $extractedInfo[1];
    return ["jsonResponse" => $extractedInfo[0] . "}", "htmlResponse" => $extractedInfo[1]];
}

function urlRequestForXML() {
    //if (isset($_SERVER['HTTP_REQUESTXML']) && !empty($_SERVER['HTTP_REQUESTXML'])) {
    $curl = curl_init();
    file_put_contents('crif_request.txt', "\n\n Time: " . date("F j, Y, g:i a") . " - " . $_SERVER['HTTP_REQUESTXML'], FILE_APPEND);
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://hub.crifhighmark.com/Inquiry/doGet.service/requestResponseSync',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'mbrid: NBF0005465',
            'productType: INDV',
            'productVersion: 1.0',
            'Content-Type: application/xml',
            'UserId: kasar_cpu_prd@kasarcredit.com',
            'password: E2ACF08723F5EBBCC6AE42383D1BEA844EEFA571',
            'reqVolType: INDV',
            'requestXml: ' . $_SERVER['HTTP_REQUESTXML']
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    //file_put_contents('crif_response.txt',"\n\n Time: ".date("F j, Y, g:i a"). " - ". $response, FILE_APPEND);
    //echo $response;
    if ($response === false) {
        $error_msg = curl_error($curl);
        $error_code = curl_errno($curl);
        echo "cURL Error: $error_msg (Error Code: $error_code)";
    } else {
        header('Content-Type: application/xml');
        echo $response;
    }
    //}
}

function cibil_api_call($lead_id = 0, $request_array = array()) {
    ini_set('max_execution_time', 7200);
    ini_set("memory_limit", "2024M");

    common_log_writer(2, "cibil_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $whitelist = array('FTSPR8837J', 'FNGPB2698K', 'DHFPK0445A');
    $response_array = array("status" => 0, "errors" => "", "cibil_score" => "");

    //INIT VAR(s)

    $envSet = COMP_ENVIRONMENT;
    $customer_id = "";
    $lead_status_id = "";
    $cibil_score = "";
    $cibil_html = "";

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "CIBIL_CALL";
    $sub_type = "REQUEST_JSON";

    $hardcode_response = false;
    //if($envSet == 'production') {
    //   $hardcode_response = true;
    //}

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";

    $leadModelObj = new LeadModel();

    try {

        $apiConfig = integration_config($type, $sub_type);
        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }
        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];

        $apiUserId = $apiConfig["UserName"];
        $apiPassword = $apiConfig["UserPassword"];
        $apiMemberId = $apiConfig["ApiMemberId"];
        $apiKey = $apiConfig["ApiKey"];
        $clientSecret = $apiConfig["ClientSecret"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];


        $customer_id = !empty($app_data['customer_id']) ? $app_data['customer_id'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $INQUIRY_UNIQUE_REF_NO = date("YmdHis") . $apiMemberId . rand(100, 999);

        $CREDT_RPT_TRN_ID = "BLUAT" . date("YmdHis") . rand(100, 999);

        if ($envSet == 'production') {
            $CREDT_RPT_TRN_ID = "BLPRD" . date("YmdHis") . rand(100, 999);
        }

        $APPLICATION_DATETIME = date('d-m-Y H:i:s');
        $TEST_FLG = ($envSet == 'production') ? 'HMLIVE' : 'HMTEST';
        $LOAN_AMOUNT = round($app_data['loan_amount'], 0);

        $NAME1 = $app_data['first_name'];

        if (!empty($app_data['middle_name'])) {
            $NAME2 = $app_data["middle_name"];
        }
        $NAME3 = !empty($app_data['sur_name']) ? $app_data['sur_name'] : "";


        $DOB_DATE = date('dmY', strtotime($app_data['dob']));
        $AGE_AS_ON = date('d-m-Y');
        $AGE = (strtotime($AGE_AS_ON) - strtotime($DOB_DATE));
        $AGE = intval(($AGE / (60 * 60 * 24 * 365.25)));

        //IDS

        if (!empty($app_data['pancard'])) {
            $ID_TYPE_PAN = "ID07";
            $PAN_VALUE = $app_data['pancard'];
        }

        if (in_array($PAN_VALUE, $whitelist)) {
            throw new Exception("Don't use this pancard. This is internal PAN Card");
        }

        if (!empty($app_data['mobile'])) {
            $PHONE_TYPE_MOBILE = "P04";
            $MOBILE_VALUE = $app_data['mobile'];
        }

        if (!empty($app_data['father_name'])) {
            $father_type = "K01";
            $father_name = $app_data['father_name'];
        }

        $EMAIL = $app_data['email'];

        $GENDER = "";
        if (!empty($app_data['gender'])) {
            $gender = strtoupper($app_data['gender']);

            if ($gender == "MALE") {
                $GENDER = "1";
            } else if ($gender == "FEMALE") {
                $GENDER = "2";
            }
        }

        //ADDRESSES
        //RESIDENCE ADDR
        if (!empty($app_data['current_house']) || in_array($lead_status_id, array(2, 3))) {
            $ADDR_TYPE_RESIDENCE = "Y";
            $RES_ADDR_VALUE = $app_data['current_house'];

            if (!empty($app_data['current_locality'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_locality'];
            }

            if (!empty($app_data['current_landmark'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_landmark'];
            }

            $res_city_id = $app_data['city_id'];

            $res_city_res = $leadModelObj->getCityDetails($res_city_id);

            if ($res_city_res['status'] == 1) {
                $res_city_details = $res_city_res['city_data'];
                $RES_ADDR_CITY = $res_city_details["m_city_name"];
            }

            $res_state_id = $app_data["state_id"];

            $res_state_res = $leadModelObj->getStateDetails($res_state_id);

            if ($res_state_res['status'] == 1) {
                $res_state_details = $res_state_res['state_data'];
                //$RES_ADDR_STATE = $res_state_details["m_state_name"];
                $RES_ADDR_STATE = $res_state_details["cibil_state_code"];
                if ($res_state_details["cibil_state_code"] < 10) {
                    $RES_ADDR_STATE = "0" . $RES_ADDR_STATE;
                }
            }

            $RES_ADDR_PINCODE = $app_data['cr_residence_pincode'];
            if (in_array($lead_status_id, array(2, 3))) {
                $RES_ADDR_VALUE = $RES_ADDR_CITY . " " . $res_state_details["m_state_name"];
            }

            $RES_ADDR_VALUE = trim($RES_ADDR_VALUE);
        }

        //PERMANENT ADDR
        if (!empty($app_data['aa_current_house'])) {
            $ADDR_TYPE_PERMANENT = "Y";

            $PER_ADDR_VALUE = $app_data['aa_current_house'];

            if (!empty($app_data['aa_current_locality'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_locality'];
            }
            if (!empty($app_data['aa_current_landmark'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_landmark'];
            }

            $perm_city_id = $app_data['aa_current_city_id'];

            $per_city_res = $leadModelObj->getCityDetails($perm_city_id);
            if ($per_city_res['status'] == 1) {
                $per_city_details = $per_city_res['city_data'];
                $PER_ADDR_CITY = $per_city_details["m_city_name"];
            }

            $perm_state_id = $app_data["aa_current_state_id"];

            $per_state_res = $leadModelObj->getStateDetails($perm_state_id);

            if ($per_state_res['status'] == 1) {
                $per_state_details = $per_state_res['state_data'];
                //                $PER_ADDR_STATE = $per_state_details["m_state_name"];
                $PER_ADDR_STATE = $per_state_details["cibil_state_code"];
            }

            $PER_ADDR_PINCODE = $app_data['aa_cr_residence_pincode'];
        }

        //echo $NAME1.'@'.$ID_TYPE_PAN.'@'.$PHONE_TYPE_MOBILE.'@'.$GENDER.'@'.$RES_ADDR_VALUE.'@'.$RES_ADDR_CITY.'@'.$RES_ADDR_STATE.'@'.$RES_ADDR_PINCODE;die;

        if (empty($NAME1) || empty($GENDER) || empty($RES_ADDR_VALUE) || empty($RES_ADDR_CITY) || empty($RES_ADDR_STATE) || empty($RES_ADDR_PINCODE)) {
            // throw new Exception("Missing mandatory fields to call bureau api.");
        }

        $apiRequestJson = [
            "serviceCode" => "CN1CAS0004",
            "monitoringDate" => date("dmY"),
            "consumerInputSubject" => [
                "tuefHeader" => [
                    "headerType" => "TUEF",
                    "version" => "12",
                    "memberRefNo" => $apiMemberId,
                    "gstStateCode" => "36",
                    "enquiryMemberUserId" => $apiUserId,
                    "enquiryPassword" => $apiPassword,
                    "enquiryPurpose" => "08",
                    "enquiryAmount" => "000015000",
                    "scoreType" => "17",
                    "outputFormat" => "03",
                    "responseSize" => "1",
                    "ioMedia" => "CC",
                    "authenticationMethod" => "L"
                ],
                "names" => [
                    [
                        "index" => "N01",
                        "firstName" => $NAME1,
                        "middleName" => $NAME2,
                        "lastName" => $NAME3,
                        "birthDate" => $DOB_DATE,
                        "gender" => $GENDER
                    ]
                ],
                "ids" => [
                    [
                        "index" => "I01",
                        "idNumber" => $PAN_VALUE,
                        "idType" => "01"
                    ]
                ],
                "telephones" => [
                    [
                        "index" => "T01",
                        "telephoneNumber" => $MOBILE_VALUE,
                        "telephoneType" => "01",
                        "enquiryEnriched" => "Y"
                    ]
                ],
                "addresses" => [
                    [
                        "index" => "A01",
                        "line1" => substr($app_data['current_house'], 0, 40),
                        "line2" => substr($app_data['current_locality'], 0, 40),
                        "line3" => substr($app_data['current_landmark'], 0, 40),
                        "line4" => "",
                        "line5" => "",
                        "stateCode" => $RES_ADDR_STATE,
                        "pinCode" => $RES_ADDR_PINCODE,
                        "addressCategory" => "04",
                        "residenceCode" => "01"
                    ]
                ],
                "enquiryAccounts" => [
                    [
                        "index" => "I01",
                        "accountNumber" => ""
                    ]
                ]
            ]
        ];

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo json_encode($apiRequestJson);
        }

        $apiHeaders = array();
        $apiHeaders[] = 'member-ref-id:' . $apiMemberId;
        $apiHeaders[] = 'apikey:' . $apiKey;
        $apiHeaders[] = 'cust-ref-id:17235683';
        $apiHeaders[] = 'client-secret:' . $clientSecret;
        $apiHeaders[] = 'Content-Type: application/json';

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            print_r($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        /*$cibil_details = $leadModelObj->getCibilData($lead_id);
        //echo '<pre>';print_r($cibil_details); die;
        if ($cibil_details['status'] == 1) {
            $apiResponseJson = $cibil_details['cibil_data'][0]['response'];
        }
        */

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($apiRequestJson),
            CURLOPT_HTTPHEADER => $apiHeaders,
            CURLOPT_SSLCERT => COMP_PATH . '/../SSLRequests/www.kasarcredit.com.p12',
            CURLOPT_SSLCERTTYPE => 'p12'
        ));

        $apiResponseJson = curl_exec($curl);

        curl_close($curl);

        if ($debug) {
            echo "<br/><br/><br/><br/><br/><br/>=======Response JSON=========<br/><br/>";
            echo $apiResponseJson;
            print_r($apiResponseJson);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!empty($apiResponseJson)) {
            $tempApiResponseJson = $apiResponseJson;
            $apiResponseData = json_decode($tempApiResponseJson, true);
            $ConsumerCreditData = $apiResponseData['consumerCreditData'][0];
            $REPORT_ID = $ConsumerCreditData['tuefHeader']['enquiryControlNumber'];
            $cibil_score = !empty($ConsumerCreditData['scores'][0]['score']) ? (int) $ConsumerCreditData['scores'][0]['score'] : 0;
            $cibil_html = getCibilFile($lead_id, json_encode($apiRequestJson), $apiResponseJson);

            $apiStatusId = 1;
        } else {
            throw new ErrorException("Empty response from CRIF API");
        }
    } catch (ErrorException $le) {
        $apiStatusId = 2;
        $errorMessage = $le->getMessage();
    } catch (RuntimeException $re) {
        $apiStatusId = 3;
        $errorMessage = $re->getMessage();
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['cibil_score'] = $cibil_score;
    $response_array['cibil_html'] = $cibil_html['cibil_html'];
    $response_array['errors'] = $errorMessage;
    $response_array['request_xml'] = $apiRequestJson;
    $response_array['response_json'] = $apiRequestJson;
    $response_array['response_html'] = $cibil_html;

    if (!empty($lead_id)) {
        if ($apiStatusId == 1) {
            $lead_remarks = "CRIF API CALL(Success) | Score : " . $cibil_score;
        } else {
            $lead_remarks = "CRIF API CALL(Failed) | Error : " . $errorMessage;
        }
        $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
    }

    if ($apiStatusId == 1) {
        $cibil_data = [
            'cibil_bureau_type' => 1, //CIBIL
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'cibil_pancard' => $PAN_VALUE,
            'memberCode' => $apiMemberId,
            'cibilScore' => $cibil_score,
            'cibil_file' => $cibil_html['cibil_html'],
            'applicationId' => $REPORT_ID['REPORT-ID'],
            'cibil_created_by' => !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0,
            'created_at' => date("Y-m-d H:i:s"),
            's3_flag' => 1
        ];

        $leadModelObj->insertTable('tbl_cibil', $cibil_data);
    }

    if ($apiStatusId == 1 || $apiStatusId == 2) {
        $cibil_log = [
            'cibil_bureau_type' => 1, //CIBIL
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'customer_name' => implode(" ", array($NAME1, $NAME2, $NAME3)),
            'customer_mobile' => $MOBILE_VALUE,
            'pancard' => $PAN_VALUE,
            'loan_amount' => $LOAN_AMOUNT,
            'dob' => $DOB_DATE,
            'gender' => $gender,
            'customer_email' => $EMAIL,
            'city' => $RES_ADDR_CITY,
            'state_id' => $res_state_id,
            'pincode' => $RES_ADDR_PINCODE,
            'api1_request' => $cibil_html['request_file_name'],
            'api1_response' => $cibil_html['response_file_name'],
            // 'api1_request' => addslashes(json_encode($apiRequestJson)),
            // 'api1_response' => addslashes($apiResponseJson),
            'memberCode' => $apiMemberId,
            'cibilScore' => $cibil_score,
            'cibil_file' =>  $cibil_html['cibil_html'],
            's3_flag' => 1
        ];
        $leadModelObj->insertTable('tbl_cibil_log', $cibil_log);
    }

    if ($apiStatusId == 1 && isset($cibil_score)) {
        $leadModelObj->updateLeadTable($lead_id, ['check_cibil_status' => 1, 'cibil' => $cibil_score]);
    }

    return $response_array;
}

function getCibilFile($lead_id, $cibilRequest, $cibilResponse) {
    ini_set('max_execution_time', 7200);
    ini_set("memory_limit", "2024M");

    common_log_writer(2, "cibil_report_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "", "cibil_score" => "");

    //INIT VAR(s)

    $envSet = COMP_ENVIRONMENT;
    $cibil_html = "";

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "CIBIL_CALL";
    $sub_type = "REQUEST_JSON";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";

    $s3_flag = 0;

    $leadModelObj = new LeadModel();

    try {

        $apiConfig = integration_config($type, $sub_type);
        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }
        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];

        $apiUserId = $apiConfig["UserName"];
        $apiPassword = $apiConfig["UserPassword"];
        $apiMemberId = $apiConfig["ApiMemberId"];
        $apiKey = $apiConfig["ApiKey"];
        $clientSecret = $apiConfig["ClientSecret"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        if (empty($lead_id) || empty($cibilRequest) || empty($cibilResponse)) {
            throw new Exception("Missing mandatory fields to fetch cibil report.");
        }

        $apiRequestJson = [
            "report" => [
                [
                    "SourceOperationId" => "SCL002P001",
                    "RawResponse" => $cibilResponse,
                    "RawRequest" => $cibilRequest,
                    "ResponseType" => "json"
                ]
            ]
        ];

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo json_encode($apiRequestJson);
        }

        $apiHeaders = array();
        $apiHeaders[] = 'member-ref-id:' . $apiMemberId;
        $apiHeaders[] = 'apikey:' . $apiKey;
        $apiHeaders[] = 'cust-ref-id:17235683';
        $apiHeaders[] = 'client-secret:' . $clientSecret;
        $apiHeaders[] = 'Content-Type: application/json';

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            print_r($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        /*$cibil_details = $leadModelObj->getCibilData($lead_id);
        //echo '<pre>';print_r($cibil_details); die;
        if ($cibil_details['status'] == 1) {
            $apiResponseJson = $cibil_details['cibil_data'][0]['response'];
        }
        */

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.transunioncibil.com/acquire/credit-assessment/v1/cir-report",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($apiRequestJson),
            CURLOPT_HTTPHEADER => $apiHeaders,
            CURLOPT_SSLCERT => COMP_PATH . '/../SSLRequests/www.kasarcredit.com.p12',
            CURLOPT_SSLCERTTYPE => 'p12'
        ));

        $apiResponseJson = curl_exec($curl);

        curl_close($curl);

        if ($debug) {
            echo "<br/><br/><br/><br/><br/><br/>=======Response JSON=========<br/><br/>";
            echo $apiResponseJson;
            print_r($apiResponseJson);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!empty($apiResponseJson)) {
            if (AWS_S3_FLAG) {
                require_once(COMP_PATH . '/CommonComponent.php');
                $CommonComponent = new CommonComponent();

                $upload_file = array();
                $upload_file['flag'] = 1;
                $upload_file['file'] = $apiResponseJson;
                $upload_file['new_file_name'] = 'cibil';
                $upload_file['ext'] = 'txt';

                $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                if ($upload_status['status'] != 1) {
                    throw new ErrorException("Some error occured while uploading file.");
                }
                $cibil_html = $upload_status['file_name'];
                $s3_flag = 1;
            } else {
                $cibil_html = base64_decode($apiResponseJson, true);
                $file_name = "cibil_html_" . $lead_id . "_" . $date("YmdHis");
                $write_file = fopen(UPLOAD_PATH . $file_name, "a+");
                $file_write_flag = fwrite($write_file, $cibil_html);
                fclose($write_file);

                if (!$file_write_flag) {
                    throw new ErrorException("Some error occured while writing file.");
                }
                $cibil_html = $file_name;
            }
            $apiStatusId = 1;
        } else {
            throw new ErrorException("Empty response from CRIF Report API");
        }
    } catch (ErrorException $le) {
        $apiStatusId = 2;
        $errorMessage = $le->getMessage();
    } catch (RuntimeException $re) {
        $apiStatusId = 3;
        $errorMessage = $re->getMessage();
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    if (!empty($cibilRequest)) {
        if (AWS_S3_FLAG) {
            require_once(COMP_PATH . '/CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $upload_file = array();
            $upload_file['flag'] = 1;
            $upload_file['file'] = base64_encode($cibilRequest);
            $upload_file['new_file_name'] = 'cibil';
            $upload_file['ext'] = 'txt';

            $upload_request = $CommonComponent->upload_document($lead_id, $upload_file);

            if ($upload_request['status'] != 1) {
                throw new ErrorException("Some error occured while uploading file.");
            }
            $cibil_request = $upload_request['file_name'];
            $s3_flag = 1;
        }
    }

    if (!empty($cibilResponse)) {
        if (AWS_S3_FLAG) {
            require_once(COMP_PATH . '/CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $upload_file = array();
            $upload_file['flag'] = 1;
            $upload_file['file'] = base64_encode($cibilResponse);
            $upload_file['new_file_name'] = 'cibil';
            $upload_file['ext'] = 'txt';

            $upload_response = $CommonComponent->upload_document($lead_id, $upload_file);

            if ($upload_response['status'] != 1) {
                throw new ErrorException("Some error occured while uploading file.");
            }
            $cibil_response = $upload_response['file_name'];
            $s3_flag = 1;
        }
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['cibil_html'] = $cibil_html;
    $response_array['errors'] = $errorMessage;
    // $response_array['request_json'] = $apiRequestJson;
    // $response_array['response_json'] = $apiRequestJson;
    $response_array['request_file_name'] = $cibil_request;
    $response_array['response_file_name'] = $cibil_response;
    $response_array['s3_flag'] = $s3_flag;

    return $response_array;
}

function splitTextForCibil($text, $maxLength = 40) {
    $words = explode(' ', $text);
    $resultParts = [];
    $currentPart = '';

    foreach ($words as $word) {
        if (strlen($currentPart) + strlen($word) + 1 > $maxLength) {
            $resultParts[] = trim($currentPart);
            $currentPart = $word;
        } else {
            if (!empty($currentPart)) {
                $currentPart .= ' ';
            }
            $currentPart .= $word;
        }
    }

    if (!empty($currentPart)) {
        $resultParts[] = trim($currentPart);
    }

    return $resultParts;
}
?>
