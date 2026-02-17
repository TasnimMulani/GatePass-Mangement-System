<?php
// logout.php
session_start();
include('app/config/db.php');

// Destroy session and redirect to login
session_unset();
session_destroy();
header("location:index.php");
exit;
?>
