<?php
session_start();
require_once '../db_connect.php';

// Fetch associations and their screen time data
try {
    $sql = "SELECT a.id, a.name, 
            COALESCE(SUM(CASE 
                WHEN DATE(t.timestamp) = CURDATE() THEN 
                    TIMESTAMPDIFF(SECOND, t.start_time, t.end_time) 
                END), 0) AS today_time,
            COALESCE(SUM(CASE 
                WHEN DATE(t.timestamp) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE() THEN 
                    TIMESTAMPDIFF(SECOND, t.start_time, t.end_time) 
                END), 0) AS week_time
            FROM associations a
            LEFT JOIN screen_time_tracking t ON a.id = t.association_id
            GROUP BY a.id, a.name
            ORDER BY today_time DESC";
    
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    // Convert seconds to hours and minutes
    $associations = [];
    while ($row = $result->fetch_assoc()) {
        $associations[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'today_time' => convertSecondsToHoursMinutes($row['today_time']),
            'week_time' => convertSecondsToHoursMinutes($row['week_time'])
        ];
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

function convertSecondsToHoursMinutes($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return sprintf("%02d:%02d", $hours, $minutes);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Association Dashboard - MTFRB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.min.css"/>
    <style>
        body { font-family: 'Inter', sans-serif; min-height: 100vh; margin: 0; }
        .header { height: 56px; border-bottom: 1px solid #dee2e6; background-color: #fff; opacity: 0.95; }
        .dashboard-container { 
            background-color: rgba(255 255 255 / 0.95); 
            border-radius: 0.75rem; 
            padding: 1.5rem; 
            margin: 1rem auto 2rem; 
            box-shadow: 0 0 10px rgb(0 0 0 / 0.1);
        }
        .card { 
            background-color: rgba(255 255 255 / 0.95); 
            border-radius: 0.5rem; 
            padding: 1rem; 
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgb(0 0 0 / 0.05);
        }
        .chart-container { 
            height: 300px; 
            margin-bottom: 2rem;
        }
        .association-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background-color: #f8fafc;
        }
        .association-card:hover {
            background-color: #e2e8f0;
        }
        .association-icon {
            width: 40px;
            height: 40px;
            background-color: #0ea5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="container mt-5" style="margin-left:250px;">
    <div class="dashboard-container">
        <h2 class="mb-4">Association Screen Time Dashboard</h2>
        
        <!-- Today's Screen Time Chart -->
        <div class="card">
            <h5 class="mb-3">Today's Screen Time</h5>
            <div class="chart-container" id="screenTimeChart"></div>
        </div>

        <!-- Association Details -->
        <div class="card">
            <h5 class="mb-3">Association Details</h5>
            <div class="association-list">
                <?php foreach ($associations as $assoc): ?>
                <div class="association-card">
                    <div class="association-icon">
                        <i class='bx bx-building'></i>
                    </div>
                    <div>
                        <h6 class="mb-1"><?php echo htmlspecialchars($assoc['name']); ?></h6>
                        <p class="mb-0">Today: <?php echo $assoc['today_time']; ?></p>
                        <p class="mb-0">This Week: <?php echo $assoc['week_time']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize Chart.js
const ctx = document.getElementById('screenTimeChart').getContext('2d');

// Get screen time data
const screenTimeData = {
    labels: [
        <?php foreach ($associations as $assoc): ?>
        '<?php echo addslashes($assoc['name']); ?>',
        <?php endforeach; ?>
    ],
    datasets: [{
        label: 'Screen Time (Today)',
        data: [
            <?php foreach ($associations as $assoc): ?>
            <?php 
            // Convert hours:minutes to seconds for chart
            $timeParts = explode(':', $assoc['today_time']);
            $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60);
            echo $seconds . ',';
            ?>
            <?php endforeach; ?>
        ],
        backgroundColor: [
            '#0ea5e9', '#10b981', '#f97316', '#f43f5e', '#8b5cf6',
            '#3b82f6', '#14b8a6', '#f59e0b', '#ef4444', '#a855f7'
        ],
        borderWidth: 1
    }]
};

// Configure Chart
const config = {
    type: 'bar',
    data: screenTimeData,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        // Convert seconds to hours:minutes for display
                        const hours = Math.floor(value / 3600);
                        const minutes = Math.floor((value % 3600) / 60);
                        return `${hours}h ${minutes}m`;
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Screen Time by Association'
            }
        }
    }
};

// Create Chart
new Chart(ctx, config);

// Auto-refresh data every 5 minutes
setInterval(async function() {
    try {
        const response = await fetch('get_screen_time_data.php');
        const data = await response.json();
        
        // Update chart data
        config.data.labels = data.labels;
        config.data.datasets[0].data = data.data;
        
        // Update chart
        window.chart.update();
        
        // Update association cards
        const associationList = document.querySelector('.association-list');
        associationList.innerHTML = data.associations.map(assoc => `
            <div class="association-card">
                <div class="association-icon">
                    <i class='bx bx-building'></i>
                </div>
                <div>
                    <h6 class="mb-1">${assoc.name}</h6>
                    <p class="mb-0">Today: ${assoc.today_time}</p>
                    <p class="mb-0">This Week: ${assoc.week_time}</p>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error refreshing data:', error);
    }
}, 300000); // 5 minutes in milliseconds
</script>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
