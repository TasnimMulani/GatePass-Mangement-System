<?php
// print-pass.php - Generate and display printable pass
session_start();
include('app/config/db.php');
include('app/lib/PDFPassGenerator.php');

// Check if pass ID is provided
if (!isset($_GET['id'])) {
    die('Pass ID not provided');
}

$passId = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch pass details
$query = mysqli_query($conn, "SELECT * FROM passes WHERE id = $passId");
if (mysqli_num_rows($query) == 0) {
    die('Pass not found');
}

$passData = mysqli_fetch_assoc($query);

// Generate PDF/HTML pass
$pdfGenerator = new PDFPassGenerator();
$passFile = $pdfGenerator->generatePass($passData);

// Redirect to generated file
header("Location: $passFile");
exit;
?>
