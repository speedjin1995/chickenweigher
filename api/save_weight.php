<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['status'], $post['product'], $post['timestampData']
, $post['vehicleNumber'], $post['driverName'], $post['farmId']
, $post['averageCage'], $post['averageBird'], $post['capturedData']
, $post['remark'], $post['startTime'], $post['endTime'], $post['weightDetails'])){

	$status = $post['status'];
	//$groupNumber = $post['groupNumber'];
	$product = $post['product'];
	$vehicleNumber = $post['vehicleNumber'];
	$driverName = $post['driverName'];
	$farmId = $post['farmId'];
	$averageCage = $post['averageCage'];
	$averageBird = $post['averageBird'];
	$capturedData = $post['capturedData'];
	$timestampData = $post['timestampData'];
	$weightDetails = $post['weightDetails'];

	//$grade = $post['grade'];
	//$gender = $post['gender'];
	//$houseNo = $post['houseNo'];
	$remark = $post['remark'];
	$startTime = $post['startTime'];
	$endTime = $post['endTime'];

	$customerName = null;
	$supplierName = null;
	$minWeight = null;
	$maxWeight = null;
	$serialNo = "";
	$today = date("Y-m-d 00:00:00");

	if($post['customerName'] != null && $post['customerName'] != ''){
		$customerName = $post['customerName'];
	}

	if($post['supplierName'] != null && $post['supplierName'] != ''){
		$supplierName = $post['supplierName'];
	}

	if($post['minWeight'] != null && $post['minWeight'] != ''){
		$minWeight = $post['minWeight'];
	}

	if($post['maxWeight'] != null && $post['maxWeight'] != ''){
		$maxWeight = $post['maxWeight'];
	}

	if($post['serialNo'] == null || $post['serialNo'] == ''){
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

	if(isset($post['id']) && $post['id'] != null && $post['id'] != ''){
		if ($update_stmt = $db->prepare("UPDATE weight SET customer=?, supplier=?, product=?, driver_name=?, lorry_no=?, farm_id=?, average_cage=?, average_bird=?
		, minimum_weight=?, maximum_weight=?, weight_data=?, remark=?, start_time=?, weight_time=?, end_time=? WHERE id=?")){
			$id = $post['id'];
			$data = json_encode($weightDetails);
			$data2 = json_encode($timestampData);
			$update_stmt->bind_param('ssssssssssssssss', $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, $data2, $endTime, $id);
		
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
		if ($insert_stmt = $db->prepare("INSERT INTO weighing (serial_no, customer, supplier, product, driver_name, lorry_no, 
		farm_id, average_cage, average_bird, minimum_weight, maximum_weight, weight_data, remark, start_time, weight_time, end_time) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){
		    $data = json_encode($weightDetails);
			$data2 = json_encode($timestampData);
			$insert_stmt->bind_param('ssssssssssssssss', $serialNo, $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, $data2, $endTime);		
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