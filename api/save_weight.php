<?php
require_once 'db_connect.php';
//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($_POST['status'], $_POST['groupNumber'], $_POST['product']
, $_POST['vehicleNumber'], $_POST['driverName'],$_POST['farmId']
, $_POST['averageCage'], $_POST['averageBird'], $_POST['capturedData'])){

	$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
	$groupNumber = filter_input(INPUT_POST, 'groupNumber', FILTER_SANITIZE_STRING);
	$product = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_STRING);
	$vehicleNumber = filter_input(INPUT_POST, 'vehicleNumber', FILTER_SANITIZE_STRING);
	$driverName = filter_input(INPUT_POST, 'driverName', FILTER_SANITIZE_STRING);
	$farmId = filter_input(INPUT_POST, 'farmId', FILTER_SANITIZE_STRING);
	$averageCage = filter_input(INPUT_POST, 'averageCage', FILTER_SANITIZE_STRING);
	$averageBird = filter_input(INPUT_POST, 'averageBird', FILTER_SANITIZE_STRING);
	$capturedData = $_POST['capturedData'];

	$customerName = null;
	$supplierName = null;
	$minWeight = null;
	$maxWeight = null;
	$serialNo = "";
	$today = date("Y-m-d 00:00:00");

	if($_POST['customerName'] != null && $_POST['customerName'] != ''){
		$customerName = filter_input(INPUT_POST, 'customerName', FILTER_SANITIZE_STRING);
	}

	if($_POST['supplierName'] != null && $_POST['supplierName'] != ''){
		$supplierName = filter_input(INPUT_POST, 'supplierName', FILTER_SANITIZE_STRING);
	}

	if($_POST['minWeight'] != null && $_POST['minWeight'] != ''){
		$minWeight = filter_input(INPUT_POST, 'minWeight', FILTER_SANITIZE_STRING);
	}

	if($_POST['maxWeight'] != null && $_POST['maxWeight'] != ''){
		$maxWeight = filter_input(INPUT_POST, 'maxWeight', FILTER_SANITIZE_STRING);
	}

	if($_POST['serialNo'] == null || $_POST['serialNo'] == ''){
		if($status == 'Sales'){
			$serialNo = 'S'.date("Ymd");
		}
		else{
			$serialNo = 'P'.date("Ymd");
		}

		if ($select_stmt = $db->prepare("SELECT COUNT(*) FROM weighing WHERE created_datetime >= ?")) {
            $select_stmt->bind_param('s', $today);
            
            // Execute the prepared query.
            if (! $select_stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Failed to get latest count"
                    )); 
            }
            else{
                $result = $select_stmt->get_result();
                $count = 1;
                
                if ($row = $result->fetch_assoc()) {
                    $count = (int)$row['COUNT(*)'] + 1;
                    $select_stmt->close();
                }

                $charSize = strlen(strval($count));

                for($i=0; $i<(4-(int)$charSize); $i++){
                    $serialNo.='0';  // S0000
                }
        
                $serialNo .= strval($count);  //S00009
			}
		}
	}

	if(isset($_POST['id']) && $_POST['id'] != null && $_POST['id'] != ''){
		if ($update_stmt = $db->prepare("UPDATE weight SET vehicleNo=?, lotNo=?, batchNo=?, invoiceNo=?, deliveryNo=?, purchaseNo=?, customer=?, productName=?, package=?
		, unitWeight=?, currentWeight=?, tare=?, totalWeight=?, actualWeight=?, currency=?, moq=?, unitPrice=?, totalPrice=?, remark=?, supplyWeight=?, varianceWeight=?, status=?, 
		dateTime=?, manual=?, manualVehicle=?, manualOutgoing=?, reduceWeight=?, outGDateTime=?, inCDateTime=?, pStatus=?, variancePerc=?, transporter=?, updated_by=? WHERE id=?")){
			$update_stmt->bind_param('ssssssssssssssssssssssssssssssssss', $vehicleNo, $lotNo, $batchNo, $invoiceNo, $deliveryNo, $purchaseNo, $customerNo, $product,
			$package, $unitWeight, $currentWeight, $tareWeight, $totalWeight, $actualWeight, $currency, $moq, $unitPrice, $totalPrice, $remark, $supplyWeight, $varianceWeight, 
			$status, $dateTime, $manual, $manualVehicle, $manualOutgoing, $reduceWeight, $outGDateTime, $inCDateTime, $pStatus, $variancePerc, $transporter, $userId, $_POST['id']);
		
			// Execute the prepared query.
			if (! $update_stmt->execute()){
				echo json_encode(
					array(
						"status"=> "failed", 
						"message"=> $update_stmt->error
					)
				);
			} 
			else{
				$update_stmt->close();
				$db->close();
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Updated Successfully!!" 
					)
				);
			}
		}
		else{
			echo json_encode(
				array(
					"status"=> "failed", 
					"message"=> $insert_stmt->error
				)
			);
		}
	}
	else{
		if ($insert_stmt = $db->prepare("INSERT INTO weighing (serialNo, group_no, customer, supplier, product, driver_name, lorry_no, 
		farm_id, average_cage, average_bird, minimum_weight, maximum_weight, weight_data) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){
			$insert_stmt->bind_param('sssssssssssss', $serialNo, $groupNumber, $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $capturedData);
								
								// Execute the prepared query.
			if (! $insert_stmt->execute()){
				echo json_encode(
					array(
						"status"=> "failed", 
						"message"=> $insert_stmt->error
					)
				);
			} 
			else{
				$insert_stmt->close();
				$db->close();
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Added Successfully!!" 
					)
				);
			}
		}
		else{
			echo json_encode(
				array(
					"status"=> "failed", 
					"message"=> "cannot prepare statement"
				)
			);  
		}
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