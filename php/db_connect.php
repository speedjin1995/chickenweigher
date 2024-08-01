<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
$db = mysqli_connect("localhost", "u664110560_ccb", "Aa@111222333", "u664110560_ccb");
//$db = mysqli_connect("srv605.hstgr.io", "u664110560_ccb", "Aa@111222333", "u664110560_ccb");

if(mysqli_connect_errno()){
    echo 'Database connection failed with following errors: ' . mysqli_connect_error();
    die();
}
?>