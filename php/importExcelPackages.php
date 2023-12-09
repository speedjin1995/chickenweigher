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
    $code =  $data['code'];
    $packages = $data['packages'];
	$address = $data['address'];
    $address2 = $data['address2'];
    $address3 = $data['address3'];
    $address4 = $data['address4'];
    $states = $data['states'];
    $supplier = $data['supplier'];

    if ($insert_stmt = $db->prepare("INSERT INTO farms (farms_code, name, address, address2, address3, address4, states, suppliers) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {
        $insert_stmt->bind_param('ssssssss', $code, $packages, $address, $address2, $address3, $address4, $states, $supplier);
        
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