<?php
require_once 'db_connect.php';

$lots = $db->query("SELECT * FROM lots WHERE deleted = '0'");
$vehicles = $db->query("SELECT * FROM vehicles WHERE deleted = '0'");
$products = $db->query("SELECT * FROM products WHERE deleted = '0'");
$packages = $db->query("SELECT * FROM packages WHERE deleted = '0'");
$customers = $db->query("SELECT * FROM customers WHERE deleted = '0'");
$suppliers = $db->query("SELECT * FROM supplies WHERE deleted = '0'");
$units = $db->query("SELECT * FROM units WHERE deleted = '0'");
$transporters = $db->query("SELECT * FROM `transporters` WHERE deleted = '0'");

$data1 = array();
$data2 = array();
$data3 = array();
$data4 = array();
$data5 = array();
$data6 = array();
$data7 = array();
$data9 = array();

while($row1=mysqli_fetch_assoc($lots)){
    $data1[] = array( 
        'id'=>$row['id'],
        'lots_no'=>$row['lots_no']
    );
}

while($row2=mysqli_fetch_assoc($vehicles)){
    $data2[] = array( 
        'id'=>$row['id'],
        'veh_number'=>$row['veh_number']
    );
}

while($row3=mysqli_fetch_assoc($products)){
    $data3[] = array( 
        'id'=>$row['id'],
        'product_name'=>$row['product_name']
    );
}

while($row4=mysqli_fetch_assoc($packages)){
    $data4[] = array( 
        'id'=>$row['id'],
        'packages'=>$row['packages']
    );
}

while($row5=mysqli_fetch_assoc($customers)){
    $data5[] = array( 
        'id'=>$row['id'],
        'customer_name'=>$row['customer_name']
    );
}

while($row6=mysqli_fetch_assoc($suppliers)){
    $data6[] = array( 
        'id'=>$row['id'],
        'supplier_name'=>$row['supplier_name']
    );
}

while($row7=mysqli_fetch_assoc($units)){
    $data7[] = array( 
        'id'=>$row['id'],
        'units'=>$row['units']
    );
}

while($row9=mysqli_fetch_assoc($transporters)){
    $data9[] = array( 
        'id'=>$row['id'],
        'transporter_name'=>$row['transporter_name']
    );
}

$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "groups"=> $data1, 
        "vehicles"=> $data2, 
        "products"=> $data3, 
        "farms"=> $data4, 
        "customers"=> $data5, 
        "suppliers"=> $data6, 
        "grades"=> $data7, 
        "drivers"=> $data9
    )
);
?>