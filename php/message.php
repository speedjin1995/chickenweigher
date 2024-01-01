<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['keyCode'], $_POST['englishDecs'], $_POST['chineseDecs'])){
	$keyCode = filter_input(INPUT_POST, 'keyCode', FILTER_SANITIZE_STRING);
	$englishDecs = filter_input(INPUT_POST, 'englishDecs', FILTER_SANITIZE_STRING);
	$chineseDecs = filter_input(INPUT_POST, 'chineseDecs', FILTER_SANITIZE_STRING);

    if($_POST['keyId'] != null && $_POST['keyId'] != ''){
        if ($update_stmt = $db->prepare("UPDATE message_resource SET message_key_code=?, en=?, ch=? WHERE id=?")) {
            $update_stmt->bind_param('ssss', $keyCode, $englishDecs, $chineseDecs, $_POST['keyId']);
            
            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                echo '<script type="text/javascript">alert("Somethings wrong");';
                echo 'location.href = "../message.php";</script>';  
            }
            else{
                echo '<script type="text/javascript">alert("Updated successfully");';
                echo 'location.href = "../message.php";</script>';   
            }
        }
    }
    else{
        if ($insert_stmt = $db->prepare("INSERT INTO message_resource (message_key_code, en, ch) VALUES (?, ?, ?)")) {
            $insert_stmt->bind_param('sss', $keyCode, $englishDecs, $chineseDecs);
            
            // Execute the prepared query.
            if (! $insert_stmt->execute()) {
                echo '<script type="text/javascript">alert("Somethings wrong");';
                echo 'location.href = "../message.php";</script>';  
            }
            else{
                echo '<script type="text/javascript">alert("Added successfully");';
                echo 'location.href = "../message.php";</script>';   
            }
        }
    }
}
else{
    echo '<script type="text/javascript">alert("Missing Attributes");';
    echo 'window.location.href = "../message.php";</script>';
}
?>