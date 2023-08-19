<?php

require_once 'db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
}

function totalWeight($strings){ 
    $totalSum = 0;

    foreach ($strings as $string) {
        if (preg_match('/([\d.]+)/', $string, $matches)) {
            $value = floatval($matches[1]);
            $totalSum += $value;
        }
    }

    return $totalSum;
}

if(isset($_POST['userID'], $_POST["file"])){
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($select_stmt = $db->prepare("select * FROM weighing WHERE id=?")) {
        $select_stmt->bind_param('s', $id);

        if (! $select_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong went execute"
                )); 
        }
        else{
            $result = $select_stmt->get_result();

            if ($row = $result->fetch_assoc()) { 
                $assigned_seconds = strtotime ( $row['start_time'] );
                $completed_seconds = strtotime ( $row['end_time'] );

                $duration = $completed_seconds - $assigned_seconds;

                // j gives days
                $time = date ( 'j g:i:s', $duration );
                $weightData = json_decode($row['weight_data'], true);
                $totalWeight = totalWeight($weightData);
                $weightTime = json_decode($row['weight_time'], true);

                $message = '<html>
    <head>
        <style>
            @media print {
                @page {
                    margin-left: 0.5in;
                    margin-right: 0.5in;
                    margin-top: 0.1in;
                    margin-bottom: 0.1in;
                }
                
            } 

            table {
                width: 100%;
                border-collapse: collapse;
                
            } 
            
            .table th, .table td {
                padding: 0.70rem;
                vertical-align: top;
                border-top: 1px solid #dee2e6;
                
            } 
            
            .table-bordered {
                border: 1px solid #000000;
            } 
            
            .table-bordered th, .table-bordered td {
                border: 1px solid #000000;
                font-family: sans-serif;
            } 
            
            .row {
                display: flex;
                flex-wrap: wrap;
                margin-top: 20px;
            } 
            
            .col-md-3{
                position: relative;
                width: 25%;
            }
            
            .col-md-9{
                position: relative;
                width: 75%;
            }
            
            .col-md-7{
                position: relative;
                width: 58.333333%;
            }
            
            .col-md-5{
                position: relative;
                width: 41.666667%;
            }
            
            .col-md-6{
                position: relative;
                width: 50%;
            }
            
            .col-md-4{
                position: relative;
                width: 33.333333%;
            }
            
            .col-md-8{
                position: relative;
                width: 66.666667%;
            }
            
            #footer {
                position: fixed;
                padding: 10px 10px 0px 10px;
                bottom: 0;
                width: 100%;
                height: 30%;
            }
        </style>
    </head>
    
    <body>
        <table class="table">
            <tbody>
                <tr>
                    <td style="width: 100%;border-top:0px;text-align:center;"><img src="assets/header.png" width="100%" height="auto" /></td>
                </tr>
            </tbody>
        </table><br><br>
        
        <table class="table">
            <tbody>
                <tr>
                    <td colspan="2" style="width: 60%;border-top:0px;">';

                    if(strpos($row['serial_no'], 'S') !== false){
                        $message .= '<p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Customer : </span>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['customer'].'</span>
                        </p>';
                    }
                    else{
                        $message .= '<p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Supplier : </span>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['supplier'].'</span>
                        </p>';
                    }
                        
                    $message .= '</td>
                    <td style="width: 40%;border-top:0px;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">DO No. : </span>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">128800</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Lorry No. : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['lorry_no'].'</span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farm : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['farm_id'].'</span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Date : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['created_datetime'].'</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Driver : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['driver_name'].'</span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farmer : </span>
                            <span style="font-size: 12px;font-family: sans-serif;"></span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Issued By : </span>
                            <span style="font-size: 12px;font-family: sans-serif;"></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 1 : </span>
                            <span style="font-size: 12px;font-family: sans-serif;"></span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Total Count : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.count($weightData).'</span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">First Record : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['start_time'].'</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 2 : </span>
                            <span style="font-size: 12px;font-family: sans-serif;"></span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Crate Wt (kg) : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['average_cage'].'</span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Last Record : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['end_time'].'</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 3 : </span>
                            <span style="font-size: 12px;font-family: sans-serif;"></span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Nett Wt (kg) : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.($totalWeight - (float)$row['average_cage']).'</span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Duration : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$time.'</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="width: 60%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Remark : </span>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['remark'].'</span>
                        </p>
                    </td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Page No. : </span>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">1 of 1</span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table><br>

        <table class="table">
            <tbody>
                <tr style="border-top: 1px solid #000000;border-bottom: 1px solid #000000;font-family: sans-serif;">
                    <td style="width: 30%;border-top:0px;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Crate No.  </span>
                        </p>
                    </td>
                    <td colspan="2" style="width: 70%;border-top:0px;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Weight (kg) / Sample Crate </span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">1</span>
                        </p>
                    </td>
                    <td colspan="2" style="width: 70%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;">'.$row['average_cage'].'/1</span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table><br>
        
        <p style="margin: 0px;"><u style="color: blue;">Group No. '.$row['group_no'].'</u></p>
        <p style="margin: 0px;">'.$row['house_no'].'</p>
        <table class="table">
            <tbody>
                <tr style="border-top: 1px solid #000000;border-bottom: 1px solid #000000;font-family: sans-serif;">
                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Grade '.$row['grade'].'</span>
                        </p>
                    </td>
                    <td colspan="10" style="width: 80%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Weight (kg) / Bird (Nos)</span>
                        </p>
                    </td>
                </tr>';
                
                $indexCount = 0;
                $indexCount2 = 11;
                $indexString = '<td style="border-top:0px;padding: 0 0.7rem;">
                <p>
                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">1</span>
                </p>
            </td>';

                for($i=0; $i<count($weightData); $i++){
                    if($indexCount < 10){
                        $indexString .= '<td style="border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;">'.$weightData[$i].'/'.round((float)$weightData[$i]/(float)$row['average_bird']).'</span><br>
                            <span style="font-size: 14px;font-family: sans-serif;">'.$weightTime[$i].'</span>
                        </p>
                    </td>';
                        $indexCount++;
                    }
                    else{
                        $indexString .= '<tr>'.$indexString.'</tr>';
                        $indexCount = 0;
                        $indexString = '<td style="border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">'.$indexCount2.'</span>
                        </p>
                    </td>';
                        $indexCount2 += 10;
                    }
                }

                $message .= $indexString;
                $message .= '</tbody>
        </table><br>
        
        <div id="footer">
            <hr>
            <table class="table">
                <tbody>
                    <tr>
                        <td style="width: 50%;border-top:0px;">
                            <p><b>SUMMARY - TOTAL</b></p>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 40%;border-top:0px;padding: 0 0.7rem;"></th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">S</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">A</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Total</th>
                                    </tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">Crates</td>';

                                        if($row['grade'] == 'S'){
                                            $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_cage'] * count($weightData)).'</td>
                                                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_cage'] * count($weightData)).'</td>';
                                        }
                                        else{
                                            $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_cage'] * count($weightData)).'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_cage'] * count($weightData)).'</td>';
                                        }
                                        
                                        
                                        $message .= '</tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;">Birds</td>';

                                        if($row['grade'] == 'S'){
                                            $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_bird'] * count($weightData)).'</td>
                                                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                                    <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_bird'] * count($weightData)).'</td>';
                                        }
                                        else{
                                            $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_bird'] * count($weightData)).'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">'.((float)$row['average_bird'] * count($weightData)).'</td>';
                                        }
                                        
                                    $message .= '</tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;">Gross Net Wt (kg)</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">8559.7</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">8559.7</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">Adjust Wt (kg)</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">Weight (kg)</th>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">8559.7</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">8559.7</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">Crates Wt (kg)</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">2689.2</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">2689.2</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">Avg kg/Bird</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">1.77</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">1.77</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">Nett Wt (kg)</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">5870.5</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0.0</td>
                                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">5870.5</td>
                                    </tr>
                                </tbody>
                            </table><br>

                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;"></th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Male</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Female</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Mixed</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Total</th>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;">Crates</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">332</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">332</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;">Birds</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">0</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">3320</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">3320</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width: 50%;border-top:0px;">
                            <p><b>SUMMARY - BY HOUSE</b></p>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;"></th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Crates</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Birds</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Nett (kg)</th>
                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">Average</th>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;">No. 5</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">224</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">2240</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">3950.4</td>
                                        <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;">1.76</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
</html>';

                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message,
                        "weightData" => $weightData,
                        "time" => $weightTime
                    )
                );
            }
            else{
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Data Not Found"
                    )); 
            }
        }
    }
    else{
        echo json_encode(
            array(
                "status" => "failed",
                "message" => "Something went wrong"
            )); 
    }
}
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    ); 
}

?>