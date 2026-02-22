<?php
// manage-passes.php - Manage All Passes
session_start();
include('app/config/db.php');
// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
} else {
?>
<?php include 'app/includes/header.php'; ?>
<?php include 'app/includes/sidebar.php'; ?>

<div class="page-header">
    <?php
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $title = "Manage Passes";
    $subtitle = "View, edit, and manage all gate passes";
    $where = "";

    if ($filter == 'today') {
        $title = "Today's Passes";
        $subtitle = "Passes created on " . date('M d, Y');
        $where = "WHERE DATE(pass_creation_date) = CURDATE()";
    } elseif ($filter == 'week') {
        $title = "Weekly Passes";
        $subtitle = "Passes created in the last 7 days";
        $where = "WHERE DATE(pass_creation_date) >= DATE(NOW()) - INTERVAL 7 DAY";
    }
    ?>
    <h1 class="page-title"><?php echo $title; ?></h1>
    <p class="page-subtitle"><?php echo $subtitle; ?></p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Gate Passes</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pass Number</th>
                        <th>Full Name</th>
                        <th>Contact</th>
                        <th>Category</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $ret = mysqli_query($conn, "SELECT * FROM passes $where ORDER BY pass_creation_date DESC");
                    $cnt = 1;
                    while ($row = mysqli_fetch_array($ret)) {
                ?>
                    <tr>
                        <td><?php echo $cnt;?></td>
                        <td><?php echo $row['pass_number'];?></td>
                        <td><?php echo $row['full_name'];?></td>
                        <td><?php echo $row['contact_number'];?></td>
                        <td><?php echo $row['category'];?></td>
                        <td><?php echo date('M d, Y', strtotime($row['pass_creation_date']));?></td>
                        <td>
                            <span class="badge badge-<?php echo $row['status'] == 'Pending' ? 'warning' : 'success'; ?>">
                                <?php echo $row['status'];?>
                            </span>
                        </td>
                        <td>
                            <a href="view-pass-detail.php?id=<?php echo $row['id'];?>" class="btn btn-primary" style="margin-right: 0.5rem;">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="edit-pass-detail.php?id=<?php echo $row['id'];?>" class="btn btn-secondary" style="margin-right: 0.5rem;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete-pass.php?id=<?php echo $row['id'];?>" class="btn btn-danger" onclick="return confirm('Do you really want to delete this pass?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php 
                    $cnt++;
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'app/includes/footer.php'; ?>
<?php } ?>
