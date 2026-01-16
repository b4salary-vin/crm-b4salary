 <?php
    function payday_face_match_verification_api_call($method_name = "", $lead_id = 0, $request_array = []) {
        common_log_writer(3, "PAYDAY AADHAR MASKING API started | $lead_id");
        $responseArray = ["status" => 0, "errors" => ""];
        $opertion_array = array(
            "GET_FACE_MATCH_VERFICATION" => 1,
        );

        $method_id = $opertion_array[$method_name]  ?? null;;

        if ($method_id == 1) {
            $responseArray = face_match_verified_api_call($method_id, $lead_id, $request_array);
        } else {
            $responseArray["errors"] = "invalid opertation called";
        }

        common_log_writer(3, "PAYDAY FACE MATCH VERIFICATION API end | $lead_id | $method_name | " . json_encode($responseArray));
        return $responseArray;
    }

    function face_match_verified_api_call($method_id, $lead_id = 0, $request_array = []) {

        $envSet = ENVIRONMENT;
        require_once(COMP_PATH . '/includes/integration/integration_config.php');
        $leadModelObj = new LeadModel();
        $apiStatusId = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";

        $type = "SIGNZY_API";
        $sub_type = "GET_FACE_MATCH_VERFICATION";

        $debug = !empty($_REQUEST["test"]) ? 1 : 0;
        // $debug = 1;
        $user_id = !empty($_SESSION["isUserSession"]["user_id"]) ? $_SESSION["isUserSession"]["user_id"] : 0;
        $leadModelObj = new LeadModel();

        try {

            $apiConfig = integration_config($type, $sub_type);

            if ($debug == 1) {
                echo "<pre>";
                print_r($apiConfig);
            }

            if ($apiConfig["Status"] != 1) {
                throw new Exception($apiConfig["ErrorInfo"]);
            }

            if (empty($lead_id)) {
                throw new Exception("Missing lead id.");
            }

            $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

            $app_data = $LeadDetails["app_data"];
            $app_status = !empty($app_data["status"]) ? $app_data["status"] : "";
            $app_stage = !empty($app_data["stage"]) ? $app_data["stage"] : "";
            $lead_status_id = !empty($app_data["lead_status_id"]) ? $app_data["lead_status_id"] : "";
            $lead_ekyc_completed = !empty($app_data["customer_digital_ekyc_flag"]) ? $app_data["customer_digital_ekyc_flag"] : "";

            if ($lead_ekyc_completed != 1) {
                throw new Exception("eKyc did not complete");
            }

            if ($LeadDetails["status"] != 1) {
                throw new Exception("Application details not found");
            }

            $LeadDocs = $leadModelObj->getFaceMatchDocDetails($lead_id);

            if ($LeadDocs["status"] != 1) {
                throw new Exception("Please upload the aadhar in documents.");
            }

            $doc_data = !empty($LeadDocs["doc_data"]) ? $LeadDocs["doc_data"] : "";
            $doc_selfie_img = $doc_data[18]['file'];
            $doc_digilocker_aadhar_img = $doc_data[19]['file'];

            if ($debug == 1) {
                echo "<br/><br/> =======Doc Data======<br/><br/>";
                print_r($doc_data);
            }

            if (empty($doc_selfie_img) || empty($doc_digilocker_aadhar_img)) {
                throw new Exception("Selfie or Aadhar image not found.");
            }

            $first_img_url = COMP_DOC_URL . $doc_selfie_img;
            $second_img_url = COMP_DOC_URL . $doc_digilocker_aadhar_img;

            $app_data = $LeadDetails["app_data"];

            $apiRequestJson  = '{
                "firstImage": "' . $first_img_url . '",
                "secondImage": "' . $second_img_url . '"
            }';

            $apiUrl = $apiConfig["ApiUrl"];
            $apiToken = $apiConfig["ApiKey"];

            $apiHeaders = array('Content-Type: application/json', 'Authorization:' . $apiToken);

            if ($debug == 1) {
                echo "<br/><br/> =======Header Plain======<br/><br/>" . $apiHeaders;
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

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
                CURLOPT_POSTFIELDS => $apiRequestJson,
                CURLOPT_HTTPHEADER => $apiHeaders,
            ));

            $apiResponseJson = curl_exec($curl);
            if ($debug == 1) {
                echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
            }

            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
            $apiResponseDateTime = date("Y-m-d H:i:s");

            if (curl_errno($curl)) {
                $curlError = curl_error($curl);
                curl_close($curl);
                throw new RuntimeException("Something went wrong. Please try after sometimes.");
            } else {
                curl_close($curl);
                $apiResponseData = json_decode($apiResponseJson, true);

                if (!empty($apiResponseData)) {
                    // Trim and clean the response data
                    $apiResponseData = common_trim_data_array($apiResponseData);
                    if (!empty($apiResponseData)  && $apiResponseData['result']['verified'] == 1) {
                        $apiStatusId = 1;
                        $faceMatchParcent = $apiResponseData['result']['matchPercentage'];
                    } else {
                        throw new ErrorException("Please check the raw response for error details.");
                    }
                } else {
                    throw new ErrorException("Empty response from API.");
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
            $lead_remarks = "Face Match Verification Completed Successfully";
            $leadModelObj->updateLeadCustomerTable($lead_id, ['face_match_verified_flag' => 1, 'face_match_verified_on' => date("Y-m-d H:i:s")]);
        } else {
            $lead_remarks = "Face Match Verification Failed";
        }

        $insertApiLog = [
            "fm_provider" => 1, // 1 for Signzy
            "fm_method_id " => $method_id,
            "fm_lead_id " => !empty($lead_id) ? $lead_id : null,
            'fm_score' => !empty($faceMatchParcent) ? $faceMatchParcent : 0,
            "fm_request" => addslashes($apiRequestJson),
            "fm_response" => addslashes($apiResponseJson),
            "fm_api_status_id" => $apiStatusId,
            "fm_errors" => $apiStatusId == 3 ? addslashes($curlError) : addslashes($errorMessage),
            "fm_request_datetime" => !empty($apiRequestDateTime) ? $apiRequestDateTime : date("Y-m-d H:i:s"),
            "fm_response_datetime" => !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s"),
            "fm_user_id" => $user_id,
        ];

        $leadModelObj->insertTable("api_face_match_logs", $insertApiLog);

        $insertLeadFollowupLog = [
            "lead_id" => $lead_id,
            "status" => $app_status,
            'user_id' => $user_id,
            'stage' =>  $app_stage,
            'lead_followup_status_id' => $lead_status_id,
            'remarks' => $lead_remarks . '<br>' . "Face Match Percentage: " . $faceMatchParcent  . '<br>' . 'Selfie Image: <a href="' . $second_img_url  . '" target="_blank"><b>View Selfie</b></a>' . '<br>' . 'Aadhar Image: <a href="' . $first_img_url . '" target="_blank"><b>View Aadhar</b></a>',
            'created_on' => $apiResponseDateTime,
            'updated_on' => $apiResponseDateTime,
        ];

        $leadModelObj->insertTable("lead_followup", $insertLeadFollowupLog);

        if ($apiStatusId == 1) {
            $leadModelObj->updateLeadCustomerTable($lead_id, ['customer_face_match_flag' => 1, 'customer_face_match_verified_on' => date("Y-m-d H:i:s"), 'customer_face_match_request_ip' => $_SERVER['REMOTE_ADDR']]);
        }

        $response_array['status'] = $apiStatusId;
        $response_array['errors'] = !empty($errorMessage) ? $errorMessage : "";
        // $response_array['request_json'] = $apiRequestJson;
        // $response_array['response_json'] = $apiResponseJson;

        return $response_array;
    }
