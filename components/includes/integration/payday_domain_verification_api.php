<?php
function domain_verified_api_call($method_name = "", $lead_id = "", $request_array = array()) {
    common_log_writer(6, "Domain Verified | $lead_id | $method_name");
    $responseArray = array("status" => 0, "errors" => "");
    $opertion_array = array(
        "GET_DOMAIN_VERIFICATION" => 1
    );
    $method_id = $opertion_array[$method_name];
    if ($method_id == 1) {
        $responseArray = domain_verification_api_call($lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }
    common_log_writer(6, "Domain Verified end | $lead_id | $method_name | " . json_encode($responseArray));
    return $responseArray;
}

function domain_verification_api_call($lead_id, $request_array = array()) {
    $envSet = ENVIRONMENT;
    require_once(COMP_PATH . '/includes/integration/integration_config.php');
    $leadModelObj = new LeadModel();
    $apiStatusId = 0;
    $emailValidateStatus = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";


    $type = "SIGNZY_API";
    $sub_type = "GET_DOMAIN_VERIFICATION";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
    // $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    try {
        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        $app_data = $LeadDetails["app_data"];
        $app_office_mail = !empty($app_data["alternate_email"]) ? $app_data["alternate_email"] : "";
        $lead_status_id = !empty($app_data["lead_status_id"]) ? $app_data["lead_status_id"] : "";
        $office_email = strtolower($app_office_mail);
        $domain_url = substr(strrchr($office_email, "@"), 1);
        $apiRequestJson = '{ "domainName": "' . $domain_url . '"}';
        $apiHeaders = array('Content-Type: application/json', 'Authorization:' . $apiConfig["Token"]);
        if ($debug == 1) {
            echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiConfig["ApiUrl"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => $apiHeaders,
        ));

        $apiResponseJson = curl_exec($curl);
        $apiResponseData = json_decode($apiResponseJson, true);
        $apiResponseDateTime = date("Y-m-d H:i:s");
        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        if ($debug == 1) {
            echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
        }

        if (curl_errno($curl) && !$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($apiResponseData)) {
                $apiResponseData = trim_data_array($apiResponseData);

                if (!empty($apiResponseData['result']) && !empty($apiResponseData['result']['creationDate'])) {

                    if (!empty($apiResponseData['result']['domainName'])) {

                        $registration_date = $apiResponseData['result']['creationDate'];
                        $registration_date = str_replace("/", "-", $registration_date);
                        $registration_date = str_replace(" ", "T", $registration_date);
                        $registration_date = date("Y-m-d", strtotime($registration_date));
                        $apiStatusId = 1;
                    } elseif ($apiResponseData['result']['domainName'] == '') {
                        throw new ErrorException("Domain not found.");
                    } else {
                        throw new ErrorException($apiResponseData['errors']['message']);
                    }
                } elseif (!empty($apiResponseData['error'])) {
                    $temp_error = !empty($apiResponseData['error']['message']) ? $apiResponseData['error']['message'] : "Some error occurred. Please try again.";
                    throw new ErrorException($temp_error);
                } else {
                    $temp_error = "Some error occurred. Please try again.";
                    throw new ErrorException($temp_error);
                }
            } else {
                throw new ErrorException("Invalid api response..");
            }
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

    $insertApiLog = array();
    $insertApiLog["dv_provider"] = 1; // Signzy
    $insertApiLog["dv_method_id"] = 1;
    $insertApiLog["dv_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["dv_email"] = $office_email;
    $insertApiLog["dv_domain"] = $domain_url;
    $insertApiLog["dv_registration_date"] = $registration_date;
    $insertApiLog["dv_api_status_id"] = $apiStatusId;
    $insertApiLog["dv_request"] = addslashes($apiRequestJson);
    $insertApiLog["dv_response"] = addslashes($apiResponseJson);
    $insertApiLog["dv_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : $errorMessage;
    $insertApiLog["dv_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["dv_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["dv_user_id"] = $user_id;

    $leadModelObj->insertTable("api_domain_verification_logs", $insertApiLog);

    $domain_age = 0;
    if (!empty($registration_date)) {
        $domain_age = date_diff(date_create($registration_date), date_create(date("Y-m-d")))->format("%y");
    }

    if ($apiStatusId == 1) {
        $lead_remarks = "Domain Verified Successfully<br/>Domain Name: " . $domain_url . "<br/>Registration Date: " . date("d-M-Y", strtotime($registration_date)) . "<br/>Domain Age: <b>" . $domain_age . " years<b>";
        $leadModelObj->updateLeadCustomerTable($lead_id, ['customer_domain_flag' => 1, 'customer_domain_verified_on' => date("Y-m-d H:i:s"), 'customer_domain_request_ip' => $_SERVER['REMOTE_ADDR']]);
    } else {
        $lead_remarks = "Domain Verification Failed";
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['email'] = $office_email;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    return $returnResponseData;
}
