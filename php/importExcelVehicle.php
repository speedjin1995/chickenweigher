<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

$input = file_get_contents('php://input');

// Decode the JSON data received
$data = json_decode($input, true); // 'true' to decode as associative arrays

if ($data !== null) {
    $vehicleNumber =  $data['veh_number'];
    $driver = $data['driver'];
    $attendance1 = $data['attandence_1'];
    $attendance2 = $data['attandence_2'];

    if ($insert_stmt = $db->prepare("INSERT INTO vehicles (veh_number, driver, attandence_1, attandence_2) VALUES (?, ?, ?, ?)")) {
        $insert_stmt->bind_param('ssss', $vehicleNumber, $driver, $attendance1, $attendance2);
        
        // Execute the prepared query.
        if (! $insert_stmt->execute()) {
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