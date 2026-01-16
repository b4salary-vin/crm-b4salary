<?php

function common_log_writer($type, $data) {

    //    if (!empty($data) && (false || $type == 1)) {
    if (!empty($data) && false) {


        $mode = "a+";
        $file_name = 'common_log_' . date("YmdH") . ".log";

        if ($type == 1) {
            $file_name = 'eligibility_' . date("YmdH") . ".log";
        } else if ($type == 2) {
            $file_name = 'crif_' . date("YmdH") . ".log";
        } else if ($type == 3) {
            $file_name = 'signzy_' . date("YmdH") . ".log";
        } else if ($type == 4) {
            $file_name = 'esign_signzy_' . date("YmdH") . ".log";
        } else if ($type == 5) {
            $file_name = 'digilocker_signzy_' . date("YmdH") . ".log";
        } else if ($type == 6) {
            $file_name = 'bre_result_' . date("YmdH") . ".log";
        } else if ($type == 7) {
            $file_name = 'bank_analysis_' . date("YmdH") . ".log";
        }

        $error_log_file = fopen(COMP_PATH . "/logs/$file_name", $mode);

        fwrite($error_log_file, PHP_EOL . "---" . date("Y-m-d H:i:s") . "---" . $data . PHP_EOL);
        fclose($error_log_file);
    }
}

function common_extract_value_from_xml($str1, $str2, $xml) {
    $stringExist = strpos($str1, $xml);
    if ($stringExist == false && $stringExist != 0) {
        $raw_data_string[0] = '';
    } else {
        $raw_data_string = explode($str1, trim($xml));
        $raw_data_string = explode($str2, trim($raw_data_string[1]));
    }
    return $raw_data_string[0];
}

function common_trim_data_array($inputstring) {

    if (!is_array($inputstring)) {
        $inputstring = trim($inputstring);
        $inputstring = addslashes($inputstring);
        $inputstring = preg_replace("!\s+!", " ", $inputstring);
        $inputstring = str_replace("Ã¢â‚¬â€œ", " ", $inputstring);
        $inputstring = str_replace("ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“", " ", $inputstring);
        $inputstring = preg_replace("!\s+!", " ", $inputstring);
        return $inputstring;
    }

    return array_map('common_trim_data_array', $inputstring);
}

//function common_parse_name($full_name = "") {
//    $first_name = $middle_name = $last_name = "";
//    if (!empty($full_name)) {
//        $first_name = substr($full_name, 0, (strpos($full_name, " ") !== false) ? strpos($full_name, " ") : strlen($full_name));
//        $full_name = trim(str_replace($first_name, "", $full_name));
//        $last_name = !empty($full_name) ? substr($full_name, (strrpos($full_name, " ", -1) !== false) ? strrpos($full_name, " ", -1) : 0, strlen($full_name)) : "";
//        $last_name = trim($last_name);
//        $full_name = trim(str_replace($last_name, "", $full_name));
//        $middle_name = trim($full_name);
//    }
//    return array("first_name" => $first_name, "middle_name" => $middle_name, "last_name" => $last_name);
//}

function common_parse_name($full_name = "") {
    $first_name = $middle_name = $last_name = "";

    if (!empty($full_name)) {
        $full_name = preg_replace("!\s+!", " ", $full_name);

        $name_array = explode(" ", $full_name);

        $first_name = $name_array[0];

        for ($i = 1; $i < (count($name_array) - 1); $i++) {
            $middle_name .= " " . $name_array[$i];
        }

        $middle_name = trim($middle_name);
        $last_name = (count($name_array) != 1 && isset($name_array[count($name_array) - 1])) ? $name_array[count($name_array) - 1] : "";
    }
    return array("first_name" => $first_name, "middle_name" => $middle_name, "last_name" => $last_name);
}

function common_send_email($to_email, $subject, $message, $bcc_email = "", $cc_email = "", $from_email = "", $reply_to = "", $attchement_path = "", $fileName = "", $file_move = "") {
    // return 1;
    $status = 0;
    $error = "";
    $active_id = 1;

    // if (date("d") > 10 && date("d") <= 21) {
    // $active_id = 5;
    // }

    // if ($_SESSION['isUserSession']['user_id'] == 91) {
    //     $active_id = 7;
    // }

    if (empty($to_email) || empty($subject) || empty($message)) {
        $error = "Please check email id, subject and message when sent email";
    } else {



        if (empty($from_email)) {
            $from_email = "no-reply@b4salary.co.in";
        }


        
        if ($active_id == 0) {
            $ci =& get_instance();

            $config = array();
            $config['protocol'] = "smtp";
            $config['smtp_host'] = "smtp.mailgun.org";
            $config['smtp_user'] = "postmaster@salaryontime.com";
            $config['smtp_pass'] = "7d8a311a3141e4cb5f050cd73ea58e9a-2175ccc2-4ddf8189";
            $config['smtp_port'] = 587;
            $config['newline'] = "\r\n";
            $config['wordwrap'] = TRUE;

            $ci->load->library('email', $config);

            $ci->email->initialize($config);
            $config['mailtype'] = "html";
            $config['charset'] = "UTF-8";
            $config['priority'] = 1;

            $ci->email->set_newline("\r\n");

            $ci->email->from($from_email);

            if (!empty($bcc_email)) {
                $ci->email->bcc($bcc_email);
            }


            $list = array(INFO_EMAIL, TECH_EMAIL);

            $ci->email->cc($list);
            $ci->email->to($to_email);

            $ci->email->subject($subject);

            $ci->email->message($message);

            if ($ci->email->send()) {
                $status = 1;
            } else {
                $error = "Some error occurred";
            }
        } elseif ($active_id == 1) {

            if (!empty($from_email)) {
                $request_array["from"] = ["address" => $from_email];
            }
            
            $request_array["to"] = [
                [
                    "email_address" => [
                        "address" => $to_email,
                        "name" => "",
                    ]
                ]
            ];

            if (!empty($bcc_email)) {
                $request_array["bcc"] = [
                    [
                        "email_address" => [
                            "address" => $bcc_email,
                            "name" => "",
                        ]
                    ]
                ];
            }

            if (!empty($cc_email)) {
                $request_array["cc"] = [
                    [
                        "email_address" => [
                            "address" => $cc_email,
                            "name" => "",
                        ]
                    ]
                ];
            }

            if (!empty($reply_to)) {
                // $request_array["h:Reply-To"] = $reply_to; // reply to now exist in the API
            }

            if (!empty($attachments) && isset($attachments)) {
                $i = 0;
                $request_array['attachments'] = [];

                foreach($attachments as $row) {
                    $request_array['attachments'][$i]['name'] = $row['file_name'] ."_". rand(999999, 111111) . date('YmdHis').".pdf";
                    $request_array['attachments'][$i]['content'] = base64_encode(file_get_contents($row['file_path']));
                    $request_array['attachments'][$i]['mime_type'] = "application/pdf";
                    $i++;
                }
            } else {
                if (!empty($attchement_path) && !empty($attachement_name)) {
                    $request_array['attachments'] = [[
                        "name" => rand(999999, 111111) . date('YmdHis').".pdf",
                        "content" => base64_encode(file_get_contents($attchement_path . $attachement_name)),
                        "mime_type" => "application/pdf"
                    ]];
                }
            }
            
            $request_array["subject"] = $subject;

            $request_array["htmlbody"] = $message;
            
            $apiUrl = "https://api.zeptomail.in/v1.1/email";

            $apiHeaders = array(
                'Accept:  application/json',
                'Authorization: Zoho-enczapikey PHtE6r1fSu2/32YppxAA7PCxF8TwMIot9b82eFIV4oxKCaUKSk0GrYoqlWK+rkp5XPMUFqKbzI5u5LPPt+rRLD7uM2lOX2qyqK3sx/VYSPOZsbq6x00csVgZcUTbU4bpe9Rt0STQud3fNA==',
                'cache-control:  no-cache',
                'Content-Type:  application/json'
            );

            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_array));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            $response = curl_exec($curl);

            $return_array = json_decode($response, true);

            if ($return_array['message'] == "OK") {
                $status = 1;
            } else {
                $error = $return_array['message'];
            }
        } else if ($active_id == 2) {

            if (empty($from_email)) {
                $from_email = INFO_EMAIL;
            }

            $apiUrl = "https://api.mailgun.net/v3/b4salary.co.in/messages";

            $request_array = array('from' => $from_email, 'to' => $to_email, 'text' => $subject, 'subject' => $message);


            if (!empty($bcc_email)) {
                $request_array["bcc"] = $bcc_email;
            }

            if (!empty($cc_email)) {
                $request_array["cc"] = $cc_email;
            }

            if (!empty($reply_to)) {
                $request_array["h:Reply-To"] = $reply_to;
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
                CURLOPT_POSTFIELDS => array('from' => $from_email, 'to' => $to_email, 'text' => $message, 'subject' => $subject),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic '. base64_encode("api:e2c36f932c826a311ca6b3505bf08afb-f6d80573-02e88f67")
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $return_array = json_decode($response, true);

            if ($return_array['message'] == "Queued. Thank you.") {
                $status = 1;
            } else {
                $error = $return_array['message'];
            }
        } else if ($active_id == 3) {

            $apiUrl = ""; // https://api.sendgrid.com/v3/mail/send

            $apiHeaders = array(
                "Authorization: Bearer ",
                "Accept: application/json",
                "Content-Type: text/html",
            );

            $apiRequestArray = [];

            $send_email_array = [];

            $send_email_array["to"] = [["email" => $to_email]];

            if (!empty($cc_email)) {

                $cc_email = explode(",", $cc_email);

                $sent_cc_email = [];
                foreach ($cc_email as $email_data) {
                    $sent_cc_email[] = ["email" => trim($email_data)];
                }

                $send_email_array["cc"] = $sent_cc_email;
            }

            if (!empty($bcc_email)) {

                $bcc_email = explode(",", $bcc_email);

                $sent_bcc_email = [];
                foreach ($bcc_email as $email_data) {
                    $sent_bcc_email[] = ["email" => trim($email_data)];
                }

                $send_email_array["bcc"] = $sent_bcc_email;
            }

            $apiRequestArray["personalizations"] = [$send_email_array];

            $apiRequestArray["from"] = ["email" => $from_email, "name" => "Salaryontime.com"];

            $apiRequestArray["reply_to"] = array("email" => $reply_to);

            $apiRequestArray["subject"] = $subject;

            $apiRequestArray["content"] = [[
                "type" => "text/html",
                "value" => "$message"
            ]];

            if (!empty($attchement_path) && !empty($attachement_name)) {
                $apiRequestArray['attachments'] = [
                    [
                        "content" => base64_encode(file_get_contents($attchement_path . $attachement_name)),
                        "type" => "application/pdf",
                        "filename" => "sanction_letter.pdf",
                        "disposition" => "attachment"
                    ]
                ];
            }

            $apiResponseJson = json_encode($apiRequestArray);
            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $apiResponseJson);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($curl);

            if (empty($response)) {
                $status = 1;
            } else {
                $return_array = json_decode($response, true);
                $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
            }
        } else if ($active_id == 4) {

            // $config = array();
            // $config['protocol'] = "smtp";
            // $config['smtp_host'] = "smtp.sendgrid.net";
            // $config['smtp_user'] = "apikey";
            // $config['smtp_pass'] = "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";
            // $config['smtp_port'] = 25;
            // $config['mailtype'] = "html";
            // $config['charset'] = "UTF-8";
            // $config['priority'] = 1;
            // $config['newline'] = "\r\n";
            // $config['wordwrap'] = TRUE;


            $config = array();
            $config['protocol'] = "smtp";
            $config['smtp_host'] = "smtp.mailgun.org";
            $config['smtp_user'] = "postmaster@salaryontime.com";
            $config['smtp_pass'] = "7d8a311a3141e4cb5f050cd73ea58e9a-2175ccc2-4ddf8189";
            $config['smtp_port'] = 587;
            $config['mailtype'] = "html";
            $config['charset'] = "UTF-8";
            $config['priority'] = 1;
            $config['newline'] = "\r\n";
            $config['wordwrap'] = TRUE;
            $ci->load->library('email', $config);

            $ci->email->initialize($config);

            $ci->email->set_newline("\r\n");

            $ci->email->from($from_email);

            if (!empty($bcc_email)) {
                $ci->email->bcc($bcc_email);
            }
            if (!empty($cc_email)) {
                $ci->email->cc($cc_email);
            }

            $ci->email->to($to_email);

            $ci->email->subject($subject);

            $ci->email->message($message);

            if ($ci->email->send()) {
                $status = 1;
            } else {
                $error = "Some error occurred";
            }
        } else if ($active_id == 5) {
            $curl = curl_init();
            if (strpos($message, 'DOCTYPE html') !== false) {
                $from_email = empty($from_email) ? INFO_EMAIL : $from_email;
                if (!empty($attchement_path)) {
                    $apiRequestArray = array(
                        'from' => $from_email,
                        'to' => $to_email,
                        'html' => $message,
                        'subject' => $subject,
                        'attachment' => new CURLFile($attchement_path),
                        'cc' => $cc_email
                    );
                } else {
                    $apiRequestArray = array(
                        'from' => $from_email,
                        'to' => $to_email,
                        'html' => $message,
                        'subject' => $subject,
                        'cc' => $cc_email
                    );
                }
            } else {
                if (!empty($attchement_path)) {
                    $apiRequestArray = array(
                        'from' => $from_email,
                        'to' => $to_email,
                        'text' => $message,
                        'subject' => $subject,
                        'attachment' => new CURLFile($attchement_path),
                        'cc' => $cc_email
                    );
                } else {
                    $apiRequestArray = array(
                        'from' => $from_email,
                        'to' => $to_email,
                        'text' => $message,
                        'subject' => $subject,
                        'cc' => $cc_email
                    );
                }
            }
            $url = "";

            if (strpos(strtolower($to_email), 'salaryontime.com') !== false) {
                $url = 'https://api.mailgun.net/v3/salaryontime.in/messages';
            } else if (strpos(strtolower($cc_email), 'salaryontime.com') !== false) {

                $url = 'https://api.mailgun.net/v3/salaryontime.in/messages';
            } else {
                $url = 'https://api.mailgun.net/v3/salaryontime.com/messages';
            }
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $apiRequestArray,
                CURLOPT_HTTPHEADER => array(
                    // 'Authorization: Basic YXBpOmFjMjg1MjIzOTU0ZmU4MmVhYTVjNGYwNDg1NzJjY2RhLTE5ODA2ZDE0LTExMWFlYjBi',
                    'Authorization: Basic YXBpOjk3MzNiMDk2NjA4YzUyNjQ0YzUyYWZjMjY3YmYyNjU5LTVkY2I1ZTM2LWI3NzIyMjkx',
                    'Content-Type = multipart/form-data'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            if (!empty($response)) {
                $status = 1;
                $return_array = array("status" => $status, "error" => $error);
            } else {
                $return_array = json_decode($response, true);
                $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
            }
        } else if ($active_id == 6) {

            $apiUrl = "https://api.sendgrid.com/v3/mail/send";

            $apiHeaders = array(
                "Authorization: Bearer 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",
                "Accept: application/json",
                "Content-Type: application/json",
            );

            $apiRequestArray = [];

            $send_email_array = [];

            // Add recipient email
            $send_email_array["to"] = [["email" => $to_email]];

            // Add CC emails
            if (!empty($cc_email)) {
                $cc_email = explode(",", $cc_email);

                $sent_cc_email = [];
                foreach ($cc_email as $email_data) {
                    if (trim(strtolower($to_email)) == trim(strtolower($email_data))) {
                        continue;
                    }
                    $sent_cc_email[] = ["email" => trim($email_data)];
                }

                if (!empty($sent_cc_email)) {
                    $send_email_array["cc"] = $sent_cc_email;
                }
            }

            // Add BCC emails
            if (!empty($bcc_email)) {
                $bcc_email = explode(",", $bcc_email);

                $sent_bcc_email = [];
                foreach ($bcc_email as $email_data) {
                    if (trim(strtolower($to_email)) == trim(strtolower($email_data))) {
                        continue;
                    }
                    $sent_bcc_email[] = ["email" => trim($email_data)];
                }

                if (!empty($sent_bcc_email)) {
                    $send_email_array["bcc"] = $sent_bcc_email;
                }
            }

            $apiRequestArray["personalizations"] = [$send_email_array];

            // Add sender details
            $apiRequestArray["from"] = ["email" => $from_email, "name" => "SalaryOnTime"];

            // Add reply-to email
            if (!empty($reply_to)) {
                $apiRequestArray["reply_to"] = ["email" => $reply_to];
            }

            // Add email subject
            $apiRequestArray["subject"] = $subject;

            // Add email content
            $apiRequestArray["content"] = [[
                "type" => "text/html",
                "value" => "$message"
            ]];

            // Add attachment if provided
            if (!empty($attchement_path)) {

                // Add attachment if provided
                $attachment_file = @file_get_contents($attchement_path);

                if ($attachment_file !== false) {
                    $attachment_content = base64_encode($attachment_file);

                    // Use a default MIME type
                    $attachment_type = "application/octet-stream";

                    // Extract filename from the S3 URL
                    $attachment_filename = basename(parse_url($attchement_path, PHP_URL_PATH));

                    $apiRequestArray['attachments'] = [
                        [
                            "content" => $attachment_content,
                            "type" => $attachment_type,
                            "filename" => empty($fileName) ? $attachment_filename : $fileName,
                            "disposition" => "attachment"
                        ]
                    ];
                } else {
                    echo "Failed to download the file from the S3 URL.";
                    exit;
                }
            }

            // Encode the API request payload
            $apiResponseJson = json_encode($apiRequestArray);
            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

            // Initialize cURL
            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $apiResponseJson);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            // Execute the cURL request and handle the response
            $response = curl_exec($curl);

            if (empty($response)) {
                $status = 1;
                $return_array = array("status" => $status, "error" => $error);
                return $return_array;
            } else {
                $return_array = json_decode($response, true);
                $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
            }
            curl_close($curl);
        } else if ($active_id == 7) {

            // AWS SES credentials
            // require_once(COMP_PATH . '/includes/integration/integration_config.php');
            // $apiConfig = integration_config("AWS", "SES_API");

            $sesAccess = 'AKIAXEVXYIVMV3NUH56D';
            $sesSecret = 'BJh9VeDKTpHxY14Bga3YMHTZBTKRupq1taFkxxbIBfuS';
            // $sesAccess = 'AKIAXEVXYIVMXNBXKQP6';
            // $sesSecret = 'BF+/DuqjrzWbHlGMLNbgpUp9r6hzNP8JYnpHaG3IQypj';

            $sesSMTP = "email-smtp.ap-south-1.amazonaws.com";
            $sesPort = 465;

            // Check AWS SES credentials
            if (empty($sesAccess) || empty($sesSecret)) {
                return ["status" => 0, "error" => "AWS SES credentials missing."];
            }

            // Ensure recipient emails are in the correct format
            $to_email = is_array($to_email) ? implode(", ", $to_email) : $to_email;
            $cc_email = !empty($cc_email) ? (is_array($cc_email) ? implode(", ", $cc_email) : $cc_email) : "";
            $bcc_email = !empty($bcc_email) ? (is_array($bcc_email) ? implode(", ", $bcc_email) : $bcc_email) : "";

            // Generate a unique boundary for email parts
            $boundary = md5(time());

            // Construct headers
            $headers = [
                "MIME-Version: 1.0",
                "From: SalaryOnTime <$from_email>",
                "To: $to_email",
                "Subject: $subject",
                "Content-Type: multipart/mixed; boundary=\"$boundary\""
            ];

            if (!empty($cc_email)) $headers[] = "Cc: $cc_email";
            if (!empty($bcc_email)) $headers[] = "Bcc: $bcc_email";
            if (!empty($replyToAddress)) $headers[] = "Reply-To: $replyToAddress";

            // Build email body
            $body = "--$boundary\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= "$message\r\n\r\n";

            // Process attachments (Downloading from URLs)
            if (!empty($attchement_path)) {
                $fileContent = file_get_contents($attchement_path);
                if ($fileContent !== false) {
                    $fileData = base64_encode($fileContent);
                    $mimeType = mime_content_type($attchement_path) ?: "application/octet-stream";

                    // Extract filename from the S3 URL
                    $attachment_filename = basename(parse_url($attchement_path, PHP_URL_PATH));
                    $ext = pathinfo($attachment_filename, PATHINFO_EXTENSION);
                    $fileName = empty($fileName) ? 'attachment.' . $ext : $fileName;

                    $body .= "--$boundary\r\n";
                    $body .= "Content-Type: $mimeType; name=\"$fileName\"\r\n";
                    $body .= "Content-Transfer-Encoding: base64\r\n";
                    $body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n\r\n";
                    $body .= chunk_split($fileData) . "\r\n";
                } else {
                    return ["status" => 0, "error" => "Error: Could not download attachment from $attchement_path"];
                }
            }

            $body .= "--$boundary--";

            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "smtps://$sesSMTP:$sesPort");
            curl_setopt($ch, CURLOPT_USERNAME, $sesAccess);
            curl_setopt($ch, CURLOPT_PASSWORD, $sesSecret);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
            curl_setopt($ch, CURLOPT_MAIL_FROM, $from_email);

            // Set recipients
            $recipients = array_filter([$to_email, $cc_email, $bcc_email]);
            curl_setopt($ch, CURLOPT_MAIL_RCPT, $recipients);

            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // Attach the email data
            $fp = fopen('php://temp', 'w+');
            fwrite($fp, implode("\r\n", $headers) . "\r\n\r\n" . $body);
            rewind($fp);
            curl_setopt($ch, CURLOPT_INFILE, $fp);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($body));

            //debug
            // curl_setopt($ch, CURLOPT_VERBOSE, true);
            // curl_setopt($ch, CURLOPT_STDERR, fopen('php://output', 'w'));

            // Execute the cURL request
            $response = curl_exec($ch);
            $error = curl_errno($ch) ? "cURL Error: " . curl_error($ch) : "";

            curl_close($ch);
            fclose($fp);

            if (!empty($error)) {
                return ["status" => 0, "error" => $error];
            }

            // Insert email log
            $insert_log_array = array();
            $insert_log_array['email_provider'] = 'SES';
            $insert_log_array['email_type_id'] = 1;
            $insert_log_array['email_address'] = $to_email;
            $insert_log_array['email_content'] = addslashes($subject);
            $insert_log_array['email_api_status_id'] = 1;
            $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

            require_once(COMP_PATH . "/classes/model/BaseModel.class.php");
            require_once(COMP_PATH . "/classes/model/LeadModel.class.php");
            $leadModelObj = new LeadModel();
            $leadModelObj->insertTable("api_email_logs", $insert_log_array);

            return ["status" => 1, "message" => "Email sent successfully."];
        }
    }

    $return_array = array("status" => $status, "error" => $error);

    return $return_array;
}

function common_lead_thank_you_email($lead_id, $email, $name, $reference_no) {

    $return_array = array();

    if (empty($lead_id) || empty($email) || empty($name) || empty($reference_no)) {
        $return_array['Status'] = 0;
        $return_array['Message'] = 'Lead id required.';
        return $return_array;
    } else {

        $subject = 'Thank You. - salaryontime';

        $html = '<!DOCTYPE html>
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>Thank You | Salary On Time</title>
                </head>

                <body>
                    <table width="400" border="0" align="center"
                        style="font-family:Arial, Helvetica, sans-serif; border:solid 1px #ddd; padding:10px; background:#f9f9f9;">
                        <tr>
                            <td width="775" align="center" style="background: #225395;"><img
                                    src="https://salaryontime.com/static/media/logo.64e094820f6a4233a384.png" width="40%"></td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                <table width="418" border="0" style="text-align:center; padding:20px; background:#fff;">
                                    <tr>
                                        <td style="font-size:16px;"><img src="https://salaryontime.in/public/emailimages/thank-you.gif"
                                                width="auto" height="250" alt="thank-you"></td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:16px;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td width="412" style="font-size:16px;">
                                            <h2 style="margin:0px; color:#116a97;">Thank You</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="412" style="font-size:16px;">
                                            <h2 style="margin:0px; color:#116a97;">Dear ' . $name . '</h2>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <p style="line-height:25px; margin:0px;">Thank you for showing interest in Salaryontime.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p style="line-height:25px; margin:0px;">We have received your loan application <strong
                                                    style="color:#116a97;">' . $reference_no . '</strong> successfully. Please note the
                                                same for future communication.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="center">Follow Us On</td>
                        </tr>
                        <td align="center">
                            <!-- Facebook Icon -->
                            <a href="https://www.facebook.com/salaryontime" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="https://salaryontime.in/public/emailimages/salaryontime-facebook.png" class="socil-t"
                                    alt="salaryontime-instagram" style="width:30px;">
                            </a>
                            <!-- LinkedIn Icon -->
                            <a href="https://www.linkedin.com/company/103731294/admin/" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="https://salaryontime.in/public/emailimages/salaryontime-linkedin.png" class="socil-t"
                                    alt="salaryontime-instagram" style="width:30px;">
                            </a>
                            <!-- Instagram Icon -->
                            <a href="https://www.instagram.com/salaryontime/" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="https://salaryontime.in/public/emailimages/salaryontime-instagram.png" class="socil-t"
                                    alt="salaryontime-instagram" style="width:30px;">
                            </a>
                            <!-- YouTube Icon -->
                            <a href="https://www.youtube.com/@salaryontime" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="https://salaryontime.in/public/emailimages/salaryontime-youtube.png" class="socil-t"
                                    alt="salaryontime-instagram" style="width:30px;">
                            </a>
                        </td>
                        </tr>
                        <tr>
                            <td align="center">For Latest Updates &amp; Offers</td>
                        </tr>
                    </table>
                </body>

                </html>';

        $email_status = common_send_email($email, $subject, $html);

        if ($email_status) {
            $return_array['email_status'] = $email_status;
            $return_array['Status'] = 1;
            $return_array['Message'] = 'Email sent successfully.';
        }

        return $return_array;
    }
}

if (!function_exists('uploadDocument')) {
    function uploadDocument() { //$file_obj, $lead_id = 0, $flag = 0, $ext = ''
        require_once(COMP_PATH . '/s3_bucket/S3_library.php');

        $s3_upload = new S3_upload();

        if ($flag == 1) {
            $extension = $ext;
        } else if ($flag == 2) {
            $extension = $ext;
        } else {
            $file_name = $file_obj["file_name"]['name'];
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
        }

        $new_name = $lead_id . '_lms_' . date('YmdHis') . rand(111, 999) . '.' . $extension;

        if ($flag == 1) {
            $upload = $s3_conn->upload_file($file_obj, $new_name, $flag);
        } else if ($flag == 2) {
            $upload = $s3_conn->upload_file($file_obj, $new_name);
        } else {
            $upload = $s3_conn->upload_file($file_obj["file_name"]["tmp_name"], $new_name);
        }

        $return_status = 0;

        if ($upload) {
            $return_status = 1;
        }

        $return_array = ["status" => $return_status, "file_name" => $new_name];
        return $return_array;
    }
}


if (!function_exists('downloadDocument')) {

    function downloadDocument($file_name, $flag = 0) {
        $ci = &get_instance();

        $ci->load->library(array('S3_upload'));

        $upload = $s3_conn->get_file($file_name, $flag);
        return $upload;
    }
}
