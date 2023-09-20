<?php
## Database configuration
require_once 'db_connect.php';

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($db,$_POST['search']['value']); // Search value

## Search 
$searchQuery = " ";

if($_POST['fromDate'] != null && $_POST['fromDate'] != ''){
  $fromDate = new DateTime($_POST['fromDate']);
  $fromDateTime = date_format($fromDate,"Y-m-d H:i:s");
  $searchQuery = " and created_datetime >= '".$fromDateTime."'";
}

if($_POST['toDate'] != null && $_POST['toDate'] != ''){
  $toDate = new DateTime($_POST['toDate']);
  $toDateTime = date_format($toDate,"Y-m-d H:i:s");
	$searchQuery .= " and created_datetime <= '".$toDateTime."'";
}

if($_POST['farm'] != null && $_POST['farm'] != '' && $_POST['farm'] != '-'){
	$searchQuery .= " and farm_id = '".$_POST['farm']."'";
}

if($_POST['customer'] != null && $_POST['customer'] != '' && $_POST['customer'] != '-'){
	$searchQuery .= " and customer = '".$_POST['customer']."'";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from weighing WHERE deleted = '0'");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from weighing WHERE deleted = '0' AND start_time IS NOT NULL AND end_time IS NOT NULL".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select * FROM weighing WHERE deleted = '0' AND start_time IS NOT NULL AND end_time IS NOT NULL".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;
$done = 0;
$inprogress = 0;
$total = 0;

while($row = mysqli_fetch_assoc($empRecords)) {
  $weighted_by = '';
  $created_datetime = '';
    
  if ($update_stmt = $db->prepare("SELECT * FROM users WHERE id=?")) {
    $userId = $row['weighted_by'];
    $update_stmt->bind_param('s', $userId);
    
    // Execute the prepared query.
    if ($update_stmt->execute()) {
      $result = $update_stmt->get_result();
        
      if ($row2 = $result->fetch_assoc()) {
        $weighted_by = $row2['name'];
      }
    }
  }

  if($row['created_datetime'] != null || $row['created_datetime'] != ''){
    $dateInt = new DateTime($row['created_datetime']);
    $created_datetime = date_format($dateInt,"d/m/Y H:i:s A");
  }

  if($row['start_time'] != null && $row['end_time'] != null){
    $done++;
    $total++;
  }
  else {
    $inprogress++;
    $total++;
  }

  $data[] = array( 
    "no"=>$counter,
    "id"=>$row['id'],
    "serial_no"=>$row['serial_no'],
    "group_no"=>$row['group_no'],
    "customer"=>$row['customer'],
    "supplier"=>$row['supplier'],
    "product"=>$row['product'],
    "driver_name"=>$row['driver_name'],
    "lorry_no"=>$row['lorry_no'],
    "farm_id"=>$row['farm_id'],
    "average_cage"=>$row['average_cage'],
    "average_bird"=>$row['average_bird'],
    "minimum_weight"=>$row['minimum_weight'],
    "maximum_weight"=>$row['maximum_weight'],
    "weight_data"=>json_decode($row['weight_data'], true),
    "created_datetime"=>$created_datetime,
    "weighted_by"=>$weighted_by,
    "start_time"=>$row['start_time'],
    "end_time"=>$row['end_time']
  );

  $counter++;
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data,
  "done" => $done,
  "inprogress" => $inprogress,
  "total" => $total
);

echo json_encode($response);

?>