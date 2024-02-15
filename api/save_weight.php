<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['status'], $post['product'], $post['timestampData']
, $post['vehicleNumber'], $post['driverName'], $post['farmId']
, $post['averageCage'], $post['averageBird'], $post['capturedData']
, $post['remark'], $post['startTime'], $post['endTime'], $post['cratesCount']
, $post['numberOfCages'], $post['totalCagesWeight'], $post['weightDetails']
, $post['cageDetails'])){

	$status = $post['status'];
	$product = $post['product'];
	$vehicleNumber = $post['vehicleNumber'];
	$driverName = $post['driverName'];
	$farmId = $post['farmId'];
	$averageCage = $post['averageCage'];
	$averageBird = $post['averageBird'];
	$capturedData = $post['capturedData'];
	$timestampData = $post['timestampData'];
	$weightDetails = $post['weightDetails'];
	$cageDetails = $post['cageDetails'];
	$cratesCount = $post['cratesCount'];
	$numberOfCages = $post['numberOfCages'];
	$totalCagesWeight = $post['totalCagesWeight'];
	$max_crates = 0;

	$remark = $post['remark'];
	$startTime = $post['startTime'];
	$endTime = $post['endTime'];

    $doNo = null;
	$customerName = null;
	$supplierName = null;
	$minWeight = null;
	$maxWeight = null;
	$attandence1 = null;
	$attandence2 = null;
	$serialNo = "";
	$today = date("Y-m-d 00:00:00");
	
	if(isset($post['doNo']) && $post['doNo'] != null && $post['doNo'] != ''){
		$doNo = $post['doNo'];
	}

	if(isset($post['max_crates']) && $post['max_crates'] != null && $post['max_crates'] != ''){
		$max_crates = (int)$post['max_crates'];
	}

	if(isset($post['customerName']) && $post['customerName'] != null && $post['customerName'] != ''){
		$customerName = $post['customerName'];
	}

	if(isset($post['minWeight']) && $post['minWeight'] != null && $post['minWeight'] != ''){
		$minWeight = $post['minWeight'];
	}

	if(isset($post['maxWeight']) && $post['maxWeight'] != null && $post['maxWeight'] != ''){
		$maxWeight = $post['maxWeight'];
	}

	if(isset($post['attandence1']) && $post['attandence1'] != null && $post['attandence1'] != ''){
		$attandence1 = $post['attandence1'];
	}

	if(isset($post['attandence2']) && $post['attandence2'] != null && $post['attandence2'] != ''){
		$attandence2 = $post['attandence2'];
	}

	if(isset($post['serialNo']) && $post['serialNo'] == null || $post['serialNo'] == ''){
		$serialNo = 'S'.date("Ymd");

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

	if(isset($post['id']) && $post['id'] != null && $post['id'] != ''){
		$id = $post['id'];
		$data = json_encode($weightDetails);
		$data2 = json_encode($timestampData);
		$data3 = json_encode($cageDetails);

		if ($update_stmt = $db->prepare("UPDATE weighing SET customer=?, supplier=?, product=?, driver_name=?, lorry_no=?, farm_id=?, average_cage=?, average_bird=?, 
		minimum_weight=?, maximum_weight=?, weight_data=?, remark=?, start_time=?, weight_time=?, end_time=?, total_cage=?, number_of_cages=?, total_cages_weight=?, 
		follower1=?, follower2=?, status=?, po_no=?, cage_data=? WHERE id=?")){
			$update_stmt->bind_param('ssssssssssssssssssssssss', $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, 
			$data2, $endTime, $cratesCount, $numberOfCages, $totalCagesWeight, $attandence1, $attandence2, $status, $doNo, $data3, $id);
		
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
						"message"=> "Updated Successfully!!",
						"serialNo" => $post['serialNo'],
						"weightId" => $id
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
	else{
		$data = json_encode($weightDetails);
		$data2 = json_encode($timestampData);
		$data3 = json_encode($cageDetails);
		$now = date("Y-m-d H:i:s");
		$id = '0';

		if ($insert_stmt = $db->prepare("INSERT INTO weighing (serial_no, customer, supplier, product, driver_name, lorry_no, 
		farm_id, average_cage, average_bird, minimum_weight, maximum_weight, weight_data, remark, start_time, weight_time, end_time,
		total_cage, number_of_cages, total_cages_weight, follower1, follower2, status, po_no, cage_data, booking_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){		    
			$insert_stmt->bind_param('sssssssssssssssssssssssss', $serialNo, $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, $data2, $endTime,
			$cratesCount, $numberOfCages, $totalCagesWeight, $attandence1, $attandence2, $status, $doNo, $data3, $now);		
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
				$id = $insert_stmt->insert_id;
				$insert_stmt->close();
				$db->close();
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Added Successfully!!",
						"serialNo"=> $serialNo,
						"weightId" => $id
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