<?php
// view-pass-detail.php
session_start();
include('db.php');
// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
} else {
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="container-fluid">
    <?php
    $pass_id = intval($_GET['id']);
    $ret = mysqli_query($conn, "select * from passes where id='$pass_id'");
    $cnt = 1;
    while ($row = mysqli_fetch_array($ret)) {
    ?>
    <h1 class="h3 mb-4 text-gray-800">View Pass #<?php echo $row['pass_number']; ?></h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4" id="printableArea">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pass Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Pass Number</th>
                            <td><?php echo $row['pass_number']; ?></td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td><?php echo $row['category']; ?></td>
                        </tr>
                        <tr>
                            <th>Full Name</th>
                            <td><?php echo $row['full_name']; ?></td>
                        </tr>
                         <tr>
                            <th>Contact Number</th>
                            <td><?php echo $row['contact_number']; ?></td>
                        </tr>
                        <tr>
                            <th>Email Address</th>
                            <td><?php echo $row['email']; ?></td>
                        </tr>
                         <tr>
                            <th>Identity Type</th>
                            <td><?php echo $row['identity_type']; ?></td>
                        </tr>
                        <tr>
                            <th>Identity Card Number</th>
                            <td><?php echo $row['identity_card_no']; ?></td>
                        </tr>
                        <tr>
                            <th>From Date</th>
                            <td><?php echo $row['from_date']; ?></td>
                        </tr>
                        <tr>
                            <th>To Date</th>
                            <td><?php echo $row['to_date']; ?></td>
                        </tr>
                        <tr>
                            <th>Reason for Pass</th>
                            <td><?php echo $row['reason']; ?></td>
                        </tr>
                         <tr>
                            <th>Pass Creation Date</th>
                            <td><?php echo $row['pass_creation_date']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <button class="btn btn-primary btn-lg" onclick="printPass()"><i class="fa fa-print"></i> Print Pass</button>
        </div>
    </div>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
<?php } ?>
