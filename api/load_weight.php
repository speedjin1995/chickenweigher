<?php
require_once 'db_connect.php';

/* ============================================
   Read input
============================================ */
$post = json_decode(file_get_contents('php://input'), true);
$staffId = $post['userId'] ?? '';

/* ============================================
   Load user allowed farms
============================================ */
$values = [];
if ($staffId !== '') {
    $u = $db->prepare("SELECT farms FROM users WHERE id=?");
    $u->bind_param("s", $staffId);
    $u->execute();
    $ur = $u->get_result();
    if ($row = $ur->fetch_assoc()) {
        if (!empty($row['farms'])) {
            $values = json_decode($row['farms'], true);
        }
    }
    $u->close();
}

/* ============================================
   Pagination
============================================ */
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 20;
$offset = ($page - 1) * $limit;

/* ============================================
   Filters
============================================ */
$po_no    = $_GET['po_no'] ?? '';
$vehicle  = $_GET['vehicle'] ?? '';
$customer = $_GET['customer'] ?? '';
$farm     = $_GET['farm'] ?? '';
$startMs  = $_GET['start'] ?? '';
$endMs    = $_GET['end'] ?? '';

/* ============================================
   Convert millis → KL calendar days
============================================ */
$startDate = '';
$endDate = '';

if ($startMs !== '' && $endMs !== '') {
    $tz = new DateTimeZone("Asia/Kuala_Lumpur");

    $s = new DateTime("@".($startMs/1000));
    $e = new DateTime("@".($endMs/1000));

    $s->setTimezone($tz);
    $e->setTimezone($tz);

    $startDate = $s->format("Y-m-d") . " 00:00:00";
    $endDate   = $e->format("Y-m-d") . " 23:59:59";
}

/* ============================================
   Build WHERE
============================================ */
$where = [];
$params = [];
$types = "";

// base
$where[] = "deleted='0'";
$where[] = "status='Complete'";

// filters
if ($po_no !== '') {
    $where[] = "po_no LIKE ?";
    $params[] = "%$po_no%";
    $types .= "s";
}

if ($vehicle !== '') {
    $where[] = "lorry_no LIKE ?";
    $params[] = "%$vehicle%";
    $types .= "s";
}

if ($customer !== '') {
    $where[] = "customer LIKE ?";
    $params[] = "%$customer%";
    $types .= "s";
}

if ($farm !== '') {
    $where[] = "farm_id IN (SELECT id FROM farms WHERE name LIKE ?)";
    $params[] = "%$farm%";
    $types .= "s";
}

if ($startDate !== '' && $endDate !== '') {
    $where[] = "booking_date BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

/* ============================================
   Staff access control (SQL level)
============================================ */
$farmPlaceholders = '';
if (!empty($values)) {
    $farmPlaceholders = implode(',', array_fill(0, count($values), '?'));
}

$staffSql = "(JSON_CONTAINS(weighted_by, ?)";

$params[] = json_encode((string)$staffId);
$types .= "s";

if (!empty($farmPlaceholders)) {
    $staffSql .= " OR farm_id IN ($farmPlaceholders)";
    foreach ($values as $f) {
        $params[] = $f;
        $types .= "s";
    }
}
$staffSql .= ")";

$where[] = $staffSql;

/* ============================================
   Final SQL
============================================ */
$whereSql = "WHERE " . implode(" AND ", $where);

$sql = "
    SELECT *
    FROM weighing
    $whereSql
    ORDER BY booking_date DESC
    LIMIT ?, ?
";

$params[] = $offset;
$params[] = $limit;
$types .= "ii";

/* ============================================
   Execute
============================================ */
$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

/* ============================================
   Build response
============================================ */
$message = [];

while ($row = $result->fetch_assoc()) {
    $farmName = '';

    if ($f = $db->prepare("SELECT name FROM farms WHERE id=?")) {
        $f->bind_param("s", $row['farm_id']);
        $f->execute();
        $fr = $f->get_result();
        if ($r = $fr->fetch_assoc()) {
            $farmName = $r['name'];
        }
        $f->close();
    }

    $row['farm_name'] = $farmName;
    $message[] = $row;
}

$stmt->close();
$db->close();

/* ============================================
   Output
============================================ */
echo json_encode([
    "status" => "success",
    "message" => $message,
    "page" => $page,
    "limit" => $limit,
    "count" => count($message)
]);
