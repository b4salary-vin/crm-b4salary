<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronReportController extends CI_Controller {

    var $cron_notification_email =CTO_EMAIL;

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronSanction_Model', 'SanctionModel');
    }

    public function credit_head_approval_report() {
        $report_name = 'Credit Head Approval Report';
        $date = date('Y-m-d', strtotime('-1 day')); // Yesterday's date

        // Optimized SQL query using DATE_FORMAT
        $sql = "SELECT LD.lead_id, U.name,
                       DATE_FORMAT(LD.lead_credithead_assign_datetime, '%H%i') AS assign_time
                FROM leads LD
                INNER JOIN users U ON LD.lead_credithead_assign_user_id = U.user_id
                WHERE LD.lead_credithead_assign_user_id > 0
                AND U.user_id != 122 AND LD.user_type='NEW'
                AND DATE(LD.lead_credithead_assign_datetime) = '$date'";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Initializing the report array
        $report_array = [];
        $grand_total_10_8_PM = 0;
        $grand_total_08_12_AM = 0;

        foreach ($result as $row) {
            $name = $row['name'];
            $assign_time = (int)$row['assign_time']; // Convert to integer for range comparison

            if ($assign_time >= 1000 && $assign_time < 2000) {
                $report_array[$name]['10-08 PM'] = isset($report_array[$name]['10-08 PM']) ? $report_array[$name]['10-08 PM'] + 1 : 1;
                $report_array[$name]['08-12 AM'] = isset($report_array[$name]['08-12 AM']) ? $report_array[$name]['08-12 AM'] : 0;
            } elseif ($assign_time >= 2000 || $assign_time < 1000) {
                $report_array[$name]['08-12 AM'] = isset($report_array[$name]['08-12 AM']) ? $report_array[$name]['08-12 AM'] + 1 : 1;
                $report_array[$name]['10-08 PM'] = isset($report_array[$name]['10-08 PM']) ? $report_array[$name]['10-08 PM'] : 0;
            }
        }

        // Generate HTML report
        $html = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Credit Head Approved Report</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 20px;
                            background-color: #f4f4f4;
                            justify-content: center;
                        }

                        .report-container {
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                            border-top: 6px solid #007BFF;
                            border-bottom: 6px solid #28A745;
                            max-width: 100%;
                            overflow-x: auto;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                            min-width: 600px;
                            border: 1px solid #ddd; /* Thin gray border for table */
                        }

                        th {
                            background: linear-gradient(135deg, #007BFF, #6610F2);
                            color: white;
                            font-weight: bold;
                            text-transform: uppercase;
                            padding: 12px;
                            border-bottom: 1px solid #aaa; /* Thin border under header */
                        }

                        td {
                            padding: 10px;
                            border: 1px solid #ddd; /* Thin gray border for cells */
                            text-align: center;
                        }

                        .total-row {
                            background: linear-gradient(135deg, #28A745, #20C997);
                            color: white;
                            font-weight: bold;
                            border-top: 1px solid #3E8E41; /* Thin border for total row */
                        }

                        /* Hover effect for better UX */
                        tr:hover {
                            background-color: #f1f1f1;
                        }

                        /* Responsive Table */
                        @media (max-width: 768px) {
                            body {
                                padding: 10px;
                            }

                            .report-container {
                                padding: 15px;
                            }

                            table {
                                min-width: 100%;
                                font-size: 14px;
                            }

                            th, td {
                                padding: 8px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <table>
                            <thead>
                                <tr>
                                    <th colspan="4">' . $report_name . ' | ' . date('d-M-Y', strtotime($date)) . '</th>
                                </tr>
                                <tr>
                                    <th rowspan="2">User Name</th>
                                    <th colspan="2">Time Slots</th>
                                    <th rowspan="2">Total</th>
                                </tr>
                                <tr>
                                    <th>10-08 PM</th>
                                    <th>08-12 AM</th>
                                </tr>
                            </thead>
                            <tbody>';

        // Populate table rows with report data
        foreach ($report_array as $user => $data) {
            $total = ($data['10-08 PM'] ?? 0) + ($data['08-12 AM'] ?? 0);
            $grand_total_10_8_PM += ($data['10-08 PM'] ?? 0);
            $grand_total_08_12_AM += ($data['08-12 AM'] ?? 0);

            $html .= '<tr>
                        <td>' . htmlspecialchars($user) . '</td>
                        <td>' . ($data['10-08 PM'] ?? 0) . '</td>
                        <td>' . ($data['08-12 AM'] ?? 0) . '</td>
                        <td>' . $total . '</td>
                      </tr>';
        }

        // Grand Total Row
        $grand_total = $grand_total_10_8_PM + $grand_total_08_12_AM;
        $html .= '<tr class="total-row">
                    <td><strong>Grand&nbsp;Total</strong></td>
                    <td><strong>' . $grand_total_10_8_PM . '</strong></td>
                    <td><strong>' . $grand_total_08_12_AM . '</strong></td>
                    <td><strong>' . $grand_total . '</strong></td>
                </tr>
                </tbody>
            </table>
            </div>
        </body>
        </html>';

        // Save the HTML to a temporary file
        $html_file = '/tmp/report.html';
        file_put_contents($html_file, $html);

        // Use wkhtmltoimage to generate the PNG
        $png_file = 'report.png'; // Replace with actual path
        exec("wkhtmltoimage $html_file $png_file");

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();
        if (file_exists($png_file)) {
            $request_array = array();
            $request_array['flag'] = 1;
            $request_array['file'] = base64_encode(file_get_contents($png_file));
            $request_array['ext'] = pathinfo($png_file, PATHINFO_EXTENSION);
            $request_array['bucket_name'] = 'sl-website';
            $request_array['folder_name'] = 'reports';
            $request_array['new_file_name'] = 'credit';

            $upload_return = $CommonComponent->upload_document($lead_id, $request_array);

            if ($upload_return['status'] == 1) {
                $return_array['status'] = 1;
                $file_name = $upload_return['file_name'];
                unlink($png_file);
            }
        }

        $mobile = array("9625891341", "9289767308", "9161674682");
        $link = LMS_URL."CronJobs/CronReportController/direct_repport_view?file_name=" . $file_name;
        $request_array = [$report_name, date('d-M-Y', strtotime($date))];

        foreach ($mobile as $key => $value) {
            $result = $this->middlewareWhatsAppReport($value, $link, $report_name, $request_array);
        }

        // Send email notification
        $subject = 'Credit Head Approved Report | ' . date('d-M-Y', strtotime($date)) . " | " . implode(", ", $mobile);
        $this->middlewareEmail($this->cron_notification_email, $subject, $html);
        // echo $html;
        echo 'OK';
    }

    public function credit_head_approval_hour_report() {
        $report_name = 'Credit Head Approval Hourly Report';
        $date = date('Y-m-d', strtotime('-1 day')); // Yesterday's date

        // Fetch the required data
        $sql = "SELECT LD.lead_id, U.name,
                       DATE_FORMAT(LD.lead_credithead_assign_datetime, '%H%i') AS assign_time
                FROM leads LD
                INNER JOIN users U ON LD.lead_credithead_assign_user_id = U.user_id
                WHERE LD.lead_credithead_assign_user_id > 0
                AND U.user_id != 122 AND LD.user_type='NEW'
                AND DATE(LD.lead_credithead_assign_datetime) = '$date'";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Define time slots dynamically
        $time_slots = [
            '<10 AM',
            '10 AM - 11 AM',
            '11 AM - 12 PM',
            '12 PM - 01 PM',
            '01 PM - 02 PM',
            '02 PM - 03 PM',
            '03 PM - 04 PM',
            '04 PM - 05 PM',
            '05 PM - 06 PM',
            '06 PM - 07 PM',
            '07 PM - 08 PM',
            '08 PM - 09 PM',
            '09 PM - 10 PM',
            '10 PM - 11 PM',
            '11 PM - 12 AM'
        ];

        // Fetch unique user names dynamically
        $users = array_unique(array_column($result, 'name'));
        sort($users); // Sort users alphabetically

        // Initialize report array with time slots and user columns
        $report_array = [];
        foreach ($time_slots as $slot) {
            $report_array[$slot] = array_fill_keys($users, 0);
        }

        // Process data into the report array
        foreach ($result as $row) {
            $name = $row['name'];
            $assign_time = (int)$row['assign_time'];

            if ($assign_time < 1000) {
                $report_array['<10 AM'][$name]++;
            } elseif ($assign_time < 1100) {
                $report_array['10 AM - 11 AM'][$name]++;
            } elseif ($assign_time < 1200) {
                $report_array['11 AM - 12 PM'][$name]++;
            } elseif ($assign_time < 1300) {
                $report_array['12 PM - 01 PM'][$name]++;
            } elseif ($assign_time < 1400) {
                $report_array['01 PM - 02 PM'][$name]++;
            } elseif ($assign_time < 1500) {
                $report_array['02 PM - 03 PM'][$name]++;
            } elseif ($assign_time < 1600) {
                $report_array['03 PM - 04 PM'][$name]++;
            } elseif ($assign_time < 1700) {
                $report_array['04 PM - 05 PM'][$name]++;
            } elseif ($assign_time < 1800) {
                $report_array['05 PM - 06 PM'][$name]++;
            } elseif ($assign_time < 1900) {
                $report_array['06 PM - 07 PM'][$name]++;
            } elseif ($assign_time < 2000) {
                $report_array['07 PM - 08 PM'][$name]++;
            } elseif ($assign_time < 2100) {
                $report_array['08 PM - 09 PM'][$name]++;
            } elseif ($assign_time < 2200) {
                $report_array['09 PM - 10 PM'][$name]++;
            } elseif ($assign_time < 2300) {
                $report_array['10 PM - 11 PM'][$name]++;
            } else {
                $report_array['11 PM - 12 AM'][$name]++;
            }
        }

        // Generate HTML report dynamically
        $html = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Credit Head Approved Report</title>
                     <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 20px;
                            background-color: #f4f4f4;
                            justify-content: center;
                        }

                        .report-container {
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                            border-top: 6px solid #007BFF;
                            border-bottom: 6px solid #28A745;
                            max-width: 100%;
                            overflow-x: auto;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                            min-width: 600px;
                            border: 1px solid #ddd; /* Thin gray border for table */
                        }

                        th {
                            background: linear-gradient(135deg, #007BFF, #6610F2);
                            color: white;
                            font-weight: bold;
                            text-transform: uppercase;
                            padding: 12px;
                            border-bottom: 1px solid #aaa; /* Thin border under header */
                        }

                        td {
                            padding: 10px;
                            border: 1px solid #ddd; /* Thin gray border for cells */
                            text-align: center;
                        }

                        .total-row {
                            background: linear-gradient(135deg, #28A745, #20C997);
                            color: white;
                            font-weight: bold;
                            border-top: 1px solid #3E8E41; /* Thin border for total row */
                        }

                        /* Hover effect for better UX */
                        tr:hover {
                            background-color: #f1f1f1;
                        }

                        /* Responsive Table */
                        @media (max-width: 768px) {
                            body {
                                padding: 10px;
                            }

                            .report-container {
                                padding: 15px;
                            }

                            table {
                                min-width: 100%;
                                font-size: 14px;
                            }

                            th, td {
                                padding: 8px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <table>
                            <thead>
                            <tr>
                                    <th colspan="' . count($report_array) . '">' . $report_name . ' | ' . date('d-M-Y', strtotime($date)) . '</th>
                                </tr>
                                <tr>
                                    <th>Time Slots</th>';

        // Create user name headers dynamically
        foreach ($users as $user) {
            $html .= "<th>" . htmlspecialchars($user) . "</th>";
        }
        $html .= '<th>Total</th></tr></thead><tbody>';

        // Initialize grand total array
        $grand_total = array_fill_keys($users, 0);
        $overall_total = 0;

        // Populate table rows dynamically
        foreach ($report_array as $slot => $data) {
            $html .= "<tr><td>$slot</td>";
            $row_total = 0;

            foreach ($users as $user) {
                $count = $data[$user] ?? 0;
                $html .= "<td>$count</td>";
                $row_total += $count;
                $grand_total[$user] += $count; // Accumulate totals per user
            }

            $overall_total += $row_total;
            $html .= "<td><strong>$row_total</strong></td></tr>";
        }

        // Grand total row
        $html .= '<tr class="total-row"><td><strong>Grand Total</strong></td>';
        foreach ($users as $user) {
            $html .= "<td><strong>{$grand_total[$user]}</strong></td>";
        }
        $html .= "<td><strong>$overall_total</strong></td></tr>";

        $html .= '</tbody></table></div></body></html>';

        // Save HTML to a temporary file
        $html_file = '/tmp/report.html';
        file_put_contents($html_file, $html);

        // Convert HTML to PNG using wkhtmltoimage
        $png_file = 'report.png';
        exec("wkhtmltoimage $html_file $png_file");

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        if (file_exists($png_file)) {
            $request_array = [
                'flag' => 1,
                'file' => base64_encode(file_get_contents($png_file)),
                'ext' => pathinfo($png_file, PATHINFO_EXTENSION),
                'bucket_name' => 'sl-website',
                'folder_name' => 'reports',
                'new_file_name' => 'credit'
            ];

            $upload_return = $CommonComponent->upload_document(null, $request_array);
            if ($upload_return['status'] == 1) {
                unlink($png_file);
            }
        }

        $mobile = array("9625891341", "9289767308", "9161674682");
        $link = LMS_URL."CronJobs/CronReportController/direct_repport_view?file_name=" . ($upload_return['file_name'] ?? '');
        $request_array = [$report_name, date('d-M-Y', strtotime($date))];

        foreach ($mobile as $number) {
            $this->middlewareWhatsAppReport($number, $link, $report_name, $request_array);
        }

        $subject = "Credit Head Approved Report | " . date('d-M-Y', strtotime($date)) . " | " . implode(", ", $mobile);
        $this->middlewareEmail($this->cron_notification_email, $subject, $html);

        echo 'OK';
    }

    public function disbursal_head_approval_hour_report() {
        $report_name = 'Disbursal Hourly Report';
        $date = date('Y-m-d', strtotime('-1 day')); // Yesterday's date

        // Fetch the required data
        $sql = "SELECT
                    LD.lead_id,
                    U.name,
                    DATE_FORMAT(LD.lead_disbursal_assign_datetime, '%H%i') AS assign_time,
                    LD.lead_status_id
                FROM
                    leads LD
                    INNER JOIN users U ON LD.lead_disbursal_assign_user_id = U.user_id
                WHERE
                    LD.lead_disbursal_assign_user_id > 0
                    AND DATE(LD.lead_disbursal_assign_datetime) = '$date'";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Define time slots
        $time_slots = [
            '<10 AM' => 1000,
            '10 AM - 11 AM' => 1100,
            '11 AM - 12 PM' => 1200,
            '12 PM - 01 PM' => 1300,
            '01 PM - 02 PM' => 1400,
            '02 PM - 03 PM' => 1500,
            '03 PM - 04 PM' => 1600,
            '04 PM - 05 PM' => 1700,
            '05 PM - 06 PM' => 1800,
            '06 PM - 07 PM' => 1900,
            '07 PM - 08 PM' => 2000,
            '08 PM - 09 PM' => 2100,
            '09 PM - 10 PM' => 2200,
            '10 PM - 11 PM' => 2300,
            '11 PM - 12 AM' => 2400
        ];

        // Fetch unique user names
        $users = array_unique(array_column($result, 'name'));
        sort($users); // Sort alphabetically

        // Initialize report array
        $report_array = [];
        foreach ($time_slots as $slot => $limit) {
            foreach ($users as $user) {
                $report_array[$slot][$user] = ['ASSIGNED' => 0, 'DISBURSED' => 0];
            }
        }

        // Process data into report array
        foreach ($result as $row) {
            $name = $row['name'];
            $assign_time = (int)$row['assign_time'];
            $lead_status = (int)$row['lead_status_id'];

            foreach ($time_slots as $slot => $limit) {
                if ($assign_time < $limit) {
                    $report_array[$slot][$name]['ASSIGNED']++;
                    if (in_array($lead_status, [14, 16, 17, 18, 19])) {
                        $report_array[$slot][$name]['DISBURSED']++;
                    }
                    break;
                }
            }
        }

        // Generate HTML report dynamically
        $html = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . $report_name . '</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                        .report-container { background: white; padding: 20px; border-radius: 8px;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border-top: 6px solid #007BFF; border-bottom: 6px solid #28A745; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
                        th { background: linear-gradient(135deg, #007BFF, #6610F2); color: white; font-weight: bold; }
                        .total-row { background: linear-gradient(135deg, #28A745, #20C997); color: white; font-weight: bold; }
                        tr:hover { background-color: #f1f1f1; }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <table>
                            <thead>
                                <tr><th colspan="' . (count($users) * 2 + 5) . '">' . $report_name . ' | ' . date('d-M-Y', strtotime($date)) . '</th></tr>
                                <tr>
                                    <th rowspan="2">Time Slots</th>';

        foreach ($users as $user) {
            $html .= "<th colspan='2'>" . htmlspecialchars($user) . "</th>";
        }

        $html .= '<th rowspan="2">Total Assigned</th><th rowspan="2">Total Disbursed</th></tr>
          <tr>'; // Removed the extra <th> here

        foreach ($users as $user) {
            $html .= "<th>ASSIGNED</th><th>DISBURSED</th>";
        }

        $html .= '</tr></thead><tbody>';

        // Initialize grand totals
        $grand_totals = array_fill_keys($users, ['ASSIGNED' => 0, 'DISBURSED' => 0]);
        $overall_assigned = 0;
        $overall_disbursed = 0;

        // Populate table rows dynamically
        foreach ($report_array as $slot => $data) {
            $html .= "<tr><td nowrap>$slot</td>";
            $row_assigned = 0;
            $row_disbursed = 0;

            foreach ($users as $user) {
                $assigned = isset($data[$user]['ASSIGNED']) ? $data[$user]['ASSIGNED'] : 0;
                $disbursed = isset($data[$user]['DISBURSED']) ? $data[$user]['DISBURSED'] : 0;

                $html .= "<td>$assigned</td><td>$disbursed</td>";
                $row_assigned += $assigned;
                $row_disbursed += $disbursed;
                $grand_totals[$user]['ASSIGNED'] += $assigned;
                $grand_totals[$user]['DISBURSED'] += $disbursed;
            }

            $overall_assigned += $row_assigned;
            $overall_disbursed += $row_disbursed;
            $html .= "<td><strong>$row_assigned</strong></td><td><strong>$row_disbursed</strong></td></tr>";
        }

        // Grand total row
        $html .= '<tr class="total-row"><td><strong>Grand Total</strong></td>';
        foreach ($users as $user) {
            $html .= "<td><strong>{$grand_totals[$user]['ASSIGNED']}</strong></td><td><strong>{$grand_totals[$user]['DISBURSED']}</strong></td>";
        }
        $html .= "<td><strong>$overall_assigned</strong></td><td><strong>$overall_disbursed</strong></td></tr>";

        $html .= '</tbody></table></div></body></html>';


        // Save HTML to a temporary file
        $html_file = '/tmp/report.html';
        file_put_contents($html_file, $html);

        // Convert HTML to PNG using wkhtmltoimage
        $png_file = 'report.png';
        exec("wkhtmltoimage --width 2000 --quality 80 $html_file $png_file");

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        if (file_exists($png_file)) {
            $request_array = [
                'flag' => 1,
                'file' => base64_encode(file_get_contents($png_file)),
                'ext' => pathinfo($png_file, PATHINFO_EXTENSION),
                'bucket_name' => 'sl-website',
                'folder_name' => 'reports',
                'new_file_name' => 'disbursal'
            ];

            $upload_return = $CommonComponent->upload_document(null, $request_array);
            if ($upload_return['status'] == 1) {
                unlink($png_file);
            }
        }

        $mobile = array("9625891341", "9289767308", "9161674682", "9897632657");
        $link = LMS_URL."CronJobs/CronReportController/direct_repport_view?file_name=" . ($upload_return['file_name'] ?? '');
        $request_array = [$report_name, date('d-M-Y', strtotime($date))];

        foreach ($mobile as $number) {
            $this->middlewareWhatsAppReport($number, $link, $report_name, $request_array);
        }

        $subject = "Credit Head Approved Report | " . date('d-M-Y', strtotime($date)) . " | " . implode(", ", $mobile);
        $this->middlewareEmail($this->cron_notification_email, $subject, $html);

        echo 'OK';
    }

    public function collection_approval_hour_report() {
        $report_name = 'Collection Hourly Report';
        $date = date('Y-m-d', strtotime('-1 day')); // Yesterday's date

        // Fetch the required data
        $sql = "SELECT
                    U.name,
                    C.lead_id,
                    C.received_amount,
                    C.payment_verification,
                    DATE_FORMAT (C.closure_payment_updated_on, '%H%i') AS assign_time
                FROM
                    collection C
                    INNER JOIN users U ON C.closure_user_id = U.user_id
                WHERE
                    C.closure_user_id > 0
                    AND DATE (C.closure_payment_updated_on) = '$date'";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Define time slots
        $time_slots = [
            '<10 AM' => 1000,
            '10 AM - 11 AM' => 1100,
            '11 AM - 12 PM' => 1200,
            '12 PM - 01 PM' => 1300,
            '01 PM - 02 PM' => 1400,
            '02 PM - 03 PM' => 1500,
            '03 PM - 04 PM' => 1600,
            '04 PM - 05 PM' => 1700,
            '05 PM - 06 PM' => 1800,
            '06 PM - 07 PM' => 1900,
            '07 PM - 08 PM' => 2000,
            '08 PM - 09 PM' => 2100,
            '09 PM - 10 PM' => 2200,
            '10 PM - 11 PM' => 2300,
            '11 PM - 12 AM' => 2400
        ];

        // Fetch unique user names
        $users = array_unique(array_column($result, 'name'));
        sort($users); // Sort alphabetically

        // Initialize report array
        $report_array = [];
        foreach ($time_slots as $slot => $limit) {
            foreach ($users as $user) {
                $report_array[$slot][$user] = ['COUNT' => 0, 'AMOUNT' => 0];
            }
        }

        // Process data into report array
        foreach ($result as $row) {
            $name = $row['name'];
            $assign_time = (int)$row['assign_time'];
            $received_amount = (int)$row['received_amount'];

            foreach ($time_slots as $slot => $limit) {
                if ($assign_time < $limit) {
                    $report_array[$slot][$name]['COUNT']++;
                    if ($row['payment_verification'] == 1) {
                        $report_array[$slot][$name]['AMOUNT'] += $received_amount;
                    }
                    break;
                }
            }
        }

        // Generate HTML report dynamically
        $html = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . $report_name . '</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                        .report-container { background: white; padding: 20px; border-radius: 8px;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border-top: 6px solid #007BFF; border-bottom: 6px solid #28A745; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
                        th { background: linear-gradient(135deg, #007BFF, #6610F2); color: white; font-weight: bold; }
                        .total-row { background: linear-gradient(135deg, #28A745, #20C997); color: white; font-weight: bold; }
                        tr:hover { background-color: #f1f1f1; }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <table>
                            <thead>
                                <tr><th colspan="' . (count($users) * 2 + 5) . '">' . $report_name . ' | ' . date('d-M-Y', strtotime($date)) . '</th></tr>
                                <tr>
                                    <th rowspan="2">Time Slots</th>';

        foreach ($users as $user) {
            $html .= "<th colspan='2'>" . htmlspecialchars($user) . "</th>";
        }

        $html .= '<th rowspan="2">Total<br/>Count</th><th rowspan="2">Total<br/>Collection</th></tr>
          <tr>'; // Removed the extra <th> here

        foreach ($users as $user) {
            $html .= "<th>COUNT</th><th>AMOUNT</th>";
        }

        $html .= '</tr></thead><tbody>';

        // Initialize grand totals
        $grand_totals = array_fill_keys($users, ['COUNT' => 0, 'AMOUNT' => 0]);
        $overall_assigned = 0;
        $overall_disbursed = 0;

        // Populate table rows dynamically
        foreach ($report_array as $slot => $data) {
            $html .= "<tr><td nowrap>$slot</td>";
            $row_assigned = 0;
            $row_disbursed = 0;

            foreach ($users as $user) {
                $assigned = isset($data[$user]['COUNT']) ? $data[$user]['COUNT'] : 0;
                $disbursed = isset($data[$user]['AMOUNT']) ? $data[$user]['AMOUNT'] : 0;

                $html .= "<td>$assigned</td><td>" . number_format($disbursed, 0) . "</td>";
                $row_assigned += $assigned;
                $row_disbursed += $disbursed;
                $grand_totals[$user]['COUNT'] += $assigned;
                $grand_totals[$user]['AMOUNT'] += $disbursed;
            }

            $overall_assigned += $row_assigned;
            $overall_disbursed += $row_disbursed;
            $html .= "<td><strong>$row_assigned</strong></td><td><strong>" . number_format($row_disbursed) . "</strong></td></tr>";
        }

        // Grand total row
        $html .= '<tr class="total-row"><td><strong>Grand Total</strong></td>';
        foreach ($users as $user) {
            $html .= "<td><strong>" . $grand_totals[$user]['COUNT'] . "</strong></td><td><strong>" . number_format($grand_totals[$user]['AMOUNT']) . "</strong></td>";
        }
        $html .= "<td><strong>$overall_assigned</strong></td><td><strong>" . number_format($overall_disbursed) . "</strong></td></tr>";

        $html .= '</tbody></table></div></body></html>';

        // Save HTML to a temporary file
        $html_file = '/tmp/report.html';
        file_put_contents($html_file, $html);

        // Convert HTML to PNG using wkhtmltoimage
        $png_file = 'report.png';
        // exec("wkhtmltoimage $html_file $png_file");
        exec("wkhtmltoimage --width 2000 --quality 80 $html_file $png_file");


        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        if (file_exists($png_file)) {
            $request_array = [
                'flag' => 1,
                'file' => base64_encode(file_get_contents($png_file)),
                'ext' => pathinfo($png_file, PATHINFO_EXTENSION),
                'bucket_name' => 'sl-website',
                'folder_name' => 'reports',
                'new_file_name' => 'collection'
            ];

            $upload_return = $CommonComponent->upload_document(null, $request_array);
            if ($upload_return['status'] == 1) {
                unlink($png_file);
            }
        }

        $mobile = array("9625891341", "9289767308", "9161674682", "9897632657");
        $link = LMS_URL."CronJobs/CronReportController/direct_repport_view?file_name=" . ($upload_return['file_name'] ?? '');
        $request_array = [$report_name, date('d-M-Y', strtotime($date))];

        foreach ($mobile as $number) {
            $this->middlewareWhatsAppReport($number, $link, $report_name, $request_array);
        }

        $subject = "Credit Head Approved Report | " . date('d-M-Y', strtotime($date)) . " | " . implode(", ", $mobile);
        $this->middlewareEmail($this->cron_notification_email, $subject, $html);

        echo 'OK';
    }

    public function lead_flow_report() {
        $report_name = 'Lead/Application Flow Report';
        $date = date('Y-m-d', strtotime('-1 day')); // Yesterday's date

        // Optimized SQL query using DATE_FORMAT
        $sql = "SELECT
                    lead_id,
                    monthly_salary_amount,
                    status,
                    lead_doable_to_application_status,
                    lead_entry_date,
                    lead_status_id
                FROM
                    leads
                WHERE
                    lead_entry_date = '$date'
                    AND lead_data_source_id !=17
                    AND lead_active = 1
                    AND user_type='NEW'
                    AND lead_status_id != 8";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Initialize report array
        $report_array = [
            'PARTIAL/REGISTRATION' => ['26K TO 30K' => 0, '30K TO 50K' => 0, 'ABOVE 50K' => 0],
            'LEADS' => ['26K TO 30K' => 0, '30K TO 50K' => 0, 'ABOVE 50K' => 0],
            'APPLICATIONS' => ['26K TO 30K' => 0, '30K TO 50K' => 0, 'ABOVE 50K' => 0]
        ];

        foreach ($result as $row) {
            $monthly_salary_amount = (int)$row['monthly_salary_amount'];
            $lead_doable_to_application_status = (int)$row['lead_doable_to_application_status'];

            if ($monthly_salary_amount >= 26000 && $monthly_salary_amount < 30000 && !in_array($row['lead_status_id'], [41, 42])) {
                if ($lead_doable_to_application_status == 2) {
                    $report_array['LEADS']['26K TO 30K']++;
                } else {
                    $report_array['APPLICATIONS']['26K TO 30K']++;
                }
            } elseif ($monthly_salary_amount >= 30000 && $monthly_salary_amount < 50000 && !in_array($row['lead_status_id'], [41, 42])) {
                if ($lead_doable_to_application_status == 2) {
                    $report_array['LEADS']['30K TO 50K']++;
                } else {
                    $report_array['APPLICATIONS']['30K TO 50K']++;
                }
            } elseif ($monthly_salary_amount >= 50000 && !in_array($row['lead_status_id'], [41, 42])) {
                if ($lead_doable_to_application_status == 2) {
                    $report_array['LEADS']['ABOVE 50K']++;
                } else {
                    $report_array['APPLICATIONS']['ABOVE 50K']++;
                }
            }

            if ($monthly_salary_amount >= 26000 && $monthly_salary_amount < 30000 && in_array($row['lead_status_id'], [41, 42])) {
                $report_array['PARTIAL/REGISTRATION']['26K TO 30K']++;
            } elseif ($monthly_salary_amount >= 30000 && $monthly_salary_amount < 50000 && in_array($row['lead_status_id'], [41, 42])) {
                $report_array['PARTIAL/REGISTRATION']['30K TO 50K']++;
            } elseif ($monthly_salary_amount >= 50000 && in_array($row['lead_status_id'], [41, 42])) {
                $report_array['PARTIAL/REGISTRATION']['ABOVE 50K']++;
            }
        }

        // HTML Report
        $html = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . $report_name . '</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 10px;
                            background-color: #f4f4f4;
                            justify-content: center;
                        }

                        .report-container {
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                            border-top: 6px solid #007BFF;
                            border-bottom: 6px solid #28A745;
                            max-width: 100%;
                            overflow-x: auto;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                            min-width: 600px;
                            border: 1px solid #ddd; /* Thin gray border for table */
                        }

                        th {
                            background: linear-gradient(135deg, #007BFF, #6610F2);
                            color: white;
                            font-weight: bold;
                            text-transform: uppercase;
                            padding: 12px;
                            border-bottom: 1px solid #aaa; /* Thin border under header */
                        }

                        td {
                            padding: 10px;
                            border: 1px solid #ddd; /* Thin gray border for cells */
                            text-align: center;
                        }

                        .total-row {
                            background: linear-gradient(135deg, #28A745, #20C997);
                            color: white;
                            font-weight: bold;
                            border-top: 1px solid #3E8E41; /* Thin border for total row */
                        }

                        /* Hover effect for better UX */
                        tr:hover {
                            background-color: #f1f1f1;
                        }
                    </style>
                </head>
                    <body>
                        <div class="report-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan="5">' . $report_name . ' | ' . date('d-M-Y', strtotime($date)) . '</th>
                                    </tr>
                                    <tr>
                                        <th>Category</th>
                                        <th>26K - 30K</th>
                                        <th>30K - 50K</th>
                                        <th>Above 50K</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>';

        // Populate table rows with report data
        foreach ($report_array as $category => $data) {
            $total = array_sum($data);
            $html .= '<tr>
                    <td>' . htmlspecialchars($category) . '</td>
                    <td>' . $data['26K TO 30K'] . '</td>
                    <td>' . $data['30K TO 50K'] . '</td>
                    <td>' . $data['ABOVE 50K'] . '</td>
                    <td>' . $total . '</td>
                  </tr>';
        }

        // Grand Total Row
        $grand_total = array_sum($report_array['LEADS']) + array_sum($report_array['APPLICATIONS']) + array_sum($report_array['PARTIAL/REGISTRATION']);
        $html .= '<tr class="total-row">
                <td>Total</td>
                <td>' . ($report_array['LEADS']['26K TO 30K'] + $report_array['APPLICATIONS']['26K TO 30K'] + $report_array['PARTIAL/REGISTRATION']['26K TO 30K']) . '</td>
                <td>' . ($report_array['LEADS']['30K TO 50K'] + $report_array['APPLICATIONS']['30K TO 50K'] + $report_array['PARTIAL/REGISTRATION']['30K TO 50K']) . '</td>
                <td>' . ($report_array['LEADS']['ABOVE 50K'] + $report_array['APPLICATIONS']['ABOVE 50K'] + $report_array['PARTIAL/REGISTRATION']['ABOVE 50K']) . '</td>
                <td>' . $grand_total . '</td>
              </tr>';

        $html .= '</tbody>
                        </table>
                    </div>
                </body>
                </html>';

        // Save the HTML to a temporary file
        $html_file = '/tmp/report.html';
        file_put_contents($html_file, $html);

        // Use wkhtmltoimage to generate the PNG
        $png_file = 'report.png'; // Replace with actual path
        // exec("wkhtmltoimage $html_file $png_file");
        exec("wkhtmltoimage --width 1400 --quality 100 --zoom 2 --enable-local-file-access $html_file $png_file");


        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();
        if (file_exists($png_file)) {
            $request_array = array();
            $request_array['flag'] = 1;
            $request_array['file'] = base64_encode(file_get_contents($png_file));
            $request_array['ext'] = pathinfo($png_file, PATHINFO_EXTENSION);
            $request_array['bucket_name'] = 'sl-website';
            $request_array['folder_name'] = 'reports';
            $request_array['new_file_name'] = 'lead_flow';

            $upload_return = $CommonComponent->upload_document($lead_id, $request_array);

            if ($upload_return['status'] == 1) {
                $return_array['status'] = 1;
                $file_name = $upload_return['file_name'];
                unlink($png_file);
            }
        }

        $mobile = array("9625891341", "9289767308", "9161674682", "7303932041");
        $link = LMS_URL."CronJobs/CronReportController/direct_repport_view?file_name=" . $file_name;
        $request_array = [$report_name, date('d-M-Y', strtotime($date))];

        foreach ($mobile as $key => $value) {
            $result = $this->middlewareWhatsAppReport($value, $link, $report_name, $request_array);
        }

        // Send email notification
        $subject = 'Credit Head Approved Report | ' . date('d-M-Y', strtotime($date)) . " | " . implode(", ", $mobile);
        $this->middlewareEmail($this->cron_notification_email, $subject, $html);
        // echo $html;
        echo 'OK';
    }

    public function conversion_report_report() {
        $report_name = 'Conversion Report';
        $date = date('Y-m-d', strtotime('-1 day'));

        $this->load->model('Report_Model');
        $html = $this->Report_Model->LeadConversionModel($date, $date);

        // Save the HTML to a temporary file
        $html_file = '/tmp/report.html';
        file_put_contents($html_file, $html);

        // Use wkhtmltoimage to generate the PNG
        $png_file = 'report.png';
        exec("wkhtmltoimage $html_file $png_file");

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();
        if (file_exists($png_file)) {
            $request_array = array();
            $request_array['flag'] = 1;
            $request_array['file'] = base64_encode(file_get_contents($png_file));
            $request_array['ext'] = pathinfo($png_file, PATHINFO_EXTENSION);
            $request_array['bucket_name'] = 'sl-website';
            $request_array['folder_name'] = 'reports';
            $request_array['new_file_name'] = 'credit';

            $upload_return = $CommonComponent->upload_document($lead_id, $request_array);

            if ($upload_return['status'] == 1) {
                $return_array['status'] = 1;
                $file_name = $upload_return['file_name'];
                unlink($png_file);
            }
        }

        $mobile = array("9289767308");
        $link = LMS_URL."CronJobs/CronReportController/direct_repport_view?file_name=" . $file_name;
        $request_array = [$report_name, date('d-M-Y', strtotime($date))];

        foreach ($mobile as $key => $value) {
            $result = $this->middlewareWhatsAppReport($value, $link, $report_name, $request_array);
        }

        // Send email notification
        $subject = 'Credit Head Approved Report | ' . date('d-M-Y', strtotime($date)) . " | " . implode(", ", $mobile);
        $this->middlewareEmail($this->cron_notification_email, $subject, $html);
        // echo $html;
        echo 'OK';
    }

    public function direct_repport_view() {

        if (isset($_GET['file_name'])) {
            $file_name = $_GET['file_name'];
        } else {
            exit("File name not found.");
        }
        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $response_array = array();
        $response_array['file'] = $file_name;
        $response_array['bucket_name'] = 'sl-website';
        $response_array['folder_name'] = 'reports';

        $result_array = $CommonComponent->download_document(0, $response_array);

        if ($result_array['status'] == 1) {
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            if (in_array(strtolower($ext), array('jpg', 'jpeg', 'png'))) {
                $content_type = 'image/' . strtolower($ext);
            } else {
                $content_type = $result_array['header_content_type'];
            }
            header("Content-Type: {$content_type}");
            header('Content-Description: File Transfer');
            // header('Content-Disposition: attachment; filename=' . $file_name);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($result_array['document_body']));
            ob_clean();
            flush();
            echo $result_array['document_body'];
        } else {
            echo "File not found.";
        }
    }

    public function middlewareWhatsAppReport($mobile, $link, $report_name, $request_array) {
        $status = 0;
        $error = "";
        $provider_name = "Whistle";

        if (empty($mobile) || empty($link) || empty($report_name)) {
            $error = "Please check mobile number, link, and report name.";
        } else {
            $url = 'https://partnersv1.pinbot.ai/v3/491310100730713/messages';
            $apiKey = '3fb59eb7-a31e-11ef-bb5a-02c8a5e042bd';

            $payload = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $mobile,
                "type" => "template",
                "template" => [
                    "name" => "sl_reports",
                    "language" => ["code" => "en"],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [[
                                "type" => "image",
                                "image" => ["link" => $link]
                            ]]
                        ],
                        [
                            "type" => "body",
                            "parameters" => array_map(function ($text) {
                                return ["type" => "text", "text" => $text];
                            }, $request_array)
                        ]
                    ]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'apikey: ' . $apiKey
            ]);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
            }
            curl_close($ch);

            if ($httpCode == 200) {
                $status = 1;
            } else {
                $error = "API Request failed with HTTP Code: " . $httpCode . " Response: " . $response;
            }
        }

        $insert_log_array = [
            'whatsapp_provider' => $provider_name,
            'whatsapp_type_id' => 2,
            'whatsapp_mobile' => $mobile,
            'whatsapp_request' => addslashes(json_encode($payload)),
            'whatsapp_response' => addslashes($response ?? ''),
            'whatsapp_api_status_id' => $status,
            'whatsapp_errors' => $error,
            'whatsapp_created_on' => date("Y-m-d H:i:s")
        ];

        $this->db->insert('api_whatsapp_logs', $insert_log_array);

        return ["status" => $status, "error" => $error];
    }

    public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 99, $cc_email = "", $reply_to = "") {
        $status = 0;
        $error = "";
        $provider_name = "";
        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = NOREPLY_EMAIL;

            $return_array = common_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);

            if (!empty($return_array) && $return_array['status'] == 1) {
                $status = $return_array['status'];
            } else {
                $return_array = json_decode($response, true);
                $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
            }

            if ($status == 1) {
                $status = $status;
                $error = $return_array['error'];

                $insert_log_array = array();
                $insert_log_array['email_provider'] = $provider_name;
                $insert_log_array['email_type_id'] = $email_type_id;
                $insert_log_array['email_address'] = $email;
                $insert_log_array['email_content'] = addslashes($message);
                $insert_log_array['email_api_status_id'] = $status;
                $insert_log_array['email_errors'] = $error;
                $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

                $this->SanctionModel->emaillog_insert($insert_log_array);
            }

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }
}
