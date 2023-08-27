<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM weight WHERE id=?")) {
        $update_stmt->bind_param('s', $id);
        
        // Execute the prepared query.
        if (! $update_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong"
                )); 
        }
        else{
            $result = $update_stmt->get_result();
            $message = array();
            
            while ($row = $result->fetch_assoc()) {
                $message['id'] = $row['id'];
                $message['status'] = $row['status'];
                $message['group_no'] = $row['group_no'];
                $message['customer'] = $row['customer'];
                $message['supplier'] = $row['supplier'];
                $message['product'] = $row['product'];
                $message['driver_name'] = $row['driver_name'];
                $message['lorry_no'] = $row['lorry_no'];
                $message['farm_id'] = $row['farm_id'];
                $message['grade'] = $row['grade'];
                $message['gender'] = $row['gender'];
                $message['house_no'] = $row['house_no'];
                $message['average_cage'] = $row['average_cage'];
                $message['average_bird'] = $row['average_bird'];
                $message['minimum_weight'] = $row['minimum_weight'];
                $message['maximum_weight'] = $row['maximum_weight'];
                $message['weighted_by'] = $row['weighted_by'];
                $message['remark'] = $row['remark'];
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message
                ));   
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Attribute"
            )); 
}
?>