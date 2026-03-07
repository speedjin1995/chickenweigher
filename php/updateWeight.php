<?php
require_once 'db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
	echo '<script type="text/javascript">location.href = "../login.html";</script>'; 
} else{
	$id = $_SESSION['userID'];
}

if(isset($_POST['id'])){
	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
	$remark = null;

	if(isset($_POST['remark']) && $_POST['remark'] != null && $_POST['remark'] != ''){
        $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_STRING);
    }

	if ($weight_stmt = $db->prepare("SELECT weight_data FROM weighing WHERE id=?")) {
		$weight_stmt->bind_param('s', $id);
		
		// Execute the prepared query.
		if (! $weight_stmt->execute()) {
			echo json_encode(
				array(
					"status" => "failed",
					"message" => "Something went wrong"
				)); 
		}
		else{
			$result = $weight_stmt->get_result();
			
			if ($row = $result->fetch_assoc()) {
				$weightData = json_decode($row['weight_data'], true);
				
				if (is_array($weightData)) {
					foreach ($weightData as $index => &$entry) {
						if (isset($_POST['remark' . $index])) {
							$entry['remark'] = filter_input(INPUT_POST, 'remark' . $index, FILTER_SANITIZE_STRING);
						}
					}
				} 

				$updatedWeightData = json_encode($weightData);

				if ($update_stmt = $db->prepare("UPDATE weighing SET weight_data=?, remark=? WHERE id=?")) {
					$update_stmt->bind_param('sss', $updatedWeightData, $remark, $id);
					
					// Execute the prepared query.
					if (! $update_stmt->execute()) {
						echo json_encode(
							array(
								"status" => "failed",
								"message" => "Something went wrong"
							)); 
					}
					else{
						echo json_encode(
							array(
								"status" => "success",
								"message" => "Remark updated successfully"
							)); 
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
						"status" => "failed",
						"message" => "Record not found"
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
            "message"=> "Please fill in all fields"
        )
    ); 
}
?>
