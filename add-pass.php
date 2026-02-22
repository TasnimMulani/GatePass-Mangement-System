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
    <p class="page-subtitle">Capture photo, scan ID with AI-powered OCR, or enter details manually</p>
</div>

<!-- Photo Capture Section -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-camera"></i> Visitor Photo</h3>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="margin-bottom: 0; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <div class="glass-inset" style="padding: var(--spacing-md); border-radius: var(--radius-md); background: rgba(0,0,0,0.2);">
                <button type="button" class="btn btn-primary w-100" onclick="startCamera()">
                    <i class="fas fa-camera"></i> Start Camera
                </button>
                <div id="camera-section" style="display: none; margin-top: 1rem; text-align: center;">
                    <video id="webcam-video" autoplay style="width: 100%; max-width: 100%; border-radius: 8px; border: 1px solid var(--glass-border);"></video>
                    <canvas id="webcam-canvas" style="display: none;"></canvas>
                    <button type="button" class="btn btn-success mt-3 w-100" id="capture-photo-btn" onclick="capturePhoto()" disabled>
                        <i class="fas fa-camera"></i> Take Photo
                    </button>
                </div>
            </div>
            <div class="glass-inset" style="padding: var(--spacing-md); border-radius: var(--radius-md); background: rgba(0,0,0,0.2); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px;">
                <img id="photo-preview" src="" alt="Photo Preview" style="display: none; max-width: 100%; max-height: 250px; border-radius: 8px; border: 2px solid var(--accent-primary); box-shadow: var(--glass-shadow);">
                <div id="photo-placeholder" style="color: var(--text-secondary); text-align: center;">
                    <i class="fas fa-user-circle" style="font-size: 5rem; opacity: 0.2; display: block; margin-bottom: 1rem;"></i>
                    <p>Photo Preview</p>
                </div>
                <div id="retake-section" style="display: none; margin-top: 1rem; width: 100%;">
                    <button type="button" class="btn btn-secondary w-100" onclick="retakePhoto()">
                        <i class="fas fa-redo"></i> Retake Photo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OCR Upload Section -->
<div class="ocr-upload-section" id="ocr-upload-section">
    <i class="fas fa-id-card"></i>
    <h4>Scan ID Card (AI-Powered OCR)</h4>
    <p>Click or drag & drop an ID card image to auto-fill details</p>
    <input type="file" id="id-card-upload" accept="image/*" style="display: none;">
</div>

<div id="ocr-loading" style="display: none; margin-bottom: var(--spacing-md);" class="alert alert-info">
    <span class="loading"></span> Processing ID card...
</div>

<div id="ocr-result" style="display: none;"></div>

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
                    <select class="form-control" name="identity_type" required>
                        <option value="">Choose Identity Type</option>
                        <option value="Voter Card">Voter Card</option>
                        <option value="PAN Card">PAN Card</option>
                        <option value="Adhar Card">Aadhaar Card</option>
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
<script src="public/js/webcam.js"></script>

<script>
// Update retake section visibility when photo is captured
const originalCapturePhoto = window.capturePhoto;
window.capturePhoto = function() {
    originalCapturePhoto();
    document.getElementById('retake-section').style.display = 'block';
};
</script>

<?php include 'app/includes/footer.php'; ?>
<?php } ?>
