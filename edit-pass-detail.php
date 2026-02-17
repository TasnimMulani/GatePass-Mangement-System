<?php
// edit-pass-detail.php
session_start();
include('db.php');
// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
} else {
    $message = '';
    $pass_id = intval($_GET['id']);

    if(isset($_POST['submit'])) {
        $category = $_POST['category'];
        $fullname = $_POST['fullname'];
        $contactno = $_POST['contactno'];
        $email = $_POST['email'];
        $idtype = $_POST['idtype'];
        $idcardno = $_POST['idcardno'];
        $fromdate = $_POST['fromdate'];
        $todate = $_POST['todate'];
        $reason = $_POST['reason'];
        
        $sql = "UPDATE passes SET category='$category', full_name='$fullname', contact_number='$contactno', email='$email', identity_type='$idtype', identity_card_no='$idcardno', from_date='$fromdate', to_date='$todate', reason='$reason' WHERE id='$pass_id'";
        
        $query = mysqli_query($conn, $sql);

        if($query) {
            $message = "<div class='alert alert-success'>Pass details updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Something went wrong. Please try again.</div>";
        }
    }
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Pass</h1>
    <?php
    $ret = mysqli_query($conn, "select * from passes where id='$pass_id'");
    while ($row = mysqli_fetch_array($ret)) {
    ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Editing Pass #<?php echo $row['pass_number']; ?></h6>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required value="<?php echo $row['full_name']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label for="contactno" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactno" name="contactno" required pattern="[0-9]{10}" value="<?php echo $row['contact_number']; ?>">
                        </div>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['email']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label for="idtype" class="form-label">Identity Type</label>
                            <select class="form-select" name="idtype" required>
                                <option value="<?php echo $row['identity_type']; ?>"><?php echo $row['identity_type']; ?></option>
                                <option value="Voter Card">Voter Card</option>
                                <option value="PAN Card">PAN Card</option>
                                <option value="Adhar Card">Aadhaar Card</option>
                                <option value="Driving License">Driving License</option>
                                <option value="Passport">Passport</option>
                            </select>
                        </div>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="idcardno" class="form-label">Identity Card Number</label>
                            <input type="text" class="form-control" id="idcardno" name="idcardno" required value="<?php echo $row['identity_card_no']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="<?php echo $row['category']; ?>"><?php echo $row['category']; ?></option>
                                <option value="Visitor">Visitor</option>
                                <option value="Employee">Employee</option>
                                <option value="Student">Student</option>
                                <option value="Vendor">Vendor</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fromdate" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="fromdate" name="fromdate" required value="<?php echo $row['from_date']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label for="todate" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="todate" name="todate" required value="<?php echo $row['to_date']; ?>">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" required><?php echo $row['reason']; ?></textarea>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Update Pass</button>
            </form>
        </div>
    </div>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
<?php } ?>
