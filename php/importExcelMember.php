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
    $username =  $data['username'];
    $name = $data['name'];
    $roleCode = $data['role_code'];
    $farms = $data['farms'];

    $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));
    $password = '123456';
    $password = hash('sha512', $password . $random_salt);

    if ($insert_stmt = $db->prepare("INSERT INTO users (username, name, password, salt, created_by, role_code, farms) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
        $insert_stmt->bind_param('sssssss', $username, $name, $password, $random_salt, $userId, $roleCode, $farms);
        
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