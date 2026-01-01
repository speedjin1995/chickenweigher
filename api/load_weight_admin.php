<?php
require_once 'db_connect.php';
session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");

// ✅ Get pagination parameters (with safe defaults)
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;

$offset = ($page - 1) * $limit;

// ==============================
// Filters
// ==============================
$po_no    = $_GET['po_no'] ?? '';
$vehicle  = $_GET['vehicle'] ?? '';
$customer = $_GET['customer'] ?? '';
$farm     = $_GET['farm'] ?? '';
$start    = $_GET['start'] ?? '';
$end      = $_GET['end'] ?? '';

// ==============================
// Build WHERE conditions
// ==============================
$where = [];
$params = [];
$types = "";

// Mandatory conditions
$where[] = "deleted = '0'";
$where[] = "status = 'Complete'";

// Optional filters
if ($po_no != '') {
    $where[] = "po_no LIKE ?";
    $params[] = "%$po_no%";
    $types .= "s";
}

if ($vehicle != '') {
    $where[] = "lorry_no LIKE ?";
    $params[] = "%$vehicle%";
    $types .= "s";
}

if ($customer != '') {
    $where[] = "customer LIKE ?";
    $params[] = "%$customer%";
    $types .= "s";
}

if ($farm != '') {
    $where[] = "farm_id IN (SELECT id FROM farms WHERE name LIKE ?)";
    $params[] = "%$farm%";
    $types .= "s";
}

if ($start !== '' && $end !== '') {
    // Convert millis → UTC DateTime
    $startUTC = new DateTime("@".($start/1000));
    $endUTC   = new DateTime("@".($end/1000));

    // Convert UTC → KL timezone
    $tz = new DateTimeZone("Asia/Kuala_Lumpur");
    $startUTC->setTimezone($tz);
    $endUTC->setTimezone($tz);

    // Extract KL calendar dates
    $startDay = $startUTC->format("Y-m-d");
    $endDay   = $endUTC->format("Y-m-d");

    // Build full-day KL range
    $startDate = $startDay . " 00:00:00";
    $endDate   = $endDay   . " 23:59:59";
    
    // booking_date stored as DATETIME
    $where[] = "booking_date BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

$whereSql = "WHERE " . implode(" AND ", $where);

// ==============================
// Main Query
// ==============================
$sql = "
    SELECT *
    FROM weighing
    $whereSql
    ORDER BY booking_date DESC
    LIMIT ?, ?
";

$stmt = $db->prepare($sql);
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$message = array();

while ($row = $result->fetch_assoc()) {
    $farmId = $row['farm_id'];
    $farmName = '';

    // ✅ Fetch farm name safely
    if ($update_stmt = $db->prepare("SELECT name FROM farms WHERE id=?")) {
        $update_stmt->bind_param('s', $farmId);
        if ($update_stmt->execute()) {
            $result3 = $update_stmt->get_result();
            if ($row3 = $result3->fetch_assoc()) {
                $farmName = $row3['name'];
            }
        }
        $update_stmt->close();
    }

    $message[] = array(
        'id' => $row['id'],
        'serial_no' => $row['serial_no'],
        'booking_date' => $row['booking_date'],
        'po_no' => $row['po_no'],
        'group_no' => $row['group_no'],
        'customer' => $row['customer'],
        'supplier' => $row['supplier'],
        'product' => $row['product'],
        'driver_name' => $row['driver_name'],
        'driver_ic' => $row['driver_ic'],
        'driver_name2' => $row['driver_name2'],
        'driver_ic2' => $row['driver_ic2'],
        'lorry_no' => $row['lorry_no'],
        'farm_id' => $row['farm_id'],
        'farm_name' => $farmName,
        'average_cage' => $row['average_cage'],
        'average_bird' => $row['average_bird'],
        'minimum_weight' => $row['minimum_weight'],
        'maximum_weight' => $row['maximum_weight'],
        'total_cages_weight' => $row['total_cages_weight'],
        'number_of_cages' => $row['number_of_cages'],
        'total_cage' => $row['total_cage'],
        'max_crate' => $row['max_crate'],
        'weight_data' => $row['weight_data'],
        'cage_data' => $row['cage_data'],
        'created_datetime' => $row['created_datetime'],
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
        'grade' => $row['grade'],
        'gender' => $row['gender'],
        'house_no' => $row['house_no'],
        'remark' => $row['remark']
    );
}

$stmt->close();
$db->close();

// ✅ Return response
echo json_encode(array(
    "status" => "success",
    "message" => $message,
    "page" => $page,
    "limit" => $limit,
    "count" => count($message)
));
?>
