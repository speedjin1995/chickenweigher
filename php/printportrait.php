<?php

require_once 'db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';

$mapOfWeights = array();

$totalGross = 0.0;
$totalCrate = 0.0;
$totalReduce = 0.0;
$totalNet = 0.0;
$totalCrates = 0;
$totalBirds = 0;
$totalMaleBirds = 0;
$totalMaleCages = 0;
$totalFemaleBirds = 0;
$totalFemaleCages = 0;
$totalMixedBirds = 0;
$totalMixedCages = 0;
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
}

function totalWeight($strings){ 
    $totalSum = 0;

    for ($i =0; $i < count($strings); $i++) {
        if (preg_match('/([\d.]+)/', $strings[$i]['grossWeight'], $matches)) {
            $value = floatval($matches[1]);
            $totalSum += $value;
        }
    }

    return $totalSum;
}

function rearrangeList($weightDetails) {
    global $mapOfWeights, $totalGross, $totalCrate, $totalReduce, $totalNet, $totalCrates, $totalBirds, $totalMaleBirds, $totalMaleCages, $totalFemaleBirds, $totalFemaleCages, $totalMixedBirds, $totalMixedCages;

    if (!empty($weightDetails)) {
        $array1 = array(); // group
        $array2 = array(); // house

        foreach ($weightDetails as $element) {
            if(!in_array($element['groupNumber'], $array1)){
                $mapOfWeights[] = array( 
                    'groupNumber' => $element['groupNumber'],
                    'weightList' => array()
                );

                array_push($array1, $element['groupNumber']);
            }

            $key = array_search($element['groupNumber'], $array1);
            array_push($mapOfWeights[$key]['weightList'], $element);
            

            $totalGross += floatval($element['grossWeight']);
            $totalCrate += floatval($element['tareWeight']);
            $totalReduce += floatval($element['reduceWeight']);
            $totalNet += floatval($element['netWeight']);
            $totalCrates += intval($element['numberOfCages']);
            $totalBirds += intval($element['numberOfBirds']);

            if ($element['sex'] == 'Male') {
                $totalMaleBirds += intval($element['numberOfBirds']);
                $totalMaleCages += intval($element['numberOfCages']);
            } elseif ($element['sex'] == 'Female') {
                $totalFemaleBirds += intval($element['numberOfBirds']);
                $totalFemaleCages += intval($element['numberOfCages']);
            } elseif ($element['sex'] == 'Mixed') {
                $totalMixedBirds += intval($element['numberOfBirds']);
                $totalMixedCages += intval($element['numberOfCages']);
            }
        }
    }
    
    // Now you can work with $mapOfWeights and the calculated totals as needed.
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
                $time = date ( 'j g:i:s', $duration );
                $weightData = json_decode($row['weight_data'], true);
                $totalWeight = totalWeight($weightData);
                //rearrangeList($weightData);
                $weightTime = json_decode($row['weight_time'], true);
                $userName = "Pri Name";

                if ($select_stmt2 = $db->prepare("select * FROM users WHERE id=?")) {
                    $uid = $row['weighted_by'];
                    $select_stmt2->bind_param('s', $uid);

                    if ($select_stmt2->execute()) {
                        $result2 = $select_stmt2->get_result();

                        if ($row2= $result2->fetch_assoc()) { 
                            $userName = $row2['name'];
                        }
                    }
                }

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
                border: 1px dashed black;
                border-collapse: collapse;
            } 
            
            .table-bordered th, .table-bordered td {
                border: 1px dashed black;
                font-family: sans-serif;
            } 

            .table-full {
                border: 1px solid black;
                border-collapse: collapse;
            } 
            
            .table-full th, .table-full td {
                border: 1px solid black;
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
        </table>
        
        <table class="table">
            <tbody>
                <tr>
                    <td style="width: 50%;border-top:0px;">';

                    $message .= '<p>
                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Customer : </span>
                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['customer'].'</span>
                    </p>';
                        
                    $message .= '</td>
                    <td style="width: 50%;border-top:0px;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">CCBSB No.: </span>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['serial_no'].'</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farm : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['farm_id'].'</span>
                        </p>
                    </td>
                    <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Date : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['created_datetime'].'</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Total Crates : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['total_cage'].'</span>
                        </p>
                    </td>
                    <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Lorry No : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['lorry_no'].'</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;border-top:0px;padding: 0 0.7rem;"></td>
                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Average Crate Wt. : </span>
                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['average_cage'].'</span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table><br>
        <table class="table-bordered"><tbody>';

        $count = 1;
        $rowCount = 0;
        $rowTotal = 0;
        $allTotal = 0;
        $indexString = '<tr>';
        
        for ($i = 0; $i < count($weightData); $i++) {
            $indexString .= '<td>'.$count.'</td><td>'.$weightData[$i]['netWeight'].'</td>';
            $rowTotal += (float)$weightData[$i]['netWeight'];
            $allTotal += (float)$weightData[$i]['netWeight'];

            if($count % 10 == 0){
                $indexString .= '<td>'.$rowTotal.'</td></tr>';
                $rowTotal = 0;
                $rowCount = 0;

                if($count < count($weightData)){
                    $indexString .= '<tr>';
                }
            }
            else{
                $rowCount++;
            }
            
            $count++;
        }

        if ($rowCount > 0) {
            for ($k = 0; $k < (10 - $rowCount); $k++) {
                if($k == ((10 - $rowCount) - 1)){
                    $indexString .= '<td></td><td></td><td>'.$rowTotal.'</td>';
                }
                else{
                    $indexString .= '<td></td><td></td>';
                }
            }
            $indexString .= '</tr>';
        }
        
        $message .= $indexString;
        $message .= '</tbody><tfoot><th colspan="20" style="text-align: right;">Total</th><th>'.$allTotal.'</th></tfoot></table><br>';
        
        $message .= '<table class="table">
                    <tbody>
                        <tr>
                            <td style="width: 40%;">
                                <table class="table-full" style="width: 90%;">
                                    <tbody>
                                        <tr>
                                            <td>Total Gross Wt.</td>
                                            <td>'.$totalWeight.'</td>
                                        </tr>
                                        <tr>
                                            <td>Total Crate Wt.</td>
                                            <td>'.number_format(((float)$row['average_cage']/(float)$row['total_cage']), 2, '.', '').'</td>
                                        </tr>
                                        <tr>
                                            <td>Total Net Wt. </td>
                                            <td>'.number_format(($totalWeight - ((float)$row['average_cage']/(float)$row['total_cage'])), 2, '.', '').'</td>
                                        </tr>
                                        <tr>
                                            <td>Unit Price</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>Amount</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td style="width: 30%;">
                                <table class="table-full" style="width: 90%;">
                                    <tbody>
                                        <tr>
                                            <td>Mix.</td>
                                            <td>'.$totalMixedBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td>Male</td>
                                            <td>'.$totalMaleBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td>Female</td>
                                            <td>'.$totalFemaleBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td>Total Birds</td>
                                            <td>'.$totalBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td>Avg. Bird Wt.</td>
                                            <td>'.$row['average_bird'].'</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td style="width: 30%;">
                                <table class="table-full" style="width: 90%;">
                                    <tbody>
                                        <tr>
                                            <td>Loading Start</td>
                                        </tr>
                                        <tr>
                                            <td>'.$row['start_time'].'</td>
                                        </tr>
                                        <tr>
                                            <td>Loading End</td>
                                        </tr>
                                        <tr>
                                            <td>'.$row['end_time'].'</td>
                                        </tr>
                                        <tr>
                                            <td>'.$duration.'</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td> 
                        </tr>
                    </tbody>
                </table></html>';

                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message,
                        "string" => $indexString
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