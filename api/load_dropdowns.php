<?php
require_once 'db_connect.php';

$lots = $db->query("SELECT * FROM lots WHERE deleted = '0'");
$vehicles = $db->query("SELECT * FROM vehicles WHERE deleted = '0'");
$products = $db->query("SELECT * FROM products WHERE deleted = '0'");
$packages = $db->query("SELECT * FROM packages WHERE deleted = '0'");
$customers = $db->query("SELECT * FROM customers WHERE deleted = '0'");
$suppliers = $db->query("SELECT * FROM supplies WHERE deleted = '0'");
$units = $db->query("SELECT * FROM units WHERE deleted = '0'");
$status = $db->query("SELECT * FROM `status` WHERE deleted = '0'");
$transporters = $db->query("SELECT * FROM `transporters` WHERE deleted = '0'");

$data1 = array();
$data2 = array();
$data3 = array();
$data4 = array();
$data5 = array();
$data6 = array();
$data7 = array();
$data8 = array();
$data9 = array();

while($row1=mysqli_fetch_assoc($lots)){
    $data1[] = array( 
        'id'=>$row['id'],
        'supplier_code'=>$row['supplier_code'],
        'supplier_name'=>$row['supplier_name'],
        'supplier_address'=>$row['supplier_address'].$row['supplier_address2'].$row['supplier_address3'].$row['supplier_address4'],
        'supplier_phone'=>$row['supplier_phone'],
        'pic'=>$row['pic'],
    );
}

while($row2=mysqli_fetch_assoc($vehicles)){

}

while($row3=mysqli_fetch_assoc($products)){

}

while($row4=mysqli_fetch_assoc($packages)){

}

while($row5=mysqli_fetch_assoc($customers)){

}

while($row6=mysqli_fetch_assoc($suppliers)){

}

while($row7=mysqli_fetch_assoc($units)){

}

while($row8=mysqli_fetch_assoc($status)){

}

while($row9=mysqli_fetch_assoc($transporters)){

}

?>