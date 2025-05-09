<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get driver earnings information
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_rides,
            SUM(amount) as total_earnings,
            AVG(amount) as average_earnings,
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as monthly_earnings
        FROM bookings 
        WHERE driver_id = :driver_id 
        AND status = 'completed'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $monthly_earnings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total earnings
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_rides,
            SUM(amount) as total_earnings,
            AVG(amount) as average_earnings
        FROM bookings 
        WHERE driver_id = :driver_id 
        AND status = 'completed'
    ");
    
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $total_stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Earnings</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .earnings-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .earnings-icon {
            font-size: 2rem;
            color: #198754;
        }
        
        .monthly-earnings {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'components/driver_sidebar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Earnings Overview</h2>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="earnings-card text-center">
                    <i class="fas fa-money-bill-wave earnings-icon mb-3"></i>
                    <h3>₱<?php echo number_format($total_stats['total_earnings'] ?? 0, 2); ?></h3>
                    <p class="text-muted mb-0">Total Earnings</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="earnings-card text-center">
                    <i class="fas fa-route earnings-icon mb-3"></i>
                    <h3><?php echo $total_stats['total_rides'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Total Rides</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="earnings-card text-center">
                    <i class="fas fa-chart-line earnings-icon mb-3"></i>
                    <h3>₱<?php echo number_format($total_stats['average_earnings'] ?? 0, 2); ?></h3>
                    <p class="text-muted mb-0">Average per Ride</p>
                </div>
            </div>
        </div>

        <!-- Earnings Chart -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Monthly Earnings</h5>
                <canvas id="earningsChart"></canvas>
            </div>
        </div>

        <!-- Monthly Breakdown -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Monthly Breakdown</h5>
                <?php if (empty($monthly_earnings)): ?>
                    <p class="text-center text-muted">No earnings data available</p>
                <?php else: ?>
                    <?php foreach ($monthly_earnings as $month): ?>
                        <div class="monthly-earnings">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></h6>
                                    <small class="text-muted"><?php echo $month['total_rides']; ?> rides</small>
                                </div>
                                <h5 class="mb-0">₱<?php echo number_format($month['monthly_earnings'], 2); ?></h5>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Prepare data for the chart
        const monthlyData = <?php echo json_encode(array_reverse($monthly_earnings)); ?>;
        
        // Create the chart
        const ctx = document.getElementById('earningsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Monthly Earnings',
                    data: monthlyData.map(item => item.monthly_earnings),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 