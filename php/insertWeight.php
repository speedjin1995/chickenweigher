<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

if(isset($_POST['customerNo'], $_POST['product'], $_POST['vehicleNo'], $_POST['driver'], $_POST['farm'])){
	$userId = $_SESSION['userID'];
	$status = "Sales";
	$product = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_STRING);
	$vehicleNo = filter_input(INPUT_POST, 'vehicleNo', FILTER_SANITIZE_STRING);
	$driver = filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_STRING);
	$farm = filter_input(INPUT_POST, 'farm', FILTER_SANITIZE_STRING);
	
	$orderNo = null;
	$poNo = null;
	$maxCrate = null;
	$assignTo = null;
	$grade = null;
	$houseNo = null;
	$gender = null;
	$aveBird = null;
	$aveCage = null;
	$group = null;
	$customerName = null;
	$supplierName = null;
	$minWeight = null;
	$maxWeight = null;
	$remark = null;
	$serialNo = "";
	$today = date("Y-m-d 00:00:00");
	
	if($status == 'Sales'){
		$customerName = filter_input(INPUT_POST, 'customerNo', FILTER_SANITIZE_STRING);
	}
	else{
		$supplierName = filter_input(INPUT_POST, 'customerNo', FILTER_SANITIZE_STRING);
	}

	if($_POST['maxCrate'] != null && $_POST['maxCrate'] != ''){
		$maxCrate = filter_input(INPUT_POST, 'maxCrate', FILTER_SANITIZE_STRING);
	}

	if($_POST['poNo'] != null && $_POST['poNo'] != ''){
		$poNo = filter_input(INPUT_POST, 'poNo', FILTER_SANITIZE_STRING);
	}

	if($_POST['aveBird'] != null && $_POST['aveBird'] != ''){
		$aveBird = filter_input(INPUT_POST, 'aveBird', FILTER_SANITIZE_STRING);
	}

	if($_POST['assignTo'] != null && $_POST['assignTo'] != ''){
		$assignTo = filter_input(INPUT_POST, 'assignTo', FILTER_SANITIZE_STRING);
	}

	if($_POST['aveCage'] != null && $_POST['aveCage'] != ''){
		$aveCage = filter_input(INPUT_POST, 'aveCage', FILTER_SANITIZE_STRING);
	}

	if($_POST['minWeight'] != null && $_POST['minWeight'] != ''){
		$minWeight = filter_input(INPUT_POST, 'minWeight', FILTER_SANITIZE_STRING);
	}

	if($_POST['maxWeight'] != null && $_POST['maxWeight'] != ''){
		$maxWeight = filter_input(INPUT_POST, 'maxWeight', FILTER_SANITIZE_STRING);
	}

	if($_POST['remark'] != null && $_POST['remark'] != ''){
		$remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_STRING);
	}

	if($_POST['id'] == null || $_POST['id'] == ''){
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

	if($_POST['id'] != null && $_POST['id'] != ''){
		$id = $_POST['id'];
		if ($update_stmt = $db->prepare("UPDATE weighing SET group_no=?, customer=?, supplier=?, product=?, driver_name=?, lorry_no=?, farm_id=?, 
		average_cage=?, average_bird=?, minimum_weight=?, maximum_weight=?, grade=?, gender=?, house_no=?, remark=?, weighted_by=?, max_crate=?, 
		po_no=? WHERE id=?")){
			$update_stmt->bind_param('sssssssssssssssssss', $group, $customerName, $supplierName, $product, $driver, $vehicleNo, $farm, $aveCage,
			$aveBird, $minWeight, $maxWeight, $grade, $gender, $houseNo, $remark, $assignTo, $maxCrate, $poNo, $id);
		
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
					"message"=> "cannot prepare statement"
				)
			);
		}
	}
	else{
		if ($insert_stmt = $db->prepare("INSERT INTO weighing (serial_no, group_no, customer, supplier, product, driver_name, lorry_no, 
		farm_id, average_cage, average_bird, minimum_weight, maximum_weight, grade, gender, house_no, remark, weighted_by, status, 
		max_crate, po_no, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){
		    $data = null;
			$data2 = null;
			$insert_stmt->bind_param('sssssssssssssssssssss', $serialNo, $group, $customerName, $supplierName, $product, $driver, 
			$vehicleNo, $farm, $aveCage, $aveBird, $minWeight, $maxWeight, $grade, $gender, $houseNo, $remark, $assignTo, 
			$status, $maxCrate, $poNo, $userId);
								
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