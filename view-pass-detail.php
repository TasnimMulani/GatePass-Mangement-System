<?php
// view-pass-detail.php
session_start();
include('app/config/db.php');
// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
} else {
?>
<?php include 'app/includes/header.php'; ?>
<?php include 'app/includes/sidebar.php'; ?>

<?php
$pass_id = intval($_GET['id']);
$ret = mysqli_query($conn, "select * from passes where id='$pass_id'");
while ($row = mysqli_fetch_array($ret)) {
?>

<div class="page-header">
    <h1 class="page-title">View Pass #<?php echo $row['pass_number']; ?></h1>
    <p class="page-subtitle">Detailed information for this gate pass</p>
</div>

<div class="stats-grid" style="grid-template-columns: 2fr 1fr;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pass Details</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
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
                        <td><?php echo date('M d, Y', strtotime($row['from_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>To Date</th>
                        <td><?php echo date('M d, Y', strtotime($row['to_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>Reason for Pass</th>
                        <td><?php echo $row['reason']; ?></td>
                    </tr>
                        <tr>
                        <th>Pass Creation Date</th>
                        <td><?php echo date('M d, Y H:i:s', strtotime($row['pass_creation_date'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="text-align: center;">
        <div class="card-header">
            <h3 class="card-title">Actions</h3>
        </div>
        <div class="card-body">
            <?php if($row['photo_path']): ?>
                <img src="<?php echo $row['photo_path']; ?>" alt="Visitor Photo" style="width: 100%; max-width: 200px; border-radius: var(--radius-md); margin-bottom: var(--spacing-md); border: 2px solid var(--accent-primary);">
            <?php endif; ?>
            
            <button class="btn btn-primary w-100 mb-3" onclick="printPass()">
                <i class="fas fa-print"></i> Print Pass
            </button>
            <a href="edit-pass-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary w-100 mb-3">
                <i class="fas fa-edit"></i> Edit Pass
            </a>
            <a href="manage-passes.php" class="btn btn-secondary w-100">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<?php } ?>

<script>
function printPass() {
    window.print();
}
</script>

<?php include 'app/includes/footer.php'; ?>
<?php } ?>
