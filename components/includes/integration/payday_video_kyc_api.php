<?php

function payday_video_kyc_api($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(3, "Video e-kyc Code started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "GET_VIDEO_KYC_REQUEST" => 1,
        "SEND_VIDEO_KYC_EMAIL" => 2
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = video_kyc_api_call($method_id, $lead_id, $request_array);
    } elseif ($method_id == 2) {
        $responseArray = send_video_kyc_email($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }
    common_log_writer(3, "Video e-kyc Code ended | $lead_id | $method_name");
    return $responseArray;
}

function video_kyc_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "video_kyc_api_call started | $lead_id");

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

    $type = "VIDEO_KYC_API";
    $sub_type = "VIDEO_KYC_REQUEST";

    $debug = !empty($_REQUEST['test']) ? 1 : 0;

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
        $product = $apiConfig["product"];
        $authToken = $apiConfig["authToken"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'] ?? [];
        $lead_status_id = $app_data['lead_status_id'] ?? "";
        $disbursal_date = $app_data['disbursal_date'];
        $repayment_date = $app_data['repayment_date'];
        $repayment_amount = $app_data['repayment_amount'];

        if (empty($disbursal_date) || empty($repayment_date) || empty($repayment_amount)) {
            throw new Exception("Disbursal date, repayment date or repayment amount is missing.");
        }

        $first_name = strtoupper(trim($app_data['first_name'] ?? ""));
        $middle_name = strtoupper(trim($app_data['middle_name'] ?? ""));
        $sur_name = strtoupper(trim($app_data['sur_name'] ?? ""));
        $customer_full_name = trim("$first_name $middle_name $sur_name");

        $emailId = $app_data['email'] ?? "";
        $contactNumber = $app_data['mobile'] ?? "";
        $loan_amount = $app_data['loan_recommended'] ?? "";

        $requestData = [
            "leadId" => $lead_id,
            "name" => $customer_full_name,
            "emailId" => $emailId,
            "contactNumber" => $contactNumber,
            "product" => $product,
            "borrowerDetails" => [
                "Loan Amount" => $loan_amount,
                "Disbursal Date" => date("d M Y", strtotime($disbursal_date)),
                "Repayment Amount" => $repayment_amount,
                "Repayment Date" => date("d M Y", strtotime($repayment_date))
            ]
        ];

        $apiRequestJson = json_encode($requestData);

        if ($debug == 1) {
            echo "<br/><br/>=======Request String=========<br/><br/>";
            echo $apiRequestJson;
        }

        $apiHeaders = [
            'auth-token: ' . $authToken,
            'Content-type: application/json'
        ];

        if ($debug == 1) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

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
        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (curl_errno($curl)) {
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        }

        curl_close($curl);

        if (!empty($apiResponseJson)) {
            $apiResponseData = json_decode($apiResponseJson, true);
            $requestId = $apiResponseData['requestId'];
            $customerUrl = $apiResponseData['customerUrl'];
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
        ? "VIDEO KYC API CALL(Success)<br/>Customer Name : $customer_full_name<br/>Request ID : $requestId"
        : "VIDEO KYC API CALL(Failed)<br/>Error : $errorMessage";

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = [
        "avedl_provider" => 1,
        "avedl_method_id" => $method_id,
        "avedl_lead_id" => $lead_id ?: NULL,
        "avedl_request_id" => $requestId ?? NULL,
        "avedl_request" => addslashes($apiRequestJson),
        "avedl_response" => addslashes($apiResponseJson),
        "avedl_return_url" => addslashes($customerUrl),
        "avedl_status_id" => $apiStatusId,
        "avedl_errors" => $apiStatusId == 3 ? addslashes($curlError) : addslashes($errorMessage),
        "avedl_request_datetime" => $apiRequestDateTime,
        "avedl_response_datetime" => $apiResponseDateTime ?: date("Y-m-d H:i:s"),
        "avedl_user_id" => $user_id
    ];
    $leadModelObj->insertTable("api_video_ekyc_logs", $insertApiLog);

    $response_array = [
        'status' => $apiStatusId,
        'url' => $customerUrl,
        'request_id' => $requestId,
        'data' => $apiResponseData,
        'errors' => $errorMessage ? "VIDEO KYC ERROR : $errorMessage" : ""
    ];

    if ($debug == 1) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $encResponseJson;
    }

    return $response_array;
}

function send_video_kyc_email($method_id, $lead_id = 0, $request_array = array()) {
    require_once(COMP_PATH . '/includes/integration/integration_config.php');
    $leadModelObj = new LeadModel();
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $user_id = $request_array['user_id'] ?? $_SESSION['isUserSession']['user_id'] ?? 0;

    $response = video_kyc_api_call(1, $lead_id, $request_array);
    $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

    if ($LeadDetails['status'] != 1) {
        throw new Exception("Application details not found");
    }

    $app_data = $LeadDetails['app_data'] ?? [];
    $name = $app_data['first_name'] ?? "";
    $emailId = $app_data['email'] ?? "";
    $lead_status_id = $app_data['lead_status_id'] ?? "";

    if (empty($response['url'])) {
        $lead_remarks = "VIDEO KYC EMAIL SEND FAILED<br/>Error : " . $response['errors'];
        $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
        return array('status' => 0, 'errors' => $response['errors']);
    }

    $html = '<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Video KYC Email</title>
                    </head>
                    <body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; border: 1px solid #ddd;">
                            <tr>
                                <td style="padding: 20px; text-align: center; background-color: #8180e0; color: #ffffff; font-size: 24px; font-weight: bold;">
                                    Video eKYC Verification
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center; padding: 20px;">
                                    <img src="https://sot-website.s3.ap-south-1.amazonaws.com/emailer/video_kyc.png" alt="Video KYC" style="max-width: 100%; height: auto;">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px;">
                                    <p style="font-size: 18px; font-weight: bold; color: #8180e0;">Dear ' . $name . ',</p>
                                    <p style="font-size: 14px; line-height: 22px;">
                                        Thank you for showing interest in <a href="https://salaryontime.com/" target="_blank" style="color: #8180e0; text-decoration: none;">Salary On Time</a>.
                                        Your Video eKYC link has been successfully generated.
                                    </p>
                                    <p style="font-size: 14px; line-height: 22px;">
                                        Click on the button below to proceed with your Video e-KYC:
                                    </p>
                                    <p style="text-align: center;">
                                        <a href="' . $response['url'] . '"
                                        style="background-color: #8180e0; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;"
                                        target="_blank">Start Video e-KYC</a>
                                    </p>
                                    <p style="font-size: 14px; line-height: 22px;">
                                        If you are unable to click the button, copy and paste the following link into your browser:
                                        <br>
                                        <a href="' . $response['url'] . '"
                                        target="_blank" style="color: #8180e0; word-break: break-word;">' . $response['url'] . '</a>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px; background-color: #f5f5f5;">
                                    <p style="font-size: 18px; font-weight: bold; color: #8180e0;">How it Works</p>
                                    <p><strong>Step 1:</strong> Click the Start Video e-kyc button.</p>
                                    <p><strong>Step 2:</strong> Allow the Audio, Video and Location permissions.</p>
                                    <p><strong>Step 3:</strong> Read script aloud to confirm the loan disbursement details and Ensure that your face is visible while reading the script during the video.</p>
                                    <p><strong>Step 4:</strong> Click next button.</p>
                                    <p><strong>Thank You!</strong> Your Video e-KYC submission has been received successfully.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px; background-color: #f5f5f5;">
                                    <p style="font-size: 18px; font-weight: bold; color: #8180e0;">Important Tips:</p>
                                    <p>Ensure Clear Photos: All images must be clear and legible for verification to proceed smoothly.</p>
                                    <p>Speak Clearly: When reading the script, ensure that your voice is clear and steady.</p>
                                    <p>Check Lighting: Make sure you are in a well-lit area to avoid dark or unclear images.</p>
                                </td>
                            </tr>
                            <!-- Footer Section -->
                            <tr>
                                <td style="background: #eeeded; color: #666; text-align: center; font-size: 14px; padding: 20px;">
                                    <p style="margin: 0;">&copy; 2025 SALARYONTIME. All rights reserved.</p>
                                    <p style="margin: 0;">
                                        <a href="https://salaryontime.com/privacypolicy" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                                        <a href="https://salaryontime.com/termsandconditions" target="_blank" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                                        <a href="https://salaryontime.com/contact-us" target="_blank" style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                                    </p>
                                    <div style="text-align: center; margin: 20px 0;">
                                        <p style="font-size: 14px; color: #777; margin: 10px;">Follow us on:</p>
                                        <a href="https://www.facebook.com/salaryontime" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                            <img src="https://sotcrm.com/public/new_images/images/facebook.png" alt="facebook" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                        </a>
                                        <a href="https://x.com/SalaryOnTme" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                            <img src="https://sotcrm.com/public/new_images/images/twitter.png" alt="twitter" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                        </a>
                                        <a href="https://www.linkedin.com/company/103731294/admin/dashboard" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                            <img src="https://sotcrm.com/public/new_images/images/linkedin.png" alt="linkedin" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                        </a>
                                        <a href="https://www.instagram.com/salaryontime" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                            <img src="https://sotcrm.com/public/new_images/images/instagram.png" alt="instagram" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                        </a>
                                        <a href="https://www.youtube.com/@salaryontime" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                            <img src="https://sotcrm.com/public/new_images/images/youtube.png" alt="youtube" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </body>
                    </html>';

    common_send_email($emailId, 'Video eKYC Verification | ' . $name . " | " . date("d M Y H:i A"), $html);

    $insertApiLog = [
        "avedl_provider" => 1,
        "avedl_method_id" => $method_id,
        "avedl_lead_id" => $lead_id ?: NULL,
        "avedl_request_id" => $response['request_id'] ?? NULL,
        "avedl_return_url" => addslashes($response['url']),
        "avedl_status_id" => 1,
        // "avedl_response" => $html,
        "avedl_request_datetime" => $apiRequestDateTime,
        "avedl_response_datetime" => $apiResponseDateTime ?: date("Y-m-d H:i:s"),
        "avedl_user_id" => $user_id
    ];

    $leadModelObj->insertTable("api_video_ekyc_logs", $insertApiLog);

    $lead_remarks = "VIDEO KYC EMAIL SEND SUCCESSFUL";

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $response_array = [
        'status' => 1
    ];

    return $response_array;
}
