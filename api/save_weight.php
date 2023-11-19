<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['status'], $post['product'], $post['timestampData']
, $post['vehicleNumber'], $post['driverName'], $post['farmId']
, $post['averageCage'], $post['averageBird'], $post['capturedData']
, $post['remark'], $post['startTime'], $post['endTime'], $post['cratesCount']
, $post['numberOfCages'], $post['totalCagesWeight'], $post['weightDetails'])){

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
	$cratesCount = $post['cratesCount'];
	$numberOfCages = $post['numberOfCages'];
	$totalCagesWeight = $post['totalCagesWeight'];
	$max_crates = 0;

	$remark = $post['remark'];
	$startTime = $post['startTime'];
	$endTime = $post['endTime'];

	$customerName = null;
	$supplierName = null;
	$minWeight = null;
	$maxWeight = null;
	$serialNo = "";
	$today = date("Y-m-d 00:00:00");

	if($post['max_crates'] != null && $post['max_crates'] != ''){
		$max_crates = (int)$post['max_crates'];
	}

	/*if((int)$cratesCount >= $max_crates){
		$status = "Complete";
	}
	else{
		$status = "Picking";
	}*/

	if($post['customerName'] != null && $post['customerName'] != ''){
		$customerName = $post['customerName'];
	}

	if($post['minWeight'] != null && $post['minWeight'] != ''){
		$minWeight = $post['minWeight'];
	}

	if($post['maxWeight'] != null && $post['maxWeight'] != ''){
		$maxWeight = $post['maxWeight'];
	}

	if($post['serialNo'] == null || $post['serialNo'] == ''){
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

		if ($update_stmt = $db->prepare("UPDATE weighing SET customer=?, supplier=?, product=?, driver_name=?, lorry_no=?, farm_id=?, average_cage=?, average_bird=?, 
		minimum_weight=?, maximum_weight=?, weight_data=?, remark=?, start_time=?, weight_time=?, end_time=?, total_cage=?, number_of_cages=?, total_cages_weight=? 
		WHERE id=?")){
			$update_stmt->bind_param('sssssssssssssssssss', $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, 
			$data2, $endTime, $cratesCount, $numberOfCages, $totalCagesWeight, $id);
		
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
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Updated Successfully!!" 
					)
				);
			}

			if ($insert_stmt2 = $db->prepare("INSERT INTO weight_cart (weighing_id, farm_id, weight_data, start_time, end_time) 
			VALUES (?, ?, ?, ?, ?)")){
				$insert_stmt2->bind_param('sssss', $id, $farmId, $data, $startTime, $endTime);
				$insert_stmt2->execute();
				$insert_stmt2->close();
			}

			$db->close();
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
		$id = '0';

		if ($insert_stmt = $db->prepare("INSERT INTO weighing (serial_no, customer, supplier, product, driver_name, lorry_no, 
		farm_id, average_cage, average_bird, minimum_weight, maximum_weight, weight_data, remark, start_time, weight_time, end_time,
		total_cage, number_of_cages, total_cages_weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){		    
			$insert_stmt->bind_param('sssssssssssssssssss', $serialNo, $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, $data2, $endTime,
			$cratesCount, $numberOfCages, $totalCagesWeight);		
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
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Added Successfully!!" 
					)
				);
			}

			if ($insert_stmt2 = $db->prepare("INSERT INTO weight_cart (weighing_id, farm_id, weight_data, start_time, end_time) 
			VALUES (?, ?, ?, ?, ?)")){
				$insert_stmt2->bind_param('sssss', $id, $farmId, $data, $startTime, $endTime);
				$insert_stmt2->execute();
				$insert_stmt2->close();
			}

			$db->close();
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