<?php
// add-pass.php - Add Pass with OCR, Photo, QR, and Email Integration
session_start();
include('app/config/db.php');
include('app/lib/QRCodeGenerator.php');
include('app/lib/EmailNotification.php');

// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
} else {
    $message = '';
    if(isset($_POST['submit'])) {
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $fullname = mysqli_real_escape_string($conn, $_POST['full_name']);
        $contactno = mysqli_real_escape_string($conn, $_POST['contact_number']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $idtype = mysqli_real_escape_string($conn, $_POST['identity_type']);
        $idcardno = mysqli_real_escape_string($conn, $_POST['identity_card_no']);
        $fromdate = $_POST['from_date'];
        $todate = $_POST['to_date'];
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        $passnumber = mt_rand(100000000, 999999999);
        
        // Handle photo upload
        $photoPath = null;
        if (!empty($_POST['photo_data'])) {
            $photoData = $_POST['photo_data'];
            $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
            $photoData = str_replace(' ', '+', $photoData);
            $decodedPhoto = base64_decode($photoData);
            
            $photoFilename = 'visitor_' . $passnumber . '.jpg';
            $photoPath = 'public/uploads/visitor_photos/' . $photoFilename;
            file_put_contents($photoPath, $decodedPhoto);
        }
        
        // Insert pass
        $sql = "INSERT INTO passes(pass_number, category, full_name, contact_number, email, identity_type, identity_card_no, from_date, to_date, reason, photo_path, status) 
                VALUES ('$passnumber', '$category', '$fullname', '$contactno', '$email', '$idtype', '$idcardno', '$fromdate', '$todate', '$reason', '$photoPath', 'Approved')";
        
        $query = mysqli_query($conn, $sql);

        if($query) {
            $passId = mysqli_insert_id($conn);
            
            // Generate QR Code
            $passData = [
                'pass_number' => $passnumber,
                'full_name' => $fullname,
                'from_date' => $fromdate,
                'to_date' => $todate,
                'category' => $category
            ];
            
            $qrGenerator = new QRCodeGenerator();
            $qrData = QRCodeGenerator::getPassQRData($passData);
            $qrPath = $qrGenerator->generate($qrData, 'qr_' . $passnumber);
            
            // Update with QR code path
            if ($qrPath) {
                mysqli_query($conn, "UPDATE passes SET qr_code_path = '$qrPath' WHERE id = $passId");
            }
            
            // Send email notification
            if (!empty($email)) {
                $emailNotif = new EmailNotification();
                $passDataFull = array_merge($passData, [
                    'identity_type' => $idtype,
                    'identity_card_no' => $idcardno
                ]);
                $emailSent = $emailNotif->sendPassCreatedEmail($passDataFull, $email);
                
                if ($emailSent) {
                    mysqli_query($conn, "UPDATE passes SET email_sent = 1 WHERE id = $passId");
                }
            }
            
            $message = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Pass created successfully! 
                        Pass number: <strong>$passnumber</strong>
                        " . (!empty($email) ? "<br><small>Email notification sent to $email</small>" : "") . "
                        </div>";
        } else {
            $message = "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Something went wrong. Please try again.</div>";
        }
    }
?>
<?php include 'app/includes/header.php'; ?>
<?php include 'app/includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">Add Gate Pass</h1>
    <p class="page-subtitle">Scan ID card with AI-powered OCR to auto-fill details</p>
</div>

<!-- OCR Upload Section (Primary Focus) -->
<div class="card mb-4" style="border: 2px dashed var(--accent-primary); background: rgba(99, 102, 241, 0.05);">
    <div class="card-body">
        <div class="ocr-upload-section" id="ocr-upload-section" style="margin-bottom: 0; border: none; background: transparent;">
            <i class="fas fa-id-card" style="font-size: 4rem; color: var(--accent-primary); margin-bottom: 1.5rem;"></i>
            <h2 class="h3 mb-3">Scan Identity Document</h2>
            <p class="text-secondary mb-4">Upload or drag & drop Aadhar, PAN, Voter ID, or Driving License for instant auto-fill</p>
            <div class="d-flex justify-content-center" style="gap: 1rem;">
                <button type="button" class="btn btn-primary btn-lg">
                    <i class="fas fa-upload"></i> Choose Image
                </button>
            </div>
            <input type="file" id="id-card-upload" accept="image/*" style="display: none;">
        </div>
    </div>
</div>

<div id="ocr-loading" style="display: none; margin-bottom: var(--spacing-md);" class="alert alert-info">
    <span class="loading"></span> <strong id="ocr-status-text">Processing ID card with AI...</strong>
</div>

<div id="ocr-result" style="margin-bottom: var(--spacing-md);"></div>

<!-- Pass Details Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gate Pass Details</h3>
    </div>
    <div class="card-body">
        <?php echo $message; ?>
        <form method="post">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="contact_number" class="form-label">Contact Number *</label>
                    <input type="text" class="form-control" id="contact_number" name="contact_number" required pattern="[0-9]{10}" placeholder="10-digit number">
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="optional">
                </div>
                <div class="form-group">
                    <label for="identity_type" class="form-label">Identity Type *</label>
                    <select class="form-control" id="identity_type" name="identity_type" required>
                        <option value="">Choose Identity Type</option>
                        <option value="Voter ID">Voter ID</option>
                        <option value="PAN Card">PAN Card</option>
                        <option value="Aadhar Card">Aadhaar Card</option>
                        <option value="Driving License">Driving License</option>
                        <option value="Passport">Passport</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="identity_card_no" class="form-label">Identity Card Number *</label>
                    <input type="text" class="form-control" id="identity_card_no" name="identity_card_no" required>
                </div>
                <div class="form-group">
                    <label for="category" class="form-label">Category *</label>
                    <select class="form-control" name="category" required>
                        <option value="">Choose Category</option>
                        <option value="Visitor">Visitor</option>
                        <option value="Employee">Employee</option>
                        <option value="Student">Student</option>
                        <option value="Vendor">Vendor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="from_date" class="form-label">From Date *</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" required>
                </div>
                <div class="form-group">
                    <label for="to_date" class="form-label">To Date *</label>
                    <input type="date" class="form-control" id="to_date" name="to_date" required>
                </div>
            </div>
            <div class="form-group">
                <label for="reason" class="form-label">Reason *</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Purpose of visit..."></textarea>
            </div>
            
            <!-- Hidden input for photo data -->
            <input type="hidden" id="photo-data" name="photo_data" value="">
            
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Pass
            </button>
        </form>
    </div>
</div>

<!-- Tesseract.js for OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js"></script>
<script src="public/js/ocr.js"></script>

<?php include 'app/includes/footer.php'; ?>
<?php } ?>
