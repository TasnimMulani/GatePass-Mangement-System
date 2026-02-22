<?php
// edit-pass-detail.php
session_start();
include('app/config/db.php');
// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
} else {
    $message = '';
    $pass_id = intval($_GET['id']);

    if(isset($_POST['submit'])) {
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $contactno = mysqli_real_escape_string($conn, $_POST['contactno']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $idtype = mysqli_real_escape_string($conn, $_POST['idtype']);
        $idcardno = mysqli_real_escape_string($conn, $_POST['idcardno']);
        $fromdate = $_POST['fromdate'];
        $todate = $_POST['todate'];
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        
        $sql = "UPDATE passes SET category='$category', full_name='$fullname', contact_number='$contactno', email='$email', identity_type='$idtype', identity_card_no='$idcardno', from_date='$fromdate', to_date='$todate', reason='$reason' WHERE id='$pass_id'";
        
        $query = mysqli_query($conn, $sql);

        if($query) {
            $message = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Pass details updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Something went wrong. Please try again.</div>";
        }
    }
?>
<?php include 'app/includes/header.php'; ?>
<?php include 'app/includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">Edit Pass</h1>
    <p class="page-subtitle">Update visitor information and pass details</p>
</div>

<?php
$ret = mysqli_query($conn, "select * from passes where id='$pass_id'");
while ($row = mysqli_fetch_array($ret)) {
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editing Pass #<?php echo $row['pass_number']; ?></h3>
    </div>
    <div class="card-body">
        <?php echo $message; ?>
        <form method="post">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required value="<?php echo $row['full_name']; ?>">
                </div>
                <div class="form-group">
                    <label for="contactno" class="form-label">Contact Number</label>
                    <input type="text" class="form-control" id="contactno" name="contactno" required pattern="[0-9]{10}" value="<?php echo $row['contact_number']; ?>">
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['email']; ?>">
                </div>
                <div class="form-group">
                    <label for="idtype" class="form-label">Identity Type</label>
                    <select class="form-control" name="idtype" required>
                        <option value="<?php echo $row['identity_type']; ?>"><?php echo $row['identity_type']; ?></option>
                        <option value="Voter Card">Voter Card</option>
                        <option value="PAN Card">PAN Card</option>
                        <option value="Adhar Card">Aadhaar Card</option>
                        <option value="Driving License">Driving License</option>
                        <option value="Passport">Passport</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="idcardno" class="form-label">Identity Card Number</label>
                    <input type="text" class="form-control" id="idcardno" name="idcardno" required value="<?php echo $row['identity_card_no']; ?>">
                </div>
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-control" name="category" required>
                        <option value="<?php echo $row['category']; ?>"><?php echo $row['category']; ?></option>
                        <option value="Visitor">Visitor</option>
                        <option value="Employee">Employee</option>
                        <option value="Student">Student</option>
                        <option value="Vendor">Vendor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fromdate" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="fromdate" name="fromdate" required value="<?php echo $row['from_date']; ?>">
                </div>
                <div class="form-group">
                    <label for="todate" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="todate" name="todate" required value="<?php echo $row['to_date']; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="reason" class="form-label">Reason</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required><?php echo $row['reason']; ?></textarea>
            </div>
            
            <div class="d-flex" style="gap: 1rem; margin-top: var(--spacing-md);">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Pass
                </button>
                <a href="manage-passes.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php } ?>

<?php include 'app/includes/footer.php'; ?>
<?php } ?>
