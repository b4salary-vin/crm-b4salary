
<?php
function payday_utility_bill_verification_api_call($method_name = "", $lead_id = 0, $request_array = []) {
    common_log_writer(3, "PAYDAY UTILITY VERIFICATION API started | $lead_id");
    $responseArray = ["status" => 0, "errors" => ""];
    $opertion_array = array(
        "GET_UB_VERFICATION" => 1,
        "GET_UB_OCR_VERFICATION" => 2,
    );

    $method_id = $opertion_array[$method_name]  ?? null;;

    if ($method_id == 1) {
        $responseArray = utility_electricity_ocr_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = utility_electricity_verification_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(3, "PAYDAY UTILITY VERIFICATION API end | $lead_id | $method_name | " . json_encode($responseArray));
    return $responseArray;
}

function utility_electricity_ocr_api_call($method_id, $lead_id = 0, $request_array = []) {

    common_log_writer(3, "utility_electricity_ocr_api_call started | $lead_id");
    require_once COMP_PATH . "/includes/integration/integration_config.php";
    $response_array = ["status" => 0, "errors" => ""];

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $consumer_name = "-";
    $consumer_no = "-";

    $type = "SIGNZY_API";
    $sub_type = "ELECTRICITY_BILL_OCR";

    $debug = !empty($_REQUEST["test"]) ? 1 : 0;
    //$debug = 1;
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


        if ($LeadDetails["status"] != 1) {
            throw new Exception("Application details not found");
        }

        $LeadDocs = $leadModelObj->getUtilityDocDetails($lead_id, 33);

        if ($LeadDocs["status"] != 1) {
            throw new Exception("Please upload the Electricity Bill in documents.");
        }

        $doc_data = !empty($LeadDocs["doc_data"]) ? $LeadDocs["doc_data"] : "";

        $file_doc = $doc_data["file"];
        $file_url =  base_url() . "direct-document-file/" . $file_doc;
        $document_id = !empty($doc_data["docs_id"]) ? $doc_data["docs_id"] : 0;

        $app_data = $LeadDetails["app_data"];

        $apiRequestJson = json_encode($file_url, JSON_UNESCAPED_SLASHES);
        $apiRequestJson  = '{ "url": [' . $apiRequestJson . '] }';

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" .
                $apiRequestJson;
        }

        $apiHeaders = [
            'Authorization:' . $apiConfig['ApiKey'],
            'x-client-unique-id: ' . $apiConfig['ApiUniqueId'],
            'Content-Type: application/json'
        ];

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" .
                json_encode($apiHeaders);
        }

        //$apiRequestJson = json_encode(["url" => ["https://sotcrm.com//direct-document-file/4303161_lms_20250217180134580.pdf"]]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.signzy.app/api/v3/utility/single-kyc',
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
                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData)) {
                    $apiResponseData = $apiResponseData["result"];

                    if (isset($apiResponseData["status"]) && $apiResponseData["status"] == "completed") {
                        $apiStatusId = 1;

                        $electricityBill = $apiResponseData["electricityBill"];
                        // $electriBillProvider = $electricityBill["electricityBillProvider"];
                        // $consumer_name = $electricityBill["fullName"];
                        $consumer_no = $electricityBill["accountNumber"];
                        // $address = $electricityBill["address"];
                        if (empty($consumer_no)) {
                            throw new ErrorException("Docs file does not received proper from API.");
                        }
                    } else {
                        throw new ErrorException("Docs file does not received from API.");
                    }
                } else {
                    throw new ErrorException("Please check raw response for error details");
                }
            } else {
                throw new ErrorException("Empty response from API");
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

    $insertApiLog = [
        "aubl_provider" => 1,
        "aubl_method_id" => $method_id,
        "aubl_docs_id" => !empty($document_id) ? $document_id : null,
        "aubl_lead_id" => !empty($lead_id) ? $lead_id : null,
        "aubl_api_status_id" => $apiStatusId,
        "aubl_request" => addslashes($apiRequestJson),
        "aubl_response" => addslashes($apiResponseJson),
        "aubl_return_id" => !empty($consumer_no) ? $consumer_no : null,
        "aubl_errors" => $apiStatusId == 3 ? addslashes($curlError) : addslashes($errorMessage),
        "aubl_request_datetime" => !empty($apiRequestDateTime) ? $apiRequestDateTime : date("Y-m-d H:i:s"),
        "aubl_response_datetime" => !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s"),
        "aubl_user_id" => $user_id,
        "aubl_created_on" => date("Y-m-d H:i:s"),
    ];

    $leadModelObj->insertTable("api_utility_bill_logs", $insertApiLog);

    if ($apiStatusId == 1) {
        $lead_remarks = "Electricity Bill OCR Verification Completed";
    } else {
        $lead_remarks = "Electricity Bill OCR Verification Failed";
    }

    $insertLeadFollowupLog = [
        "lead_id" => $lead_id,
        "status" => $app_status,
        'user_id' => $user_id,
        'stage' =>  $app_stage,
        'lead_followup_status_id' => $lead_status_id,
        'remarks' => $lead_remarks,
        'created_on' => date("Y-m-d H:i:s"),
        'updated_on' => date("Y-m-d H:i:s"),
    ];

    $leadModelObj->insertTable("lead_followup", $insertLeadFollowupLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['api_status_id'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Utility Bill OCR API Error : " . $errorMessage : "";
    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }

    return $response_array;
}

function utility_electricity_verification_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "utility_electricity_verification_api_call started | $lead_id");
    require_once COMP_PATH . "/includes/integration/integration_config.php";

    $response_array = ["status" => 0, "errors" => ""];

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $consumer_name = "-";
    $consumer_no = "-";

    $type = "SIGNZY_API";
    $sub_type = "ELECTRICITY_VERFICATION";

    $debug = !empty($_REQUEST["test"]) ? 1 : 0;

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

        $UtilityBill = $leadModelObj->getUtilityBillList();
        $leadCustomerDetail = $leadModelObj->getLeadDetails($lead_id);

        if ($leadCustomerDetail["status"] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $leadCustomerDetail["app_data"];
        $lead_status_id = $app_data['lead_status_id'];
        $status = $app_data['status'];
        $stage = $app_data['stage'];
        $bill_fetch_status = $app_data['customer_electrical_bill_fetch_flag'];

        if ($bill_fetch_status == 1) {
            throw new Exception("Utility Bill already fetched.");
        }

        $electricity_logs = $leadModelObj->getElectrictyOCRLogs($lead_id);

        if ($electricity_logs["status"] != 1) {
            throw new Exception("OCR details not found");
        }

        $app_ocr_data = $LeadDocs["ocr_data"];

        $aubl_response = $app_ocr_data['aubl_response'];

        $ub_response = json_decode($aubl_response);

        if (isset($ub_response) && !empty($ub_response)) {

            $ub_ocr_responseData = $ub_response->result;

            if (isset($ub_ocr_responseData->status) && $ub_ocr_responseData->status == "completed") {
                $apiStatusId = 1;
                $electricityBill = $ub_ocr_responseData->electricityBill;
                $electriBillProvider = $electricityBill->electricityBillProvider;
                // $consumer_name = $electricityBill->fullName;
                $consumer_no = $electricityBill->accountNumber;
                if (empty($consumer_no)) {
                    throw new ErrorException(
                        "Docs file does not received proper from API."
                    );
                }
            } else {
                throw new ErrorException(
                    "Please check raw response for error details"
                );
            }
        } else {
            throw new ErrorException("Empty response from API");
        }

        $apiRequestJson = '{
            "consumerNo": "' . $consumer_no . '",
            "electricityProvider": "' . $electriBillProvider . '"
         }';

        $lead_status_id = !empty($app_data["lead_status_id"]) ? $app_data["lead_status_id"] : "";

        if (isset($apiRequestJson) && !empty($apiRequestJson)) {

            foreach ($UtilityBill['billl_data'] as $value) {
                $electriBillProvider =  $value['provider_name'];
            }

            // Output result
            if (!empty($electriBillProvider)) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.signzy.app/api/v3/electricitybills/fetch',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $apiRequestJson,
                    CURLOPT_HTTPHEADER => array(
                        'Authorization:' . $apiConfig['ApiKey'],
                        'x-client-unique-id: ' . $apiConfig['ApiUniqueId'],
                        'Content-Type: application/json'
                    ),
                ));

                $apiResponseJson = curl_exec($curl);

                curl_close($curl);
            } else {
                throw new ErrorException("Invalid electricity provider.");
            }
        }

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" .
                $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (curl_errno($curl)) {
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException(
                "Something went wrong. Please try after sometimes."
            );
        } else {
            curl_close($curl);
            $apiResponseData = json_decode($apiResponseJson, true);


            if (!empty($apiResponseData)) {
                $apiResponseData = $apiResponseData["result"];
                $apiResponseOCR = json_encode($apiResponseData, true);
                if (isset($apiResponseOCR) && $apiResponseOCR) {
                    $apiStatusId = 1;
                } else {
                    throw new ErrorException(
                        "Docs file does not received from API."
                    );
                }
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
        $lead_remarks = "Utility Bill Verification Completed";
        $leadModelObj->updateLeadCustomerTable($lead_id, ['customer_electrical_bill_fetch_flag' => 1, 'customer_electrical_bill_fectched_on' => date("Y-m-d H:i:s")]);
    } else {
        $lead_remarks = "Utility Bill Verification Failed";
        $leadModelObj->updateLeadCustomerTable($lead_id, ['customer_electrical_bill_fetch_flag' => 0, 'customer_electrical_bill_fectched_on' => NULL]);
    }


    $insertLeadFollowupLog = [
        'remarks' => $lead_remarks,
        "status" => $status,
        'user_id' => $user_id,
        'stage' =>  $stage,
        'lead_followup_status_id' => $lead_status_id,
        "lead_id" => $lead_id,
        'created_on' => $apiResponseDateTime,
        'updated_on' => $apiResponseDateTime,
    ];

    $leadModelObj->insertTable("lead_followup", $insertLeadFollowupLog);

    $insertApiLog = [
        "aubl_provider" => 1,
        "aubl_method_id" => $method_id,
        "aubl_lead_id" => !empty($lead_id) ? $lead_id : null,
        "aubl_api_status_id" => $apiStatusId,
        "aubl_request" => addslashes($apiRequestJson),
        "aubl_response" => addslashes($apiResponseJson),
        "aubl_return_id" => !empty($consumer_no) ? $consumer_no : null,
        "aubl_errors" => $apiStatusId == 3 ? addslashes($curlError) : addslashes($errorMessage),
        "aubl_request_datetime" => !empty($apiRequestDateTime) ? $apiRequestDateTime : date("Y-m-d H:i:s"),
        "aubl_response_datetime" => !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s"),
        "aubl_user_id" => $user_id,
    ];

    $leadModelObj->insertTable("api_utility_bill_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['api_status_id'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Electricity Bill API Error : " . $errorMessage : "";
    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }

    return $response_array;
}
?>
