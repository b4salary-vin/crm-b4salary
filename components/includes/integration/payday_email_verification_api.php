
<?php

function email_verification_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "Email Verification started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");
    $opertion_array = array(
        "PERSONAL_EMAIL_VALIDATE" => 1,
        "OFFICE_EMAIL_VALIDATE" => 2,
    );

    $method_id = $opertion_array[$method_name];
    if ($method_id == 1) {
        //$responseArray = signzy_email_verification_api_call($method_id, $lead_id, $request_array);
        $responseArray = office_email_verification_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = office_email_verification_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(6, "Email Verification end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function office_email_verification_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "office_email_verification_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "OFFICE_EMAIL_VERIFICATION";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $token_string = "";

    $email_address = "";
    $email_validate_status = "";
    $alternate_email_verified_status = "";

    $lead_status_id = 0;

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
        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $mailtype = (isset($request_array['email_type'])) ? 'Personal ' : 'Office ';

        if (isset($request_array['email_type'])) {
            //Personal Email
            $email_address = !empty($app_data['email']) ? trim($app_data['email']) : "";
            $email_address_status = !empty($app_data['email_verified_status']) ? trim($app_data['email_verified_status']) : "";
        } else {
            //Office Email
            $email_address = !empty($app_data['alternate_email']) ? trim($app_data['alternate_email']) : "";
            $email_address_status = !empty($app_data['alternate_email_verified_status']) ? trim($app_data['alternate_email_verified_status']) : "";
        }



        if (empty($email_address)) {
            $errmsg = (isset($request_array['email_type'])) ? 'Missing Personal email address' : 'Missing office email address';
            throw new Exception($errmsg);
        }

        if ($email_address_status == "YES") {
            $errmsg = (isset($request_array['email_type'])) ? 'Missing Personal email address' : 'Missing office email address';
            throw new Exception($errmsg);
        }
        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiConfig['ApiUrl'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ' {
                "emailId": "' . $email_address . '"
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: '.$apiConfig['Token'],
                'Content-Type: application/json'
            ),
        ));

        $apiResponseJson = curl_exec($curl);
        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);
            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);
                if (!empty($apiResponseData['result'])) {
                    $apiResponseData = $apiResponseData['result'];
                    if (!empty($apiResponseData['validEmail'])) {
                        $apiStatusId = 1;
                        if ($apiResponseData['validEmail'] == "true" || $apiResponseData['subStatus'] == "role_based") {
                            $alternate_email_verified_status = "YES";
                            $email_validate_status = 1;
                        } else {
                            $email_validate_status = 2;
                            $alternate_email_verified_status = "NO";
                        }
                    } else {
                        throw new ErrorException("Email response does not received from api.");
                    }
                } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                    throw new ErrorException($apiResponseData['error']['message']);
                }
                else {
                    throw new ErrorException("$mailtype Email verification : API Response empty.");
                }
            } else {

                throw new ErrorException("$mailtype Email verification : API Response empty.");
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

    if ($apiStatusId == 1) {
        $lead_remarks = "$mailtype Email Verification API CALL(Success) <br/> $mailtype Email : $email_address | Result : " . $alternate_email_verified_status;

        if ($email_validate_status == 1) {
            if (isset($request_array['email_type'])) {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['email_verified_status' => "YES", 'email_verified_on' => date("Y-m-d H:i:s")]);
            } else {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['alternate_email_verified_status' => "YES", 'alternate_email_verified_on' => date("Y-m-d H:i:s")]);
            }
        }
    } else {
        $lead_remarks = "$mailtype Email Verification API CALL(Failed) <br/> $mailtype Email : $email_address | Error : " . $errorMessage;
    }


    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ev_provider_id"] = 2; //SIGNZY
    $insertApiLog["ev_method_id"] = $method_id;
    $insertApiLog["ev_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ev_email"] = $email_address;
    $insertApiLog["ev_email_validate_status"] = $email_validate_status;
    $insertApiLog["ev_api_status_id"] = $apiStatusId;
    $insertApiLog["ev_request"] = addslashes($apiRequestJson);
    $insertApiLog["ev_response"] = addslashes($apiResponseJson);
    $insertApiLog["ev_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ev_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ev_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ev_user_id"] = $user_id;

    $leadModelObj->insertTable("api_email_verification_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['email'] = $email_address;
    $response_array['email_validate_status'] = $email_validate_status;
    $response_array['errors'] = !empty($errorMessage) ? "Office Email Error : " . $errorMessage : "";
    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}