<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");

$stmt = $db->prepare("SELECT * from weighing");
$stmt->execute();
$result = $stmt->get_result();
$message = array();

while($row = $result->fetch_assoc()){
	$message[] = array( 
        'id'=>$row['id'],
        'serial_no'=>$row['serial_no'],
        'group_no'=>$row['group_no'],
        'customer'=>$row['customer'],
        'supplier'=>$row['supplier'],
        'product'=>$row['product'],
        'driver_name'=>$row['driver_name'],
        'lorry_no'=>$row['lorry_no'],
        'farm_id'=>$row['farm_id'],
        'average_cage'=>$row['average_cage'],
        'average_bird'=>$row['average_bird'],
        'minimum_weight'=>$row['minimum_weight'],
        'maximum_weight'=>$row['maximum_weight'],
        'weight_data'=>$row['weight_data'],
        'created_datetime'=>$row['created_datetime'],
        'start_time'=>$row['start_time'],
        'end_time'=>$row['end_time']
    );
}

$stmt->close();
$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "message"=> $message
    )
);
?>
