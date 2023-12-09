<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}
else{
    $userId = $_SESSION['userID'];
}

$input = file_get_contents('php://input');

// Decode the JSON data received
$data = json_decode($input, true); // 'true' to decode as associative arrays

if ($data !== null) {
    $code =  $data['code'];
    $name = $data['name'];
    $reg_no = $data['reg_no'];
	$address = $data['address'];
    $address2 = $data['address2'];
    $address3 = $data['address3'];
    $address4 = $data['address4'];
    $states = $data['states'];
    $phone = $data['phone'];
    $email = $data['pic'];

    if ($insert_stmt = $db->prepare("INSERT INTO supplies (supplier_code, reg_no, supplier_name, supplier_address, supplier_address2, supplier_address3, supplier_address4, states, supplier_phone, pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $insert_stmt->bind_param('ssssssssss', $code, $reg_no, $name, $address, $address2, $address3, $address4, $states, $phone, $email);
            
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