<?php
// dashboard.php - AI-Enhanced Dashboard
session_start();
include('app/config/db.php');
include('app/lib/AiInsights.php');

// Check if user is logged in
if (strlen($_SESSION['admin_id'] == 0)) {
    header("location:logout.php");
} else {
    
// Initialize AI Insights
$aiInsights = new AiInsights($conn);
$trafficInsights = $aiInsights->getTrafficInsights();
$peakHours = $aiInsights->getPeakHours();

?>
<?php include 'app/includes/header.php'; ?>
<?php include 'app/includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">AI-powered analytics and insights</p>
</div>

<!-- AI Insights Section -->
<div class="ai-insights">
    <div class="ai-insights-header">
        <i class="fas fa-brain"></i>
        <h3 class="ai-insights-title">Smart Insights</h3>
    </div>
    
    <?php foreach($trafficInsights as $insight): ?>
        <div class="insight-item">
            <div class="insight-icon <?php echo $insight['type']; ?>">
                <?php if($insight['type'] == 'alert'): ?>
                    <i class="fas fa-exclamation-triangle"></i>
                <?php elseif($insight['type'] == 'warning'): ?>
                    <i class="fas fa-info-circle"></i>
                <?php else: ?>
                    <i class="fas fa-check-circle"></i>
                <?php endif; ?>
            </div>
            <div class="insight-content">
                <h4><?php echo $insight['title']; ?></h4>
                <p><?php echo $insight['message']; ?></p>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if(count($peakHours) > 0): ?>
        <div class="insight-item">
            <div class="insight-icon normal">
                <i class="fas fa-clock"></i>
            </div>
            <div class="insight-content">
                <h4>Peak Hours This Week</h4>
                <p>
                    <?php 
                    $hours = array_map(function($h) {
                        $hour = $h['hour'];
                        return ($hour % 12 ?: 12) . ($hour >= 12 ? ' PM' : ' AM') . ' (' . $h['count'] . ' passes)';
                    }, $peakHours);
                    echo implode(', ', $hours);
                    ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <!-- Total Passes Card -->
    <div class="stat-card">
        <div class="stat-label">Total Passes</div>
        <?php 
            $query = mysqli_query($conn, "SELECT id FROM passes");
            $count_total_passes = mysqli_num_rows($query);
        ?>
        <div class="stat-value"><?php echo $count_total_passes; ?></div>
        <i class="fas fa-id-card-alt stat-icon"></i>
    </div>

    <!-- Passes Created Today Card -->
    <div class="stat-card">
        <div class="stat-label">Passes Created Today</div>
        <?php 
            $query_today = mysqli_query($conn, "SELECT id FROM passes where date(pass_creation_date) = CURDATE()");
            $count_today_passes = mysqli_num_rows($query_today);
        ?>
        <div class="stat-value"><?php echo $count_today_passes; ?></div>
        <i class="fas fa-calendar-day stat-icon"></i>
    </div>
    
    <!-- Passes in Last 7 Days Card -->
    <div class="stat-card">
        <div class="stat-label">Passes in Last 7 days</div>
        <?php 
            $query_week = mysqli_query($conn, "SELECT id FROM passes where date(pass_creation_date) >= DATE(NOW()) - INTERVAL 7 DAY");
            $count_week_passes = mysqli_num_rows($query_week);
        ?>
        <div class="stat-value"><?php echo $count_week_passes; ?></div>
        <i class="fas fa-calendar-week stat-icon"></i>
    </div>
</div>

<!-- Recent Passes -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Passes</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pass Number</th>
                        <th>Full Name</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($conn, "SELECT * FROM passes ORDER BY pass_creation_date DESC LIMIT 10");
                    $count = 1;
                    while($row = mysqli_fetch_assoc($query)) {
                    ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                        <td><?php echo $count; ?></td>
                        <td><?php echo $row['pass_number']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['pass_creation_date'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $row['status'] == 'Pending' ? 'warning' : 'success'; ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php 
                        $count++;
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="page-header" style="margin-top: 2rem;">
    <h2 class="page-title"><i class="fas fa-chart-bar"></i> Analytics Dashboard</h2>
</div>

<div class="stats-grid">
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">7-Day Traffic Trend</h3>
        </div>
        <div class="card-body">
            <canvas id="trafficTrendChart"></canvas>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Category Distribution</h3>
        </div>
        <div class="card-body">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Peak Hours</h3>
        </div>
        <div class="card-body">
            <canvas id="peakHoursChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Traffic Trend Chart (Last 7 days)
<?php
$trafficData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passes WHERE DATE(pass_creation_date) = '$date'");
    $result = mysqli_fetch_assoc($query);
    $trafficData[] = [
        'date' => date('M d', strtotime($date)),
        'count' => (int)$result['count']
    ];
}
?>
const trafficCtx = document.getElementById('trafficTrendChart').getContext('2d');
new Chart(trafficCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($trafficData, 'date')); ?>,
        datasets: [{
            label: 'Passes Created',
            data: <?php echo json_encode(array_column($trafficData, 'count')); ?>,
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});

// Category Distribution Chart
<?php
$categoryData = $aiInsights->getCategoryDistribution();
?>
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($categoryData, 'category')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($categoryData, 'count')); ?>,
            backgroundColor: [
                'rgba(99, 102, 241, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(251, 146, 60, 0.8)',
                'rgba(34, 197, 94, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Peak Hours Chart
const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
new Chart(peakHoursCtx, {
    type: 'bar',
    data: {
        labels: [<?php foreach($peakHours as $ph) echo ($ph['hour'] % 12 ?: 12) . ' ' . ($ph['hour'] < 12 ? 'AM' : 'PM') . ','; ?>],
        datasets: [{
            label: 'Pass Count',
            data: [<?php foreach($peakHours as $ph) echo $ph['count'] . ','; ?>],
            backgroundColor: 'rgba(139, 92, 246, 0.8)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>

<?php include 'app/includes/footer.php'; ?>
<?php } ?>
