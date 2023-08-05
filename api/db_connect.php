<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
$db = mysqli_connect("localhost", "u782565293_chickenweigher", "Aa@111222333", "u782565293_chickenweigher");

if(mysqli_connect_errno()){
    echo 'Database connection failed with following errors: ' . mysqli_connect_error();
    die();
}
?>