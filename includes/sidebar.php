<!-- includes/sidebar.php -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>GPMS</h3>
        <small>Admin Panel</small>
    </div>
    <ul class="nav flex-column p-3">
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fa fa-tachometer-alt"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add-pass.php') ? 'active' : ''; ?>" href="add-pass.php">
                <i class="fa fa-plus-circle"></i>Add Pass
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage-passes.php') ? 'active' : ''; ?>" href="manage-passes.php">
                <i class="fa fa-tasks"></i>Manage Passes
            </a>
        </li>
        <li class="nav-item mt-auto">
            <a class="nav-link" href="logout.php">
                <i class="fa fa-sign-out-alt"></i>Logout
            </a>
        </li>
    </ul>
</div>
<div class="main-content">
