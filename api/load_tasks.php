<?php
require_once 'db_connect.php';

$post = json_decode(file_get_contents('php://input'), true);

$userId=$post['userId'];
$now = date("Y-m-d 00:00:00");

//$stmt = $db->prepare("SELECT * from weighing WHERE created_datetime >= ?");
$stmt = $db->prepare("SELECT * from weighing WHERE created_datetime >= ? AND weighted_by = ? AND start_time IS NULL AND end_time IS NULL AND `deleted` = '0'");
$stmt->bind_param('ss', $now, $userId);
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
        'end_time'=>$row['end_time'],
        'grade'=>$row['grade'],
        'gender'=>$row['gender'],
        'house_no'=>$row['house_no'],
        'remark'=>$row['remark']
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