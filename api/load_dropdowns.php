<?php
require_once 'db_connect.php';

//$lots = $db->query("SELECT * FROM lots WHERE deleted = '0'");
$vehicles = $db->query("SELECT * FROM vehicles WHERE deleted = '0'");
$products = $db->query("SELECT * FROM products WHERE deleted = '0'");
$farms = $db->query("SELECT * FROM farms WHERE deleted = '0'");
$customers = $db->query("SELECT * FROM customers WHERE deleted = '0'");
$suppliers = $db->query("SELECT * FROM supplies WHERE deleted = '0'");
$grades = $db->query("SELECT * FROM grades WHERE deleted = '0'");
$transporters = $db->query("SELECT * FROM `transporters` WHERE deleted = '0'");

$data1 = array();
$data2 = array();
$data3 = array();
$data4 = array();
$data5 = array();
$data6 = array();
$data7 = array();
$data9 = array();

/*while($row1=mysqli_fetch_assoc($lots)){
    $data1[] = array( 
        'id'=>$row1['id'],
        'lots_no'=>$row1['lots_no']
    );
}*/

while($row2=mysqli_fetch_assoc($vehicles)){
    $data2[] = array( 
        'id'=>$row2['id'],
        'veh_number'=>$row2['veh_number'],
        'driver'=>$row2['driver'],
        'attandence_1'=>$row2['attandence_1'],
        'attandence_2'=>$row2['attandence_2']
    );
}

while($row3=mysqli_fetch_assoc($products)){
    $data3[] = array( 
        'id'=>$row3['id'],
        'product_name'=>$row3['product_name']
    );
}

while($row4=mysqli_fetch_assoc($farms)){
    $data4[] = array( 
        'id'=>$row4['id'],
        'name'=>$row4['name']
    );
}

while($row5=mysqli_fetch_assoc($customers)){
    $data5[] = array( 
        'id'=>$row5['id'],
        'customer_name'=>$row5['customer_name']
    );
}

while($row6=mysqli_fetch_assoc($suppliers)){
    $data6[] = array( 
        'id'=>$row6['id'],
        'supplier_name'=>$row6['supplier_name']
    );
}

while($row7=mysqli_fetch_assoc($grades)){
    $data7[] = array( 
        'id'=>$row7['id'],
        'units'=>$row7['units']
    );
}

while($row9=mysqli_fetch_assoc($transporters)){
    $data9[] = array( 
        'id'=>$row9['id'],
        'transporter_name'=>$row9['transporter_name']
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