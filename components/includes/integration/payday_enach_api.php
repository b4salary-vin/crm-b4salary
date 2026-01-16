<?php

function payday_enach_api($method_name = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "Transaction initiate started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "TRANSACTION_INITIATE" => 1,
        "MANDATE_VERIFICATION" => 2,
        "TRANSACTION_CANCEL" => 3,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = initiate_transation_api_call($method_id, $lead_id, $request_array);
    } elseif ($method_id == 2) {
        $responseArray = mandate_validation_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }
    common_log_writer(3, "Transaction initiate ended | $lead_id | $method_name");
    return $responseArray;
}

function initiate_transation_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "initiate_transation_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 1;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $hardcoded = true;

    $type = "ENACH";
    $sub_type = "INITIATE_TRANSACTION";

    $debug = !empty($_REQUEST['test']) ? 1 : 0;
    // $debug = 1;

    $user_id = $request_array['user_id'] ?? $_SESSION['isUserSession']['user_id'] ?? 0;

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
        $merchantId = $apiConfig["merchantId"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        if (empty($request_array['requested_amount'])) {
            throw new Exception("Requested amount is missing.");
        }

        if (empty($request_array['requested_end_date'])) {
            throw new Exception("Requested end date is missing.");
        }

        $requested_amount = $request_array['requested_amount'];
        $requested_end_date = $request_array['requested_end_date'];

        $LeadDetails = $leadModelObj->geteNachDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['enach_data'] ?? [];
        $lead_status_id = $app_data['lead_status_id'] ?? "";
        $mandate_registration_no = $app_data['loan_enach_mandate_registration_no'] ?? "";
        $loan_enach_mandate_datetime = $app_data['loan_enach_mandate_datetime'] ?? "";

        if (empty($mandate_registration_no)) {
            throw new Exception("Mandate registration number is missing.");
        }

        if (empty($lead_status_id) && !in_array($lead_status_id, [14, 19])) {
            throw new Exception("Invalid lead status id.");
        }

        $requestData = [
            "merchant" => [
                "identifier" => $merchantId
            ],
            "payment" => [
                "instrument" => [
                    "identifier" => "eNach"
                ],
                "instruction" => [
                    "amount" => $requested_amount,
                    "endDateTime" => date("dmY", strtotime($requested_end_date)),
                    "identifier" => $mandate_registration_no
                ]
            ],
            "transaction" => [
                "deviceIdentifier" => "S",
                "type" => "002",
                "currency" => "INR",
                "identifier" => rand(999, 999999),
                "subType" => "003",
                "requestType" => "TSI"
            ]
        ];

        $apiRequestJson = json_encode($requestData);

        if ($debug == 1) {
            echo "<br/><br/>=======Request String=========<br/><br/>";
            echo $apiRequestJson;
        }

        $apiHeaders = [
            'Content-type: application/json'
        ];

        if ($debug == 1) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        if ($hardcoded) {
            $apiResponseJson = '{"merchantCode": "L1055993","merchantTransactionIdentifier": "605400","merchantTransactionRequestType": "TSI","responseType": "web","transactionState": null,"merchantAdditionalDetails": null,"paymentMethod": {"token": "","instrumentAliasName": "","instrumentToken": "","bankSelectionCode": "","paymentMode": null,"aCS": null,"oTP": null,"paymentTransaction": {"amount": "13","balanceAmount": "","bankReferenceIdentifier": "","dateTime": "15022025","errorMessage": "Transaction scheduling request has been initiated","identifier": "701987476967","refundIdentifier": "","statusCode": "0398","statusMessage": "I","instruction": null,"reference": null,"mandateDetails": null},"authentication": null,"error": {"code": "S1006","desc": "Transaction scheduling request has been initiated"},"instrument": null},"error": null,"merchantResponseString": null,"pdfDownloadUrl": null}';
        } else {
            $curl = curl_init();
            curl_setopt_array($curl, [
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
            ]);

            $apiResponseJson = curl_exec($curl);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/>=======Response String=========<br/><br/>";
            echo $apiResponseJson;
        }

        if (curl_errno($curl) && $hardcoded == false) {
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        }

        if (!empty($apiResponseJson)) {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData) && isset($apiResponseData['paymentMethod']['error']['code']) && $apiResponseData['paymentMethod']['error']['code'] == 'S1006') {
                $return_message = $apiResponseData['paymentMethod']['error']['desc'];
                $requestId = $apiResponseData['merchantTransactionIdentifier'] ?? "";
                $apiStatusId = 1;
            } elseif (!empty($apiResponseData) && isset($apiResponseData['paymentMethod']['error']['code']) && $apiResponseData['paymentMethod']['error']['code'] != 'ERR1002') {
                $return_message = $apiResponseData['paymentMethod']['error']['desc'];
                throw new RuntimeException($return_message);
            } else {
                throw new Exception("Something went wrong. Please try after sometimes.");
            }
        } else {
            throw new ErrorException("Something went wrong. Please try after sometimes.");
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

    $lead_remarks = $apiStatusId == 1
        ? "TRANSACTION INITIATE API CALL(Successful)<br/>Request ID : $requestId <br/>Response : $return_message"
        : "TRANSACTION INITIATE API CALL(Failed)<br/>Error : $errorMessage";

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = [
        "aetl_lead_id" => $lead_id,
        "aetl_user_id" => $user_id,
        "enach_method_id" => $method_id,
        "aetl_request_id" => $requestId,
        "aetl_request" => addslashes($apiRequestJson),
        "aetl_response" => addslashes($apiResponseJson),
        "aetl_status_id" => $apiStatusId,
        "aetl_errors" => $errorMessage,
        "aetl_request_datetime" => $apiRequestDateTime,
        "aetl_response_datetime" => $apiResponseDateTime,
        "aetl_requested_amount" => $requested_amount,
        "aetl_deduct_request_date" => date("Y-m-d H:i:s", strtotime($requested_end_date)),
        "aetl_requested_amount" => $requested_amount
    ];

    $leadModelObj->insertTable("api_enach_transaction_schedule_logs", $insertApiLog);

    $response_array = [
        'status' => $apiStatusId,
        'request_id' => $requestId,
        'data' => $apiResponseData,
        'errors' => $errorMessage ? "ENACH ERROR : $errorMessage" : ""
    ];

    if ($debug == 1) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $encResponseJson;
    }

    return $response_array;
}

function mandate_validation_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "mandate_validation_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 1;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $hardcoded = false;

    $type = "ENACH";
    $sub_type = "MANDATE_VERIFICATION";

    $debug = !empty($_REQUEST['test']) ? 1 : 0;
    // $debug = 1;

    $user_id = $request_array['user_id'] ?? $_SESSION['isUserSession']['user_id'] ?? 0;

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
        $merchantId = $apiConfig["merchantId"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        // Lead details
        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'] ?? [];
        $lead_status_id = $app_data['lead_status_id'] ?? "";
        $lead_status = $app_data['status'] ?? "";
        $loan_enach_mandate_registration_no = $app_data['loan_enach_mandate_registration_no'] ?? "";

        if (empty($lead_status_id)) {
            throw new Exception("Invalid lead status id.");
        }

        if (in_array($lead_status_id, [14, 16, 17, 18, 19])) {
            throw new Exception("Lead is already in <strong>$lead_status</strong> status.");
        }

        if (!empty($loan_enach_mandate_registration_no)) {
            throw new Exception("Mandate registration number is already available.");
        }

        // eNach details
        $eNachDetails = $leadModelObj->geteNachLogs($lead_id);

        if ($eNachDetails['status'] != 1) {
            throw new Exception("eNach logs not found");
        }

        $enach_data = $eNachDetails['enach_data'] ?? [];
        $enach_transaction_id = $enach_data['enach_transaction_id'] ?? "";
        $enach_request = $enach_data['enach_request'] ?? "";

        if (empty($enach_transaction_id)) {
            throw new Exception("eNach transaction id is missing.");
        }

        if (empty($enach_request)) {
            throw new Exception("eNach request is missing.");
        }

        $enach_request = str_replace("\\", "", $enach_request);
        $enach_request = str_replace("'", '"', $enach_request);
        $enach_request_array = json_decode($enach_request, true);
        $loan_enach_mandate_datetime = $enach_request_array['debitStartDate'] ?? "";

        if (empty($loan_enach_mandate_datetime)) {
            throw new Exception("eNach mandate date time is missing.");
        }

        $apiRequestJson = '{
                            "merchant": {
                                "identifier": "L1055993"
                            },
                            "payment": {
                                "instruction": {}
                            },
                            "transaction": {
                                "deviceIdentifier": "S",
                                "type": "002",
                                "currency": "INR",
                                "identifier": "' . $enach_transaction_id . '",
                                "dateTime": ' . date("d-m-Y", strtotime($loan_enach_mandate_datetime)) . ',
                                "subType": "002",
                                "requestType": "TSI"
                            },
                            "consumer": {
                                "identifier": ""
                            }
                        }';

        if ($debug == 1) {
            echo "<br/><br/>=======Request String=========<br/><br/>";
            echo $apiRequestJson;
        }

        $apiHeaders = [
            'Content-type: application/json'
        ];

        if ($debug == 1) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        if ($hardcoded) {
            $apiResponseJson = '{"merchantCode": "L1055993","merchantTransactionIdentifier": "TXN67ee52b6b38651531","merchantTransactionRequestType": "TSI","responseType": "web","transactionState": null,"merchantAdditionalDetails": null,"paymentMethod": {    "token": "1223306578",    "instrumentAliasName": "",    "instrumentToken": "00811140041614",    "bankSelectionCode": "",    "paymentMode": null,    "aCS": null,    "oTP": null,    "paymentTransaction": {        "amount": null,        "balanceAmount": "",        "bankReferenceIdentifier": "HDFC7010304252016333",        "dateTime": null,        "errorMessage": "Mandate Verification Successfull",        "identifier": "611913509",        "refundIdentifier": "",        "statusCode": "0300",        "statusMessage": "Mandate Verification Successfull",        "instruction": null,        "reference": null,        "mandateDetails": null    },    "authentication": null,    "error": {        "code": "",        "desc": "Mandate Verification Successfull"    },    "instrument": null,    "status_check_timeout": null},"error": null,"merchantResponseString": null,"pdfDownloadUrl": null}';
        } else {
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
            ));


            $apiResponseJson = curl_exec($curl);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/>=======Response String=========<br/><br/>";
            echo $apiResponseJson;
        }

        if (curl_errno($curl) && $hardcoded == false) {
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        }

        if (!empty($apiResponseJson)) {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData) && !empty($apiResponseData['paymentMethod']['error']['desc']) && $apiResponseData['paymentMethod']['paymentTransaction']['statusCode'] == '0300' && $apiResponseData['paymentMethod']['error']['code'] == '') {
                $return_message = $apiResponseData['paymentMethod']['error']['desc'];
                $requestId = $apiResponseData['merchantTransactionIdentifier'] ?? "";
                $mandate_registration_no = $apiResponseData['paymentMethod']['token'] ?? "";

                if (empty($mandate_registration_no)) {
                    throw new Exception("Mandate registration number is missing.");
                }

                if ($return_message != "Mandate Verification Successfull") {
                    throw new Exception($return_message);
                }

                $apiStatusId = 1;
            } elseif (!empty($apiResponseData) && !empty($apiResponseData['paymentMethod']['error']['code'])) {
                $return_message = $apiResponseData['paymentMethod']['error']['desc'];
                throw new RuntimeException($return_message);
            } else {
                throw new Exception("Something went wrong. Please try after sometimes.");
            }
        } else {
            throw new ErrorException("Something went wrong. Please try after sometimes.");
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

    $lead_remarks = $apiStatusId == 1
        ? "MANDATE VERIFICATION API CALL(Successful)<br/>Request ID : $requestId <br/>Mandate Registration No : $mandate_registration_no <br/>Response : $return_message"
        : "MANDATE VERIFICATION API CALL(Failed)<br/>Request ID : $enach_transaction_id<br/>Error : $errorMessage";

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $updateLoan = [
        "loan_enach_mandate_registration_no" => $mandate_registration_no,
        "loan_enach_mandate_datetime" => date("Y-m-d H:i:s")
    ];

    if ($apiStatusId == 1) {
        $leadModelObj->updateLoanTable($lead_id, $updateLoan);
    }

    $insertApiLog = [
        "enach_provider" => 1,
        "enach_method_id" => $method_id,
        "enach_lead_id" => $lead_id,
        "enach_transaction_id" => $enach_transaction_id,
        "enach_request" => addslashes($apiRequestJson),
        "enach_response" => addslashes($apiResponseJson),
        "enach_status_id" => $apiStatusId,
        "enach_transaction_status" => 2,
        "enach_errors" => $errorMessage,
        "enach_request_datetime" => $apiRequestDateTime,
        "enach_response_datetime" => $apiResponseDateTime,
        "enach_user_id" => $user_id
    ];

    $leadModelObj->insertTable("api_enach_logs", $insertApiLog);

    $response_array = [
        'status' => $apiStatusId,
        'request_id' => $requestId,
        'errors' => $errorMessage ? "ENACH ERROR : $errorMessage" : ""
    ];

    if ($debug == 1) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseData;
    }

    return $response_array;
}
