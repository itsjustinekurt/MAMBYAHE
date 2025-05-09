<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Set page title
$page_title = "Driver Dashboard";

// Add this at the top of the file, after session_start()
$baseUrl = 'http://localhost/MAMBYAHE';

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

// After session_start()
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if driver is logged in
    if (!isset($_SESSION['driver_id'])) {
        header("Location: login.php");
        exit();
    }

$driver_id = $_SESSION['driver_id'];
    $driver_name = htmlspecialchars($_SESSION['username']);

    // Get driver's current location
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM driver_locations WHERE driver_id = :driver_id ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute(['driver_id' => $driver_id]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get driver's profile picture
    $stmt = $pdo->prepare("SELECT profile_pic FROM driver WHERE driver_id = :driver_id");
    $stmt->execute(['driver_id' => $driver_id]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug the profile picture path
    error_log("Driver profile picture: " . print_r($driver['profile_pic'], true));

    // Use relative path
    $profilePic = !empty($driver['profile_pic']) 
        ? 'uploads/driver_ids/' . $driver['profile_pic'] 
        : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';

    // Add error handling for image loading with more detailed logging
    echo '<script>
        window.addEventListener("load", function() {
            const profileImages = document.querySelectorAll("img[src*=\'driver_ids\']");
            profileImages.forEach(img => {
                console.log("Attempting to load image:", img.src); // Debug log
                img.onerror = function() {
                    console.log("Image failed to load:", this.src); // Debug log
                    console.log("Current page URL:", window.location.href); // Debug log
                    this.src = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg";
                };
            });
        });
    </script>';

    // Debug: Check notifications table structure
    try {
        $debug_stmt = $pdo->query("DESCRIBE notifications");
        $columns = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Notifications table structure: " . print_r($columns, true));
    } catch (PDOException $e) {
        error_log("Error checking notifications table structure: " . $e->getMessage());
    }

    // Debug: Check all notifications for this driver
    try {
        $debug_stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE driver_id = :driver_id 
            ORDER BY created_at DESC
        ");
        $debug_stmt->execute(['driver_id' => $driver_id]);
        $all_notifications = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("All notifications for driver $driver_id: " . print_r($all_notifications, true));
    } catch (PDOException $e) {
        error_log("Error fetching all notifications: " . $e->getMessage());
    }

    // Get unread notifications count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS unread_count 
        FROM notifications 
        WHERE driver_id = :driver_id 
        AND user_type = 'driver'
        AND status = 'unread'
    ");
    try {
        $stmt->execute(['driver_id' => $driver_id]);
        $unread_count = $stmt->fetchColumn();
        error_log("Unread notifications count: " . $unread_count);
    } catch (PDOException $e) {
        error_log("Error getting unread count: " . $e->getMessage());
        $unread_count = 0;
    }

    // Check if notifications table exists
    try {
        $tableExists = $pdo->query("SHOW TABLES LIKE 'notifications'")->rowCount() > 0;
        error_log("Notifications table exists: " . ($tableExists ? 'Yes' : 'No'));
        
        if ($tableExists) {
            // Get table structure
            $columns = $pdo->query("DESCRIBE notifications")->fetchAll(PDO::FETCH_COLUMN);
            error_log("Notifications table columns: " . print_r($columns, true));
        }
    } catch (PDOException $e) {
        error_log("Error checking notifications table: " . $e->getMessage());
    }

    // Debug: Check notifications table
    try {
        $debug_stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE driver_id = :driver_id 
            ORDER BY created_at DESC
        ");
        $debug_stmt->execute(['driver_id' => $driver_id]);
        $all_notifications = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("All notifications for driver: " . print_r($all_notifications, true));
    } catch (PDOException $e) {
        error_log("Error fetching all notifications: " . $e->getMessage());
    }

    // Get notifications for the driver
    $stmt = $pdo->prepare("
        SELECT n.*, p.fullname as passenger_name, p.profile_pic, p.phone as passenger_phone,
               b.status as booking_status, b.pickup_location, b.destination, b.seats, b.fare,
               b.id as booking_id, b.passenger_id
        FROM notifications n
        LEFT JOIN bookings b ON n.booking_id = b.id
        LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
        WHERE n.driver_id = :driver_id 
        AND n.user_type = 'driver'
        ORDER BY n.created_at DESC 
        LIMIT 10
    ");
    
    try {
        error_log("Executing notifications query for driver_id: " . $driver_id);
        $stmt->execute(['driver_id' => $driver_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Number of notifications found: " . count($notifications));
        error_log("Raw notifications data: " . print_r($notifications, true));
    } catch (PDOException $e) {
        error_log("Error fetching notifications: " . $e->getMessage());
        $notifications = [];
    }

    // Debug output for notifications
    error_log("Driver ID: " . $driver_id);
    error_log("SQL Query: " . $stmt->queryString);
    error_log("Raw notifications data: " . print_r($notifications, true));

    // Get total trips (completed)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE driver_id = :driver_id AND status = 'completed'");
    $stmt->execute(['driver_id' => $driver_id]);
    $total_trips = $stmt->fetchColumn();
    // Get pending rides
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE driver_id = :driver_id AND status = 'pending'");
    $stmt->execute(['driver_id' => $driver_id]);
    $pending_rides = $stmt->fetchColumn();

    // Get daily ride counts for the last 14 days
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as ride_date, COUNT(*) as ride_count
        FROM bookings
        WHERE driver_id = :driver_id
        GROUP BY ride_date
        ORDER BY ride_date DESC
        LIMIT 14
    ");
    $stmt->execute(['driver_id' => $driver_id]);
    $ride_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ride_dates = [];
    $ride_counts = [];
    foreach (array_reverse($ride_stats) as $row) {
        $ride_dates[] = $row['ride_date'];
        $ride_counts[] = (int)$row['ride_count'];
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    try {
        // Update driver's online status to offline
        $stmt = $pdo->prepare("UPDATE driver SET is_online = 'offline' WHERE driver_id = :driver_id");
        $stmt->execute(['driver_id' => $_SESSION['driver_id']]);

        // Clear session and redirect
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        die("Error updating status: " . $e->getMessage());
    }
}

// Add this after the session_start() and before the HTML
if (isset($_SESSION['arrival_state'])) {
    $arrival_state = $_SESSION['arrival_state'];
    // Check if the arrival state is less than 1 hour old
    if (time() - $arrival_state['timestamp'] < 3600) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const rideSheet = document.getElementById("rideSheet");
                if (rideSheet) {
                    // Set the booking and passenger IDs
                    rideSheet.dataset.bookingId = "' . $arrival_state['booking_id'] . '";
                    rideSheet.dataset.passengerId = "' . $arrival_state['passenger_id'] . '";
                    
                    // Fetch and display passenger info
                    fetch("get_passenger_info.php?passenger_id=' . $arrival_state['passenger_id'] . '")
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById("sheetPassengerName").textContent = data.passenger_name;
                                document.getElementById("sheetPassengerImg").src = data.profile_pic || "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg";
                                document.getElementById("sheetPickup").textContent = data.pickup_location;
                                document.getElementById("sheetDropoff").textContent = data.destination;
                                document.getElementById("sheetCallBtn").href = "tel:" + data.phone;
                                
                                // Show the bottom sheet
                                rideSheet.classList.add("show");
                                
                                // Prevent the sheet from being closed by clicking outside
                                document.addEventListener("click", function(e) {
                                    if (e.target === rideSheet) {
                                        e.stopPropagation();
                                    }
                                });
                                
                                // Remove any existing click handlers on the sheet handle
                                const sheetHandle = document.getElementById("sheetHandle");
                                if (sheetHandle) {
                                    sheetHandle.onclick = function(e) {
                                        e.stopPropagation();
                                    };
                                }
                            }
                        })
                        .catch(error => console.error("Error fetching passenger info:", error));
                }
            });
        </script>';
    } else {
        // Clear expired arrival state
        unset($_SESSION['arrival_state']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> - Mambyahe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        #map {
            height: calc(100vh - 60px); /* Adjust map height to account for header */
            width: 100%;
            position: absolute;
            top: 60px; /* Position map below header */
            left: 0;
            z-index: 1;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 15px;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
        }

        .menu-toggle {
            background: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            margin-right: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .menu-toggle:hover {
            background-color: #f8f9fa;
        }

        .notification-icon {
            position: relative;
            background: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .notification-icon:hover {
            background-color: #f8f9fa;
        }

        .notification-icon .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
        }

        /* Update existing styles */
        .leaflet-control-zoom {
            margin-top: 70px !important; /* Adjust zoom controls position */
            margin-right: 10px !important;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item .mb-0.text-muted {
            font-size: 0.85rem;
            line-height: 1.2;
            word-break: break-word;
            white-space: normal;
        }

        .notification-item h6.mb-0 {
            font-size: 0.95rem;
        }

        .notification-item small.text-muted {
            font-size: 0.8rem;
        }

        /* Add styles for notification container */
        .notification-container {
            position: relative;
        }

        /* Add styles for dropdown header and footer */
        .notification-header {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
        }

        .notification-footer {
            padding: 12px 15px;
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
        }

        /* Add styles for no notifications message */
        .no-notifications {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }

        .no-notifications i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

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
<body>
    <?php include 'components/header.php'; ?>
    <?php include 'components/driver_sidebar.php'; ?>

<!-- Map Container -->
<div id="map"></div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <div class="mb-3">
                    <i class="fas fa-sign-out-alt fa-3x text-danger"></i>
                </div>
                <h4 class="mb-1">Come back soon!</h4>
                <p class="text-muted">Are you sure you want to logout?</p>
                <div class="d-grid gap-2">
                    <form method="POST">
                        <button type="submit" name="confirm_logout" class="btn btn-dark">Yes, Logout</button>
                    </form>
                    <button type="button" class="btn btn-link text-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rides per Day Graph (Chart.js) -->
<div class="container-fluid mt-3" style="max-width: 600px;">
  <div class="card mb-3">
    <div class="card-body">
      <canvas id="ridesChart" height="120"></canvas>
    </div>
  </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Passenger Information -->
        <div class="passenger-info mb-4">
          <div class="d-flex align-items-center">
            <img id="passengerPhoto" src="" alt="Passenger Photo" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
            <div class="flex-grow-1">
              <h6 class="mb-1" id="passengerName"></h6>
              <p class="text-muted mb-0" id="passengerPhone"></p>
            </div>
            <a href="#" class="btn btn-primary btn-sm ms-2" id="callPassengerBtn">
              <i class="fas fa-phone"></i>
            </a>
          </div>
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
          <div class="mb-4">
            <div class="d-flex align-items-start">
              <i class="fas fa-map-marker-alt text-primary me-3 mt-1"></i>
              <div class="flex-grow-1">
                <small class="text-muted d-block mb-1">Pickup Location</small>
                <div id="pickupLocation" class="fs-6"></div>
              </div>
            </div>
          </div>
          
          <div class="mb-4">
            <div class="d-flex align-items-start">
              <i class="fas fa-map-pin text-danger me-3 mt-1"></i>
              <div class="flex-grow-1">
                <small class="text-muted d-block mb-1">Drop-off Location</small>
                <div id="dropoffLocation" class="fs-6"></div>
              </div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-6">
              <div class="d-flex align-items-start">
                <i class="fas fa-users text-info me-3 mt-1"></i>
                <div>
                  <small class="text-muted d-block mb-1">Seats</small>
                  <div id="seats" class="fs-6"></div>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-start">
                <i class="fas fa-money-bill text-success me-3 mt-1"></i>
                <div>
                  <small class="text-muted d-block mb-1">Total Fare</small>
                  <div id="fare" class="fs-6"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2 mt-4">
          <button type="button" class="btn btn-success flex-grow-1" id="confirmBooking" aria-label="Confirm booking">
            <i class="fas fa-check me-2"></i>Confirm
          </button>
          <button type="button" class="btn btn-danger flex-grow-1" id="rejectBooking" aria-label="Reject booking">
            <i class="fas fa-times me-2"></i>Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectionReasonModal" tabindex="-1" role="dialog" aria-labelledby="rejectionReasonModalLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
            <h5 class="modal-title" id="rejectionReasonModalLabel">Rejection Reason</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="rejectionForm">
          <div class="mb-3">
            <label for="rejectionReason" class="form-label">Please provide a reason for rejecting this ride:</label>
            <textarea class="form-control" id="rejectionReason" rows="3" required></textarea>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-danger">Submit Rejection</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bottom Sheet for Ride Info -->
<div id="rideSheet" class="bottom-sheet">
  <div class="sheet-handle" id="sheetHandle"></div>
  <div class="sheet-content">
    <div class="d-flex align-items-center mb-3">
      <!-- Report Icon -->
      <button class="btn btn-link text-danger me-3" id="sheetReportBtn" title="Report Passenger">
        <i class="fas fa-flag"></i>
      </button>
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
          <div class="fw-bold" id="sheetPickup"></div>
        </div>
      </div>
      <div class="pickup-dropoff-line position-absolute start-0" style="left: 10px; top: 28px; height: 32px;"></div>
      <div class="dropoff-row d-flex align-items-center mt-1">
        <span class="dropoff-icon me-2"><i class="fas fa-map-marker-alt"></i></span>
        <div>
          <div class="text-muted small">Drop Off</div>
          <div class="fw-bold" id="sheetDropoff"></div>
        </div>
      </div>
    </div>
    <div class="d-flex align-items-center mb-3">
      <button class="btn btn-success flex-grow-1 arrive-btn" id="sheetArriveBtn" onclick="handleArrival()">Arrive</button>
    </div>
  </div>
</div>

<!-- Review & Report Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center w-100">
          <div class="avatar-half-circle position-relative me-3">
            <div class="half-circle-bg"></div>
            <img id="reviewPassengerImg" src="" class="passenger-img position-absolute top-0 start-50 translate-middle-x" alt="Passenger Photo">
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center justify-content-between">
              <div class="fw-bold" id="reviewPassengerName"></div>
              <button type="button" class="btn btn-link text-danger p-0 ms-2" id="reportBtn" title="Report Passenger"><i class="fas fa-flag"></i></button>
            </div>
            <div class="text-muted small">Passenger</div>
          </div>
        </div>
      </div>
      <div class="modal-body pt-2">
        <div class="row mb-2">
          <div class="col-6">
            <div class="text-muted small">Pickup</div>
            <div class="fw-bold" id="reviewPickup"></div>
          </div>
          <div class="col-6">
            <div class="text-muted small">Drop Off</div>
            <div class="fw-bold" id="reviewDropoff"></div>
          </div>
        </div>
        <form id="reviewForm">
          <div class="mb-2">
            <label for="reviewText" class="form-label">Review</label>
            <textarea class="form-control" id="reviewText" rows="2" placeholder="Share your feedback..."></textarea>
          </div>
          <div class="mb-3 text-center">
            <span class="star-rating">
              <i class="far fa-star" data-value="1"></i>
              <i class="far fa-star" data-value="2"></i>
              <i class="far fa-star" data-value="3"></i>
              <i class="far fa-star" data-value="4"></i>
              <i class="far fa-star" data-value="5"></i>
            </span>
          </div>
          <button type="submit" class="btn btn-dark w-100">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Complaint Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Report Passenger</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="complaintForm">
          <div class="mb-2">
            <label for="complaintReason" class="form-label">Reason</label>
            <select class="form-select" id="complaintReason" required>
              <option value="">Select a reason</option>
              <option value="rude">Rude/Disrespectful</option>
              <option value="property">Damaged Property</option>
              <option value="safety">Safety Issue</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="complaintDetails" class="form-label">Details</label>
            <textarea class="form-control" id="complaintDetails" rows="2" placeholder="Describe the issue..." required></textarea>
          </div>
          <button type="submit" class="btn btn-danger w-100">Submit Complaint</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Report Passenger Modal -->
<div class="modal fade" id="reportPassengerModal" tabindex="-1" aria-labelledby="reportPassengerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reportPassengerModalLabel">Report Passenger</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="reportPassengerForm">
          <div class="mb-3">
            <label for="reportReason" class="form-label">Reason for Reporting</label>
            <textarea class="form-control" id="reportReason" rows="3" placeholder="Describe the issue..." required></textarea>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-danger">Submit Report</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Add this debug output temporarily -->
<div style="display: none;">
    <?php
    echo "Debug - Number of notifications: " . count($notifications) . "<br>";
    echo "Debug - Unread count: " . $unread_count . "<br>";
    echo "Debug - Notifications data: <pre>" . print_r($notifications, true) . "</pre>";
    ?>
</div>

    <!-- Bootstrap 5 Bundle JS (for Offcanvas) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Map Initialization -->
    <script>
    // Initialize map
    let map = L.map('map').setView([14.5995, 120.9842], 12); // Default center
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

    // Get current location and update it periodically
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Update map view
                map.setView([lat, lng], 15);

                // Add marker for current location
                const driverMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'driver-marker',
                        html: '<i class="fas fa-car" style="color: #198754; font-size: 24px;"></i>'
                    })
                }).addTo(map);

                // Update location periodically
                setInterval(() => {
                    navigator.geolocation.getCurrentPosition(
                        function(newPosition) {
                            const newLat = newPosition.coords.latitude;
                            const newLng = newPosition.coords.longitude;
                            
                            // Update marker position
                            driverMarker.setLatLng([newLat, newLng]);

        // Update location in database
        fetch('update_location.php', {
          method: 'POST',
          headers: {
                            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
                            latitude: newLat,
                            longitude: newLng
          })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                console.error('Location update failed:', data.message);
                            }
                        })
                        .catch(error => console.error('Error updating location:', error));
        },
        function(error) {
                        console.error('Geolocation error:', error.message);
        }
      );
            }, 10000); // Update every 10 seconds
        },
        function(error) {
            console.error('Geolocation error:', error.message);
            Swal.fire({
                icon: 'error',
                title: 'Location Error',
                text: 'Please enable location services to use the map.'
            });
        }
      );
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Not Supported',
            text: 'Geolocation is not supported by your browser.'
        });
    }

    // Function to handle arrival
    function handleArrival() {
        const bookingId = document.getElementById("rideSheet").dataset.bookingId;
        const passengerId = document.getElementById("rideSheet").dataset.passengerId;

        fetch("driver_arrived.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                booking_id: bookingId,
                passenger_id: passengerId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear the arrival state from session
                fetch("clear_arrival_state.php", {
                    method: "POST"
    });

                // Hide the bottom sheet
                document.getElementById("rideSheet").classList.remove("show");
        
                // Show success message
                Swal.fire({
                    icon: "success",
                    title: "Arrival Notified",
                    text: "Passenger has been notified of your arrival",
                    timer: 2000,
                    showConfirmButton: false
                });
                } else {
                    Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "Failed to notify passenger"
                    });
                }
            })
        .catch(error => {
            console.error("Error:", error);
                Swal.fire({
                icon: "error",
                title: "Error",
                text: "An error occurred while notifying passenger"
            });
        });
}

    // Function to update notifications display
    function updateNotificationsDisplay(notifications) {
        const notificationsContainer = document.querySelector(".notification-dropdown");
        if (!notificationsContainer) {
            console.error("Notification container not found");
            return;
        }

        // Clear existing notifications except header and footer
        const header = notificationsContainer.querySelector(".dropdown-header")?.parentElement;
        const footer = notificationsContainer.querySelector(".dropdown-item.text-center");
        
        // Clear all content
        notificationsContainer.innerHTML = "";
        
        // Add back header if it exists
        if (header) {
        notificationsContainer.appendChild(header);
            notificationsContainer.appendChild(document.createElement("div")).className = "dropdown-divider";
        }
        
        if (!notifications || notifications.length === 0) {
            const noNotifications = document.createElement("div");
            noNotifications.className = "dropdown-item text-center py-3";
            noNotifications.innerHTML = `
                <i class="fas fa-bell-slash text-muted mb-2"></i>
                <p class="mb-0">No notifications</p>
            `;
            notificationsContainer.appendChild(noNotifications);
        } else {
            notifications.forEach(notification => {
                if (!notification) return; // Skip invalid notifications
                
                const notificationItem = document.createElement("div");
                notificationItem.className = `dropdown-item notification-item ${notification.status === "unread" ? "bg-light" : ""}`;
                notificationItem.setAttribute("data-notification-id", notification.notification_id);
                notificationItem.setAttribute("data-booking-id", notification.booking_id);
                notificationItem.setAttribute("data-driver-id", notification.driver_id);
                notificationItem.style.cursor = "pointer";
                
                // Create notification content
                const content = document.createElement("div");
                content.className = "d-flex align-items-start";
                
                // Add passenger image if available
                const imgDiv = document.createElement("div");
                imgDiv.className = "me-3";
                const img = document.createElement("img");
                img.className = "rounded-circle";
                img.style.width = "40px";
                img.style.height = "40px";
                img.style.objectFit = "cover";
                
                if (notification.profile_pic) {
                    const baseUrl = window.location.protocol + '//' + window.location.hostname + '/MAMBYAHE';
                    loadImageWithRetry(img, baseUrl, notification.profile_pic);
                        } else {
                    img.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
                }
                
                imgDiv.appendChild(img);
                content.appendChild(imgDiv);
                
                // Add notification text
                const textDiv = document.createElement("div");
                textDiv.className = "flex-grow-1";
                
                const message = document.createElement("div");
                message.className = "mb-1";
                message.textContent = notification.message;
                
                const time = document.createElement("small");
                time.className = "text-muted";
                time.textContent = new Date(notification.created_at).toLocaleString();
                
                textDiv.appendChild(message);
                textDiv.appendChild(time);
                content.appendChild(textDiv);
                
                notificationItem.appendChild(content);
                notificationsContainer.appendChild(notificationItem);
                notificationsContainer.appendChild(document.createElement("div")).className = "dropdown-divider";
                
                // Add click handler
                notificationItem.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
                    if (notification.status === "unread") {
                        markNotificationAsRead(notification.notification_id);
                    }
                    
                    if (notification.booking_id) {
                        // Handle booking-related notification
                        fetch('get_booking_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                booking_id: notification.booking_id
                            })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                                openBookingModal(data);
                            } else {
                                console.error('Failed to load booking details:', data.message);
                }
            })
                        .catch(error => {
                            console.error('Error loading booking details:', error);
                        });
                    }
                };
            });
        }
        
        // Add back footer if it exists
        if (footer) {
            notificationsContainer.appendChild(footer);
        }
    }

    // Function to mark a single notification as read
function markNotificationAsRead(notificationId) {
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                loadNotifications(); // Reload notifications after marking as read
                        } else {
                console.error('Failed to mark notification as read:', data.message);
        }
    })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
}

    // Function to mark all notifications as read
    function markAllNotificationsAsRead() {
                fetch('mark_all_notifications_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                loadNotifications(); // Reload notifications after marking all as read
    } else {
                console.error('Failed to mark notifications as read:', data.message);
    }
                })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
            });
        }

    // Function to load notifications
    function loadNotifications() {
        fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                    updateNotificationsDisplay(data.notifications);
                    updateNotificationBadge(data.unread_count);
    } else {
                    console.error('Failed to load notifications:', data.message);
            }
        })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
}

    // Function to update notification badge
    function updateNotificationBadge(unreadCount) {
        const badge = document.querySelector('.notification-icon .badge');
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'block';
    } else {
                badge.style.display = 'none';
    }
        }
    }

    // Function to properly close a modal
    function closeModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                // Remove focus from any focused elements before hiding
                const focusedElement = document.activeElement;
                if (focusedElement) {
                    focusedElement.blur();
                }
                
                modal.hide();
    
                // Clean up modal backdrop and body classes
                setTimeout(() => {
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 150);
            }
        }
    }

    // Add this function to handle image loading with retry
    function loadImageWithRetry(imgElement, basePath, filename, maxRetries = 3) {
        let retryCount = 0;
        
        function tryLoadImage(currentFilename = filename) {
            // Remove 'view/' from basePath if it exists
            const correctedBasePath = basePath.replace('/view', '');
            const fullPath = correctedBasePath + '/uploads/passenger/' + currentFilename;
            console.log(`Attempt ${retryCount + 1} to load image:`, fullPath);

            // Check if the image exists before trying to load it
            fetch(fullPath, { method: 'HEAD' })
                .then(response => {
                    if (response.ok) {
                        imgElement.src = fullPath;
                        console.log('Image exists and will be loaded:', fullPath);
                    } else {
                        console.log(`Image does not exist: ${fullPath}`);
                        handleImageError();
                    }
                })
                .catch(() => {
                    console.log(`Error checking image existence: ${fullPath}`);
                    handleImageError();
                });
            
            function handleImageError() {
                retryCount++;
                
                if (retryCount < maxRetries) {
                    // Try different variations of the filename
                    const variations = [
                        currentFilename,
                        currentFilename.replace(/\d+\.jpg$/, '1746402706.jpg'),
                        currentFilename.replace(/\d+\.jpg$/, '1746402706.png'),
                        currentFilename.replace(/\.jpg$/, '.png')
                    ];
                    
                    if (retryCount < variations.length) {
                        console.log('Trying alternative filename:', variations[retryCount]);
                        tryLoadImage(variations[retryCount]);
                    } else {
                        // All retries failed, use fallback
                        console.log('All retries failed, using fallback image');
                        imgElement.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
            }
        } else {
                    // All retries failed, use fallback
                    console.log('All retries failed, using fallback image');
                    imgElement.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
                }
        }
        }
        
        tryLoadImage();
    }

    // Update the openBookingModal function to use the new image loading function
    function openBookingModal(data) {
        // Update modal content with booking details
        document.getElementById('passengerName').textContent = data.fullname || 'Unknown';
        document.getElementById('passengerPhone').textContent = data.passenger_phone || 'Not available';
        document.getElementById('pickupLocation').textContent = data.pickup_location || 'Not specified';
        document.getElementById('dropoffLocation').textContent = data.destination || 'Not specified';
        document.getElementById('seats').textContent = data.seats || '0';
        document.getElementById('fare').textContent = '₱' + (data.fare || '0');

        // Set passenger photo with correct path
        const passengerPhoto = document.getElementById('passengerPhoto');
        if (data.profile_pic) {
            const baseUrl = window.location.protocol + '//' + window.location.hostname + '/MAMBYAHE';
            loadImageWithRetry(passengerPhoto, baseUrl, data.profile_pic);
        } else {
            passengerPhoto.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
    }

        // Set call button href
        const callBtn = document.getElementById('callPassengerBtn');
        if (data.passenger_phone && data.passenger_phone !== 'Not available') {
            callBtn.href = 'tel:' + data.passenger_phone;
            callBtn.style.display = 'block';
        } else {
            callBtn.style.display = 'none';
        }

        // Set up confirm and reject buttons
        const confirmBtn = document.getElementById('confirmBooking');
        const rejectBtn = document.getElementById('rejectBooking');

        if (confirmBtn) {
            confirmBtn.onclick = function() {
                // Handle booking confirmation
                fetch('confirm_booking.php', {
        method: 'POST',
        headers: {
                        'Content-Type': 'application/json'
        },
        body: JSON.stringify({
                        booking_id: data.booking_id,
                        passenger_id: data.passenger_id
        })
    })
    .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Close the modal
                        closeModal('bookingDetailsModal');
                        
                        // Show the ride sheet first
                        const rideSheet = document.getElementById("rideSheet");
                        if (rideSheet) {
                            // Set the booking and passenger IDs
                            rideSheet.dataset.bookingId = data.booking_id;
                            rideSheet.dataset.passengerId = data.passenger_id;
            
                            // Update ride sheet content
                            document.getElementById("sheetPassengerName").textContent = data.fullname || "Unknown";
                            document.getElementById("sheetPickup").textContent = data.pickup_location || "Not specified";
                            document.getElementById("sheetDropoff").textContent = data.destination || "Not specified";
                            
                            // Set passenger photo
                            const sheetPassengerImg = document.getElementById("sheetPassengerImg");
                            if (data.profile_pic) {
                                const baseUrl = window.location.protocol + '//' + window.location.hostname + '/MAMBYAHE';
                                loadImageWithRetry(sheetPassengerImg, baseUrl, data.profile_pic);
                            } else {
                                sheetPassengerImg.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
            }
            
                            // Set call button
                            const sheetCallBtn = document.getElementById("sheetCallBtn");
                            if (data.passenger_phone && data.passenger_phone !== 'Not available') {
                                sheetCallBtn.href = 'tel:' + data.passenger_phone;
                                sheetCallBtn.style.display = 'block';
                            } else {
                                sheetCallBtn.style.display = 'none';
                            }
                            
                            // Show the bottom sheet
                            rideSheet.classList.add("show");
                            
                            // Show success message after a short delay
                            setTimeout(() => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Booking Confirmed',
                                    text: 'You have accepted this ride request',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }, 500); // 500ms delay to ensure bottom sheet is visible first
            }
        } else {
                    // Handle different error cases
                    if (result.status === 'confirmed') {
                        // Show "on the go" message
                        Swal.fire({
                            icon: 'info',
                            title: 'Booking Status',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else if (result.redirect) {
                        // Show message and redirect
                        Swal.fire({
                            icon: 'info',
                            title: 'Booking Status',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = result.redirect;
                        });
                    } else {
                        // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                        text: result.message || 'Failed to confirm booking'
            });
                    }
        }
    })
    .catch(error => {
                    console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
                        text: 'An error occurred while confirming the booking'
        });
    });
            };
        }

        if (rejectBtn) {
            rejectBtn.onclick = function() {
                // Close the booking details modal first
                closeModal('bookingDetailsModal');
                
                // Show rejection reason modal after a short delay
                setTimeout(() => {
                    const rejectionModal = new bootstrap.Modal(document.getElementById('rejectionReasonModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    rejectionModal.show();
                }, 300);

                // Handle rejection form submission
                document.getElementById('rejectionForm').onsubmit = function(e) {
                    e.preventDefault();
                    const reason = document.getElementById('rejectionReason').value;

                    fetch('reject_booking.php', {
        method: 'POST',
        headers: {
                            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
                            booking_id: data.booking_id,
                            passenger_id: data.passenger_id,
                            reason: reason
        })
    })
    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Close the rejection modal
                            closeModal('rejectionReasonModal');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                                title: 'Booking Rejected',
                                text: 'The passenger has been notified',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                                text: result.message || 'Failed to reject booking'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
                            text: 'An error occurred while rejecting the booking'
        });
    });
                };
            };
}

        // Show the modal
        const bookingModal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
        bookingModal.show();
    }

    // Initialize everything when the page loads
    document.addEventListener("DOMContentLoaded", function() {
        // Handle profile image loading with correct path
        const profileImages = document.querySelectorAll("img[src*='passenger']");
        profileImages.forEach(img => {
            const filename = img.src.split("/").pop();
            if (filename) {
                const baseUrl = window.location.protocol + '//' + window.location.hostname + '/MAMBYAHE';
                loadImageWithRetry(img, baseUrl, filename);
            }
        });

        // Initialize notifications
        function initializeNotifications() {
            const notificationDropdown = document.querySelector('.notification-dropdown');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    markAllNotificationsAsRead();
                });
            }

            // Load initial notifications
            loadNotifications();
            
            // Set up periodic notification refresh
            setInterval(loadNotifications, 30000); // Refresh every 30 seconds
        }

        // Initialize notifications
        initializeNotifications();

        // Set up ride sheet handlers
        const rideSheet = document.getElementById("rideSheet");
        if (rideSheet) {
            rideSheet.addEventListener("click", function(e) {
    if (e.target === this) {
        e.stopPropagation();
    }
});
        }

        const sheetHandle = document.getElementById("sheetHandle");
        if (sheetHandle) {
            sheetHandle.onclick = function(e) {
    e.stopPropagation();
};
        }

        // Check arrival state periodically
        function checkAndRestoreArrivalState() {
            if (rideSheet && rideSheet.dataset.bookingId && rideSheet.dataset.passengerId) {
                if (!rideSheet.classList.contains("show")) {
                    rideSheet.classList.add("show");
                }
            }
        }

        // Initial check and periodic updates
        checkAndRestoreArrivalState();
        setInterval(checkAndRestoreArrivalState, 5000);
    });

    // Handle Report Icon Click
    document.getElementById('sheetReportBtn').addEventListener('click', function () {
        // Open the report modal
        const reportModal = new bootstrap.Modal(document.getElementById('reportPassengerModal'));
        reportModal.show();

        // Pre-fill passenger details if needed
        const passengerId = document.getElementById('rideSheet').dataset.passengerId;
        console.log(`Reporting passenger with ID: ${passengerId}`);
    });

    // Handle Report Form Submission
    document.getElementById('reportPassengerForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const passengerId = document.getElementById('rideSheet').dataset.passengerId;
        const reason = document.getElementById('reportReason').value;

        // Send the report to the server
        fetch('submit_report.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                passenger_id: passengerId,
                reason: reason,
            }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Close the modal
                const reportModal = bootstrap.Modal.getInstance(document.getElementById('reportPassengerModal'));
                reportModal.hide();

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Report Submitted',
                    text: 'Your report has been submitted to MTFRB.',
                    showConfirmButton: false,
                    timer: 2000,
                });

                // Reset the form
                document.getElementById('reportPassengerForm').reset();
            } else {
                throw new Error(data.message);
            }
        })
        .catch((error) => {
            console.error('Error submitting report:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to submit the report. Please try again.',
            });
        });
    });
</script>
</body>
</html>