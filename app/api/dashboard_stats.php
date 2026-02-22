<?php
// app/api/dashboard_stats.php
session_start();
include('../config/db.php');

header('Content-Type: application/json');

// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-13 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// 1. Stacked Bar Chart Data: Visitor vs Vendor vs Employee (Last 14 days by default)
$categories = ['Visitor', 'Vendor', 'Employee'];
$stackedData = [];

// Generate labels (dates)
$labels = [];
$current = strtotime($startDate);
$last = strtotime($endDate);
while ($current <= $last) {
    $labels[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

foreach ($categories as $cat) {
    $catData = [];
    foreach ($labels as $date) {
        $query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passes WHERE category = '$cat' AND DATE(pass_creation_date) = '$date'");
        $row = mysqli_fetch_assoc($query);
        $catData[] = (int)$row['count'];
    }
    $stackedData[] = [
        'label' => $cat,
        'data' => $catData
    ];
}

// 2. Heatmap-style Hourly Check-in Data
$hourlyData = array_fill(0, 24, 0);
$query = mysqli_query($conn, "
    SELECT HOUR(timestamp) as hour, COUNT(*) as count 
    FROM visitor_logs 
    WHERE action = 'check_in' 
    AND DATE(timestamp) BETWEEN '$startDate' AND '$endDate'
    GROUP BY HOUR(timestamp)
");
while ($row = mysqli_fetch_assoc($query)) {
    $hourlyData[(int)$row['hour']] = (int)$row['count'];
}

// 3. Current Stat card counts
$stats = [];
$stats['total'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM passes"));
$stats['today'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM passes WHERE DATE(pass_creation_date) = CURDATE()"));
$stats['week'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM passes WHERE DATE(pass_creation_date) >= DATE(NOW()) - INTERVAL 7 DAY"));

echo json_encode([
    'labels' => $labels,
    'stackedData' => $stackedData,
    'hourlyData' => $hourlyData,
    'stats' => $stats
]);
?>
