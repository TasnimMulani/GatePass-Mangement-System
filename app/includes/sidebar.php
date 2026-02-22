<!-- app/includes/sidebar.php -->
<div class="dashboard-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>GPMS</h2>
                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">AI-Driven System</p>
                </div>
                <button id="sidebarCollapse" class="btn btn-link text-white p-2" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="add-pass.php" class="sidebar-link <?php echo ($current_page == 'add-pass.php') ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Add Pass</span>
            </a>
            <a href="manage-passes.php" class="sidebar-link <?php echo ($current_page == 'manage-passes.php') ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>Manage Passes</span>
            </a>
            <a href="scan-qr.php" class="sidebar-link <?php echo ($current_page == 'scan-qr.php') ? 'active' : ''; ?>">
                <i class="fas fa-qrcode"></i>
                <span>Scan QR Code</span>
            </a>
            <a href="logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>
    <main class="main-content">
