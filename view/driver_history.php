<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base URL
define('BASE_URL', 'http://localhost/MAMBYAHE/view');
define('UPLOADS_URL', BASE_URL . '/uploads');

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

    // Get ride history and driver info
    $stmt = $pdo->prepare("
        SELECT b.*, 
               p.fullname as passenger_name,
               p.profile_pic as passenger_pic,
               r.rating,
               r.review,
               b.pickup as pickup_location,
               b.destination as dropoff_location,
               b.fare as amount,
               d.driver_id,
               CASE 
                   WHEN d.profile_pic LIKE 'driver_%' THEN SUBSTRING_INDEX(d.profile_pic, '_', -1)
                   ELSE d.profile_pic
               END as driver_pic,
               CASE 
                   WHEN d.profile_pic LIKE '%.jpg' THEN '.jpg'
                   WHEN d.profile_pic LIKE '%.png' THEN '.png'
                   WHEN d.profile_pic LIKE '%.jpeg' THEN '.jpeg'
                   ELSE ''
               END as driver_ext,
               CASE 
                   WHEN b.status = 'completed' THEN 'Completed'
                   WHEN b.status = 'cancelled' THEN 'Cancelled'
                   WHEN b.status = 'rejected' THEN 'Rejected'
                   WHEN b.status = 'confirmed' THEN 'Confirmed'
                   ELSE 'In Progress'
               END as status_display
        FROM bookings b
        LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
        LEFT JOIN driver d ON b.driver_id = d.driver_id
        LEFT JOIN driver_reviews r ON b.id = r.booking_id
        WHERE b.driver_id = :driver_id
        ORDER BY b.created_at DESC
    ");
    
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total rides count
    $totalRidesStmt = $pdo->prepare("
        SELECT COUNT(*) as total_rides 
        FROM bookings 
        WHERE driver_id = :driver_id AND status = 'completed'
    ");
    $totalRidesStmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $totalRides = $totalRidesStmt->fetch(PDO::FETCH_ASSOC)['total_rides'];

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
    <title>Ride History</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Layout */
        .sidebar {
            width: 280px;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
        }

        .content {
            margin-left: 280px;
            padding: 20px;
        }

        /* Ride Card */
        .ride-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .passenger-pic, .driver-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .star-rating {
            color: #ffc107;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'components/driver_sidebar.php'; ?>

    <div class="content">
        <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Ride History</h2>
            <div class="total-rides-badge bg-primary text-white px-3 py-2 rounded">
                <i class="fas fa-car-side me-2"></i>
                Total Rides: <?php echo $totalRides; ?>
            </div>
        </div>
        
        <?php if (empty($rides)): ?>
            <div class="text-center text-muted">
                <i class="fas fa-history fa-3x mb-3"></i>
                <p>No ride history available</p>
            </div>
        <?php else: ?>
            <?php foreach ($rides as $ride): ?>
                <div class="ride-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <img src="<?php 
                                if (!empty($ride['driver_id']) && !empty($ride['driver_pic'])) {
                                    // Remove any existing extension before adding new one
                                    $baseName = pathinfo($ride['driver_pic'], PATHINFO_FILENAME);
                                    $driverPic = $ride['driver_prefix'] . $ride['driver_id'] . '_' . $baseName . $ride['driver_ext'];
                                }
                                echo !empty($driverPic) ? 'http://localhost:3000/MAMBYAHE/view/uploads/driver_ids/' . $driverPic : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'; 
                            ?>" 
                                 alt="Driver" 
                                 class="driver-pic me-3"
                                 onerror="this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'">
                            <img src="<?php 
                                $passengerPic = !empty($ride['passenger_pic']) ? $ride['passenger_pic'] . $ride['passenger_ext'] : '';
                                $passengerPic = '';
                                if (!empty($ride['passenger_pic'])) {
                                    // Remove any existing extension before adding new one
                                    $baseName = pathinfo($ride['passenger_pic'], PATHINFO_FILENAME);
                                    $passengerPic = $baseName . $ride['passenger_ext'];
                                }
                                echo !empty($passengerPic) ? 'http://localhost:3000/MAMBYAHE/view/uploads/passenger/' . $passengerPic : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'; 
                            ?>" 
                                 alt="Passenger" 
                                 class="passenger-pic me-3"
                                 onerror="this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($ride['passenger_name'] ?? 'Unknown Passenger'); ?></h5>
                                <small class="text-muted">
                                    <?php echo date('F d, Y h:i A', strtotime($ride['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <span class="status-badge bg-<?php 
                            echo match($ride['status']) {
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'in_progress' => 'primary',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $ride['status'])); ?>
                        </span>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Pickup Location</small>
                            <p class="mb-0"><?php echo htmlspecialchars($ride['pickup_location'] ?? 'Not specified'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Dropoff Location</small>
                            <p class="mb-0"><?php echo htmlspecialchars($ride['dropoff_location'] ?? 'Not specified'); ?></p>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">₱<?php echo number_format($ride['amount'] ?? 0, 2); ?></h5>
                            <small class="text-muted">Fare Amount</small>
                        </div>
                        
                        <?php if ($ride['status'] === 'completed' && isset($ride['rating'])): ?>
                            <div class="text-end">
                                <div class="star-rating mb-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $ride['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php if (!empty($ride['review'])): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($ride['review']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl7/1vVx9M/1bOc74n0xXQEqAxULb7q+lsZB3YLdwhN4s" crossorigin="anonymous"></script>
</body>
</html> 