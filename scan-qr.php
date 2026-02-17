<?php
// scan-qr.php - QR Code Scanner Page for Check-in/Check-out
session_start();
include('app/config/db.php');

// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
    exit;
}
?>
<?php include 'app/includes/header.php'; ?>
<?php include 'app/includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">QR Code Scanner</h1>
    <p class="page-subtitle">Scan visitor's QR code for quick check-in/check-out</p>
</div>

<div class="card">
    <div class="card-body" style="text-align: center;">
        <div id="qr-reader" style="width: 100%; max-width: 600px; margin: 0 auto;"></div>
        
        <div id="scan-result" style="margin-top: 2rem; display: none;">
            <div class="alert alert-success">
                <h4 id="visitor-name"></h4>
                <p id="pass-details"></p>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button class="btn btn-success" onclick="checkIn()">
                    <i class="fas fa-sign-in-alt"></i> Check In
                </button>
                <button class="btn btn-danger" onclick="checkOut()">
                    <i class="fas fa-sign-out-alt"></i> Check Out
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include HTML5 QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let currentPassNumber = null;

function onScanSuccess(decodedText, decodedResult) {
    // Parse QR code data
    const lines = decodedText.split('\n');
    let passNumber = '';
    let name = '';
    let validity = '';
    
    lines.forEach(line => {
        if (line.includes('Pass#:')) {
            passNumber = line.split(':')[1].trim();
        } else if (line.includes('Name:')) {
            name = line.split(':')[1].trim();
        } else if (line.includes('Valid:')) {
            validity = line.split(':')[1].trim();
        }
    });
    
    if (passNumber) {
        currentPassNumber = passNumber;
        document.getElementById('visitor-name').textContent = name;
        document.getElementById('pass-details').innerHTML = `
            Pass Number: <strong>${passNumber}</strong><br>
            Validity: ${validity}
        `;
        document.getElementById('scan-result').style.display = 'block';
        
        // Stop scanning
        html5QrcodeScanner.clear();
    }
}

function onScanError(errorMessage) {
    // Handle scan error
    console.log(errorMessage);
}

// Initialize QR Scanner
const html5QrcodeScanner = new Html5QrcodeScanner(
    "qr-reader", 
    { 
        fps: 10, 
        qrbox: 250,
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true
        }
    }
);
html5QrcodeScanner.render(onScanSuccess, onScanError);

function checkIn() {
    if (!currentPassNumber) return;
    
    fetch('check-in-out.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `pass_number=${currentPassNumber}&action=check_in`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Check-in successful!', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification(data.message || 'Check-in failed', 'danger');
        }
    });
}

function checkOut() {
    if (!currentPassNumber) return;
    
    fetch('check-in-out.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `pass_number=${currentPassNumber}&action=check_out`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Check-out successful!', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification(data.message || 'Check-out failed', 'danger');
        }
    });
}
</script>

<?php include 'app/includes/footer.php'; ?>
