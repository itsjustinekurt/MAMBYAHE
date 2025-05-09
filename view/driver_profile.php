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

    // Get driver information
    $stmt = $pdo->prepare("
        SELECT d.*, 
               COUNT(DISTINCT b.id) as total_rides,
               COALESCE(AVG(r.rating), 0) as average_rating,
               COUNT(DISTINCT r.id) as total_reviews
        FROM driver d
        LEFT JOIN bookings b ON d.driver_id = b.driver_id AND b.status = 'completed'
        LEFT JOIN driver_reviews r ON d.driver_id = r.driver_id
        WHERE d.driver_id = :driver_id
        GROUP BY d.driver_id
    ");
    
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get recent reviews
    $stmt = $pdo->prepare("
        SELECT r.*, p.fullname as passenger_name, p.profile_pic
        FROM driver_reviews r
        JOIN passenger p ON r.passenger_id = p.passenger_id
        WHERE r.driver_id = :driver_id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $recent_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all reviews
    $stmt = $pdo->prepare("
        SELECT r.*, p.fullname as passenger_name, p.profile_pic, b.created_at as booking_date
        FROM driver_reviews r
        JOIN passenger p ON r.passenger_id = p.passenger_id
        JOIN bookings b ON r.booking_id = b.id
        WHERE r.driver_id = :driver_id
        ORDER BY r.created_at DESC
    ");
    
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $all_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Driver Profile</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <style>
        .profile-header {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .profile-pic {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        
        .badge i {
            font-size: 1.1rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .review-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .star-rating {
            color: #ffc107;
        }
        
        .edit-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        .status-badge {
            font-size: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
        }
        
        .status-badge i {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .status-icon {
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .status-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .review-item {
            padding: 1rem;
            border-radius: 8px;
            background: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1000;
            color: #198754;
            font-size: 1.5rem;
            background: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: none;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: white;
            z-index: 1001;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-header {
            padding: 2rem 1rem;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .sidebar-profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #198754;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover {
            background: #f8f9fa;
            color: #198754;
        }

        .sidebar-link.active {
            background: #198754;
            color: white;
        }

        .sidebar-link i {
            width: 24px;
            margin-right: 10px;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            display: none;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Adjust main content when sidebar is active */
        .container {
            transition: all 0.3s ease;
        }

        .sidebar.active + .container {
            margin-left: 280px;
        }

        /* Bottom Sheet Styles */
        .bottom-sheet {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            background: #fff;
            border-radius: 1.5rem 1.5rem 0 0;
            box-shadow: 0 -2px 16px rgba(0,0,0,0.15);
            z-index: 2000;
            transition: transform 0.3s;
            max-width: 500px;
            margin: 0 auto;
            transform: translateY(100%);
        }
        .bottom-sheet.show {
            transform: translateY(0);
        }
        .bottom-sheet .sheet-handle {
            width: 50px;
            height: 6px;
            background: #ccc;
            border-radius: 3px;
            margin: 10px auto 0 auto;
            cursor: pointer;
        }
        .bottom-sheet .sheet-content {
            padding: 1.5rem;
        }
        .bottom-sheet .passenger-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 1rem;
        }
        .bottom-sheet .call-btn {
            background: #f5f5f5;
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-right: 0;
        }
        .bottom-sheet .arrive-btn {
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 0.75rem;
            margin-top: 1rem;
        }
        .avatar-half-circle {
            width: 56px;
            height: 56px;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .half-circle-bg {
            width: 56px;
            height: 28px;
            background: linear-gradient(135deg, #e0e7ef 60%, #fff 100%);
            border-top-left-radius: 56px;
            border-top-right-radius: 56px;
            position: absolute;
            bottom: 0;
            left: 0;
            z-index: 1;
        }
        .passenger-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            z-index: 2;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            border: 2px solid #f5f5f5;
        }
        .pickup-dropoff-group {
            margin-left: 10px;
            margin-bottom: 1.5rem;
        }
        .pickup-row .pickup-icon {
            color: #198754;
            font-size: 1.1rem;
        }
        .dropoff-row .dropoff-icon {
            color: #dc3545;
            font-size: 1.2rem;
        }
        .pickup-dropoff-line {
            width: 2px;
            height: 32px;
            background: repeating-linear-gradient(
                to bottom,
                #b0b0b0 0px,
                #b0b0b0 4px,
                transparent 4px,
                transparent 8px
            );
            left: 15px;
            z-index: 0;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'components/driver_sidebar.php'; ?>
    
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?php echo !empty($driver['profile_pic']) ? './uploads/driver_ids/' . $driver['profile_pic'] : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'; ?>" 
                         alt="Profile Picture" 
                         class="profile-pic rounded-circle mb-3"
                         onerror="this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'">
                </div>
                <div class="col-md-9">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2><?php echo htmlspecialchars($driver['fullname'] ?? ''); ?></h2>
                            <p class="mb-2">
                                <i class="fas fa-star text-warning"></i>
                                <?php echo number_format($driver['average_rating'] ?? 0, 1); ?> 
                                (<a href="#" class="text-white text-decoration-none" onclick="showAllReviews()"><?php echo $driver['total_reviews'] ?? 0; ?> reviews</a>)
                            </p>
                            <?php if (!empty($driver['vehicle_model']) || !empty($driver['plate_number'])): ?>
                            <p class="mb-0">
                                <i class="fas fa-car text-white"></i>
                                <?php 
                                $vehicleInfo = [];
                                if (!empty($driver['vehicle_model'])) {
                                    $vehicleInfo[] = htmlspecialchars($driver['vehicle_model']);
                                }
                                if (!empty($driver['plate_number'])) {
                                    $vehicleInfo[] = htmlspecialchars($driver['plate_number']);
                                }
                                echo implode(' - ', $vehicleInfo);
                                ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="status-icon" onclick="showStatusInfo()" style="background: rgba(255, 255, 255, 0.2);">
                            <?php
                            $statusIcon = match($driver['status'] ?? '') {
                                'approved' => 'fa-check-circle text-success',
                                'rejected' => 'fa-times-circle text-danger',
                                default => 'fa-exclamation-circle text-warning'
                            };
                            ?>
                            <i class="fas <?php echo $statusIcon; ?> fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <h3 class="mb-0"><?php echo $driver['total_rides'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Total Rides</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <h3 class="mb-0"><?php echo number_format($driver['average_rating'] ?? 0, 1); ?></h3>
                    <p class="text-muted mb-0">Average Rating</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <h3 class="mb-0">
                        <a href="#" class="text-decoration-none text-dark" onclick="showAllReviews(); return false;">
                            <?php echo $driver['total_reviews'] ?? 0; ?>
                        </a>
                    </h3>
                    <p class="text-muted mb-0">Total Reviews</p>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Personal Information</h4>
                        <div class="mb-3">
                            <label class="text-muted">Full Name</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['fullname'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Username</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['username'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Phone</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['phone'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Nationality</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['nationality'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Date of Birth</label>
                            <p class="mb-0"><?php echo $driver['dob'] ? date('F d, Y', strtotime($driver['dob'])) : 'Not specified'; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Address</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['address'] ?? 'No address provided'); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">TODA</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['toda'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Vehicle Information</h4>
                        <div class="mb-3">
                            <label class="text-muted">Franchise Number</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['franchise_no'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">OR Number</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['or_no'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Vehicle Make</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['make'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Motor Number</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['motor_no'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Chassis Number</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['chassis_no'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Plate Number</label>
                            <p class="mb-0"><?php echo htmlspecialchars($driver['plate_no'] ?? ''); ?></p>
                        </div>
                        <button class="btn btn-primary mt-3" onclick="editProfile()">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Personal Information</h5>
                                <div class="mb-3">
                                    <label class="form-label">Profile Picture</label>
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?php echo !empty($driver['profile_pic']) ? './uploads/driver_ids/' . $driver['profile_pic'] : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'; ?>" 
                                             alt="Profile Picture" 
                                             class="rounded-circle me-3"
                                             style="width: 100px; height: 100px; object-fit: cover;"
                                             onerror="this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'">
                                        <div>
                                            <input type="file" class="form-control" name="profile_pic" accept="image/*">
                                            <small class="text-muted">Max file size: 2MB. Supported formats: JPG, PNG</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($driver['username'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($driver['fullname'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($driver['phone'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" class="form-control" name="nationality" value="<?php echo htmlspecialchars($driver['nationality'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="<?php echo $driver['dob'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($driver['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">TODA</label>
                                    <input type="text" class="form-control" name="toda" value="<?php echo htmlspecialchars($driver['toda'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Vehicle Information</h5>
                                <div class="mb-3">
                                    <label class="form-label">Franchise Number</label>
                                    <input type="text" class="form-control" name="franchise_no" value="<?php echo htmlspecialchars($driver['franchise_no'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">OR Number</label>
                                    <input type="text" class="form-control" name="or_no" value="<?php echo htmlspecialchars($driver['or_no'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Vehicle Make</label>
                                    <input type="text" class="form-control" name="make" value="<?php echo htmlspecialchars($driver['make'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Motor Number</label>
                                    <input type="text" class="form-control" name="motor_no" value="<?php echo htmlspecialchars($driver['motor_no'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Chassis Number</label>
                                    <input type="text" class="form-control" name="chassis_no" value="<?php echo htmlspecialchars($driver['chassis_no'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Plate Number</label>
                                    <input type="text" class="form-control" name="plate_no" value="<?php echo htmlspecialchars($driver['plate_no'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Modal -->
    <div class="modal fade" id="reviewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Reviews</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($all_reviews)): ?>
                        <p class="text-center text-muted">No reviews yet</p>
                    <?php else: ?>
                        <div class="reviews-list">
                            <?php foreach ($all_reviews as $review): ?>
                                <div class="review-item mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?php echo !empty($review['profile_pic']) ? './uploads/' . $review['profile_pic'] : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'; ?>" 
                                             alt="Passenger" 
                                             class="rounded-circle me-3"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($review['passenger_name']); ?></h6>
                                            <div class="star-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted ms-auto">
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                                    <small class="text-muted">Ride date: <?php echo date('M d, Y', strtotime($review['booking_date'])); ?></small>
                                </div>
                                <?php if (!$loop->last): ?>
                                    <hr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Sheet for Ride Info -->
    <div id="rideSheet" class="bottom-sheet">
        <div class="sheet-handle" id="sheetHandle"></div>
        <div class="sheet-content">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-half-circle position-relative me-3">
                    <div class="half-circle-bg"></div>
                    <img id="sheetPassengerImg" src="" class="passenger-img position-absolute top-0 start-50 translate-middle-x" alt="Passenger Photo">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fw-bold" id="sheetPassengerName"></div>
                        <a href="#" class="call-btn ms-2" id="sheetCallBtn"><i class="fas fa-phone"></i></a>
                    </div>
                    <div class="text-muted small">Passenger</div>
                </div>
            </div>
            <div class="pickup-dropoff-group position-relative mb-2">
                <div class="pickup-row d-flex align-items-center mb-1">
                    <span class="pickup-icon me-2"><i class="fas fa-circle"></i></span>
                    <div>
                        <div class="text-muted small">Pickup</div>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        function editProfile() {
            const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
            modal.show();
        }

        function saveProfile() {
            const form = document.getElementById('editProfileForm');
            const formData = new FormData(form);

            fetch('update_driver_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Profile updated successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update profile'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating profile'
                });
            });
        }

        function showStatusInfo() {
            const status = '<?php echo $driver['status'] ?? 'pending'; ?>';
            const isOnline = '<?php echo $driver['is_online'] ?? 'offline'; ?>';
            
            let title, icon, text;
            
            switch(status) {
                case 'approved':
                    title = 'Account Approved';
                    icon = 'success';
                    text = 'Your account is approved and active.';
                    break;
                case 'rejected':
                    title = 'Account Rejected';
                    icon = 'error';
                    text = 'Your account has been rejected. Please contact support for more information.';
                    break;
                default:
                    title = 'Account Pending';
                    icon = 'warning';
                    text = 'Your account is pending approval. We will notify you once it is reviewed.';
            }
            
            if (isOnline === 'online') {
                text += '<br><br><i class="fas fa-circle text-success"></i> Currently Online';
            } else {
                text += '<br><br><i class="fas fa-circle text-secondary"></i> Currently Offline';
            }
            
            Swal.fire({
                title: title,
                html: text,
                icon: icon,
                confirmButtonColor: '#198754'
            });
        }

        function showAllReviews() {
            const reviewsModal = new bootstrap.Modal(document.getElementById('reviewsModal'));
            reviewsModal.show();
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>
</body>
</html> 