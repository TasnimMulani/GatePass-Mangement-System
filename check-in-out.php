<?php
// check-in-out.php - API endpoint for check-in/check-out
session_start();
include('app/config/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$passNumber = mysqli_real_escape_string($conn, $_POST['pass_number']);
$action = $_POST['action']; // 'check_in' or 'check_out'

// Validate action
if (!in_array($action, ['check_in', 'check_out'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Get pass ID
$query = mysqli_query($conn, "SELECT id, full_name FROM passes WHERE pass_number = '$passNumber'");
if (mysqli_num_rows($query) == 0) {
    echo json_encode(['success' => false, 'message' => 'Pass not found']);
    exit;
}

$pass = mysqli_fetch_assoc($query);
$passId = $pass['id'];

// Insert visitor log
$sql = "INSERT INTO visitor_logs (pass_id, action, timestamp) VALUES ($passId, '$action', NOW())";
$result = mysqli_query($conn, $sql);

if ($result) {
    $actionText = ($action == 'check_in') ? 'checked in' : 'checked out';
    echo json_encode([
        'success' => true,
        'message' => $pass['full_name'] . ' ' . $actionText . ' successfully'  
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
