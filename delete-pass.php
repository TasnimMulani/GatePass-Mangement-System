<?php
session_start();
include('app/config/db.php');

// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Check if pass exists before deleting (optional but good for feedback)
    $check_query = mysqli_query($conn, "SELECT pass_number FROM passes WHERE id = '$id'");
    
    if (mysqli_num_rows($check_query) > 0) {
        $result = mysqli_query($conn, "DELETE FROM passes WHERE id = '$id'");
        
        if ($result) {
            echo "<script>alert('Pass deleted successfully');</script>";
        } else {
            echo "<script>alert('Error: Could not delete pass');</script>";
        }
    } else {
        echo "<script>alert('Pass not found');</script>";
    }
}

echo "<script>window.location.href='manage-passes.php';</script>";
?>
