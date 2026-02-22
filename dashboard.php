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

<!-- Stats Cards & Filters -->
<div class="card mb-4" style="background: var(--glass-bg); border: 2px solid var(--accent-primary);">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 1rem;">
            <div>
                <h3 class="mb-0"><i class="fas fa-calendar-alt"></i> Date Range</h3>
                <p class="mb-0 text-secondary">Filter analytics by date</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 1rem;">
                <div class="form-group mb-0">
                    <label class="form-label mb-1">From</label>
                    <input type="date" id="stats-start-date" class="form-control" value="<?php echo date('Y-m-d', strtotime('-13 days')); ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label mb-1">To</label>
                    <input type="date" id="stats-end-date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="button" class="btn btn-primary" style="margin-top: 1.5rem;" onclick="updateDashboard()">
                    <i class="fas fa-sync-alt"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid">
    <!-- Total Passes Card -->
    <a href="manage-passes.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-label">Total Passes</div>
        <?php 
            $query = mysqli_query($conn, "SELECT id FROM passes");
            $count_total_passes = mysqli_num_rows($query);
        ?>
        <div class="stat-value" id="stat-total"><?php echo $count_total_passes; ?></div>
        <i class="fas fa-id-card-alt stat-icon"></i>
    </a>

    <!-- Passes Created Today Card -->
    <a href="manage-passes.php?filter=today" class="stat-card" style="text-decoration: none;">
        <div class="stat-label">Passes Created Today</div>
        <?php 
            $query_today = mysqli_query($conn, "SELECT id FROM passes where date(pass_creation_date) = CURDATE()");
            $count_today_passes = mysqli_num_rows($query_today);
        ?>
        <div class="stat-value" id="stat-today"><?php echo $count_today_passes; ?></div>
        <i class="fas fa-calendar-day stat-icon"></i>
    </a>
    
    <!-- Passes in Last 7 Days Card -->
    <a href="manage-passes.php?filter=week" class="stat-card" style="text-decoration: none;">
        <div class="stat-label">Passes in Last 7 days</div>
        <?php 
            $query_week = mysqli_query($conn, "SELECT id FROM passes where date(pass_creation_date) >= DATE(NOW()) - INTERVAL 7 DAY");
            $count_week_passes = mysqli_num_rows($query_week);
        ?>
        <div class="stat-value" id="stat-week"><?php echo $count_week_passes; ?></div>
        <i class="fas fa-calendar-week stat-icon"></i>
    </a>
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
    <div class="card" style="grid-column: span 2; position: relative;">
        <div class="card-header">
            <h3 class="card-title">14-Day Category Trends</h3>
        </div>
        <div class="card-body">
            <canvas id="categoryTrendsChart"></canvas>
            <div id="noData-trends" class="empty-state" style="display: none;">No Data Found</div>
        </div>
    </div>
    
    <div class="card" style="position: relative;">
        <div class="card-header">
            <h3 class="card-title">Hourly Check-in Heatmap</h3>
        </div>
        <div class="card-body">
            <canvas id="checkinHeatmapChart"></canvas>
            <div id="noData-heatmap" class="empty-state" style="display: none;">No Data Found</div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Current Category Mix</h3>
        </div>
        <div class="card-body">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<style>
.empty-state {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.7);
    padding: 1rem 2rem;
    border-radius: var(--radius-md);
    color: var(--text-secondary);
    font-weight: 600;
    z-index: 5;
}
</style>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let trendsChart, heatmapChart, categoryChart;

function initCharts() {
    // 1. Stacked Category Trends
    const trendsCtx = document.getElementById('categoryTrendsChart').getContext('2d');
    trendsChart = new Chart(trendsCtx, {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 2. Heatmap-style Hourly Bar Chart
    const heatmapCtx = document.getElementById('checkinHeatmapChart').getContext('2d');
    heatmapChart = new Chart(heatmapCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => `${i}:00`),
            datasets: [{
                label: 'Check-ins',
                data: [],
                backgroundColor: (context) => {
                    const value = context.dataset.data[context.dataIndex];
                    const alpha = Math.min(0.2 + (value / 10), 0.9);
                    return `rgba(99, 102, 241, ${alpha})`;
                }
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // 3. Category Distribution (Doughnut)
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [] }] },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

async function updateDashboard() {
    const start = document.getElementById('stats-start-date').value;
    const end = document.getElementById('stats-end-date').value;
    
    try {
        const response = await fetch(`app/api/dashboard_stats.php?start_date=${start}&end_date=${end}`);
        const data = await response.json();
        
        // Update Stats
        document.getElementById('stat-total').textContent = data.stats.total;
        document.getElementById('stat-today').textContent = data.stats.today;
        document.getElementById('stat-week').textContent = data.stats.week;
        
        // Update Stacked Trends
        trendsChart.data.labels = data.labels.map(l => {
            const d = new Date(l);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        const colors = {
            'Visitor': 'rgba(99, 102, 241, 0.8)',
            'Vendor': 'rgba(139, 92, 246, 0.8)',
            'Employee': 'rgba(236, 72, 153, 0.8)'
        };
        
        trendsChart.data.datasets = data.stackedData.map(d => ({
            label: d.label,
            data: d.data,
            backgroundColor: colors[d.label] || 'rgba(156, 163, 175, 0.8)'
        }));
        trendsChart.update();
        
        // Toggle Empty State for Trends
        const hasTrendsData = data.stackedData.some(d => d.data.some(val => val > 0));
        document.getElementById('noData-trends').style.display = hasTrendsData ? 'none' : 'block';
        
        // Update Heatmap
        heatmapChart.data.datasets[0].data = data.hourlyData;
        heatmapChart.update();
        
        // Toggle Empty State for Heatmap
        const hasHeatmapData = data.hourlyData.some(val => val > 0);
        document.getElementById('noData-heatmap').style.display = hasHeatmapData ? 'none' : 'block';

        // Update Category Doughnut
        categoryChart.data.labels = data.stackedData.map(d => d.label);
        categoryChart.data.datasets[0].data = data.stackedData.map(d => d.data.reduce((a, b) => a + b, 0));
        categoryChart.data.datasets[0].backgroundColor = [
            'rgba(99, 102, 241, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)'
        ];
        categoryChart.update();
        
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    updateDashboard();
    
    // Auto-refresh every 30 seconds
    setInterval(updateDashboard, 30000);
});
</script>

<?php include 'app/includes/footer.php'; ?>
<?php } ?>
