<?php
require_once '../config/database.php'; // or wherever your PDO connection is defined

session_start();

// Set page title
$page_title = "Passenger Dashboard";

// Add this at the top of the file, after session_start()
$baseUrl = 'http://localhost/MAMBYAHE';

// Redirect to login if not logged in
if (!isset($_SESSION['passenger_id'], $_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['passenger_id'];
$passenger_name = htmlspecialchars($_SESSION['username']);

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Count unread notifications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS unread_count 
        FROM notifications n 
        WHERE n.passenger_id = :passenger_id 
        AND n.user_type = 'passenger'
        AND n.status = :status
    ");
    $stmt->execute(['passenger_id' => $passenger_id, 'status' => 'unread']);
    $unread_count = $stmt->fetchColumn();

    // Fetch latest notifications
    $stmt = $pdo->prepare("
        SELECT n.*, d.fullname as driver_name, d.username as driver_username, d.plate_no as plate_number, d.profile_pic as driver_pic, b.id as booking_id
        FROM notifications n 
        LEFT JOIN driver d ON n.driver_id = d.driver_id
        LEFT JOIN bookings b ON n.booking_id = b.id
        WHERE n.passenger_id = :passenger_id 
        AND n.user_type = 'passenger'
        ORDER BY n.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute(['passenger_id' => $passenger_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get profile picture
    $stmt = $pdo->prepare("SELECT profile_pic FROM passenger WHERE passenger_id = :passenger_id");
    $stmt->execute(['passenger_id' => $passenger_id]);
    $passenger = $stmt->fetch(PDO::FETCH_ASSOC);

    $profilePic = !empty($passenger['profile_pic']) ? 'uploads/' . $passenger['profile_pic'] : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';

    // Get fare matrix
    $fares = $pdo->query("SELECT DISTINCT origin, destination, fare, students_senior FROM fare_matrix");

    // Fetch only online drivers with debug information
    try {
        $drivers = $pdo->query("
            SELECT d.driver_id, d.fullname, d.plate_no, d.is_online, d.status
            FROM driver d 
            WHERE d.is_online = 'online' 
            AND d.status = 'approved'
        ");
        
        // Debug: Print the number of drivers found
        $driverCount = $drivers->rowCount();
        error_log("Number of online drivers found: " . $driverCount);
        
        // Debug: Print all drivers for verification
        $allDrivers = $pdo->query("SELECT driver_id, fullname, is_online, status FROM driver");
        while ($driver = $allDrivers->fetch(PDO::FETCH_ASSOC)) {
            error_log("Driver: " . $driver['fullname'] . 
                     ", Online: " . $driver['is_online'] . 
                     ", Status: " . $driver['status']);
        }
    } catch (PDOException $e) {
        error_log("Error fetching drivers: " . $e->getMessage());
        die("Error fetching drivers: " . $e->getMessage());
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: logout.php");
    exit();
}

if (isset($_SESSION['tab_token'])):
?>
<script>
var serverTabToken = '<?php echo $_SESSION['tab_token']; ?>';
if (sessionStorage.getItem('tab_token') !== serverTabToken) {
    window.location.href = '../logout.php';
}
window.addEventListener('unload', function() {
    navigator.sendBeacon('../logout.php');
});
</script>
<?php endif; ?>

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
      background: white;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
        transition: background-color 0.2s;
    }

    .notification-icon:hover {
        background-color: #f8f9fa;
    }

    /* Update existing styles */
    .leaflet-control-zoom {
        margin-top: 70px !important; /* Adjust zoom controls position */
        margin-right: 10px !important;
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

    .notification-dropdown {
        width: 350px;
        max-width: 95vw;
        min-width: 250px;
        word-break: break-word;
        white-space: normal;
    }

    /* Book Now Button Styles */
    .book-now-btn {
        position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
        background: #198754;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 15px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .book-now-btn:hover {
        background: #146c43;
        transform: translateX(-50%) translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }

    .book-now-btn i {
        margin-right: 8px;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 1rem;
      border: none;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }

    .modal-header {
      border-bottom: none;
        padding: 1.5rem 1.5rem 0.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: none;
        padding: 0.5rem 1.5rem 1.5rem;
    }

    /* Form Styles */
    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #dee2e6;
    }

    .form-control:focus, .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25,135,84,0.15);
    }

    .form-label {
      font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    /* Button Styles */
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .btn-success {
      background: #198754;
      border: none;
    }

    .btn-success:hover {
      background: #146c43;
    }

    /* Notification Styles */
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }
  </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <?php include 'components/passenger_sidebar.php'; ?>

<!-- Map Container -->
<div id="map"></div>

<!-- Menu Button -->
<button class="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
  <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <div class="user-card text-center">
      <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; margin-bottom: 10px;">
      <br>
      <strong><?php echo $passenger_name; ?></strong><br>
      <span class="text-success"><i class="fas fa-circle"></i> Online</span><br>
      <a href="passenger_profile.php" class="btn btn-sm btn-outline-primary mt-2">View Profile</a>
    </div>
    <ul class="list-unstyled">
      <li><a href="dashboardPassenger.php" class="d-block py-2"><i class="fas fa-home me-2"></i> Home</a></li>
      <li><a href="ride_details.php" class="d-block py-2"><i class="fas fa-car me-2"></i> Ride Details</a></li>
      <li><a href="support.php" class="d-block py-2"><i class="fas fa-headset me-2"></i> Contact Support</a></li>
      <li>
        <a href="#" class="d-block py-2 text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
          <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</div>

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

<!-- Notification Icon -->
<div class="notification-icon dropdown">
  <a class="dropdown-toggle text-dark" href="#" role="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-bell fs-4"></i>
    <?php if ($unread_count > 0): ?>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        <?= $unread_count ?>
      </span>
    <?php endif; ?>
  </a>
  <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notifDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
    <li class="d-flex justify-content-between align-items-center px-2 mb-2">
      <span class="fw-bold">Notifications</span>
      <div>
        <button class="btn btn-sm btn-outline-success me-1" id="markAllReadBtn">Mark all as read</button>
      </div>
    </li>
    <hr class="my-1">
    <?php if (!empty($notifications)): ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="dropdown-item notification-item <?= $notification['status'] === 'unread' ? 'bg-light' : '' ?>"
                 data-notification-id="<?= $notification['notification_id'] ?>"
                 data-booking-id="<?= $notification['booking_id'] ?>"
                 data-driver-id="<?= $notification['driver_id'] ?>"
                 data-type="<?= htmlspecialchars($notification['type']) ?>"
                 data-message="<?= htmlspecialchars($notification['message']) ?>"
                 data-created-at="<?= $notification['created_at'] ?>"
                 style="cursor: pointer;">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <?php if (!empty($notification['profile_pic'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($notification['profile_pic']); ?>" 
                                 class="rounded-circle" 
                                 alt="Profile" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
            </div>
                        <?php endif; ?>
          </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?php echo htmlspecialchars($notification['type']); ?></h6>
                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($notification['created_at'])); ?></small>
                        </div>
                        <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                    </div>
                </div>
            </div>
            <div class="dropdown-divider"></div>
      <?php endforeach; ?>
    <?php else: ?>
          <div class="dropdown-item text-center py-3">
            <i class="fas fa-bell-slash text-muted mb-2"></i>
            <p class="mb-0">No notifications</p>
          </div>
    <?php endif; ?>
    <li><a href="view_all_notifications.php" class="dropdown-item text-center">See all notifications</a></li>
  </ul>
</div>

<!-- Rejection Notification Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fas fa-times-circle me-2"></i>Ride Rejection
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <!-- Driver Information -->
          <div class="d-flex align-items-center mb-3">
            <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
            <div class="flex-grow-1">
              <h6 class="mb-1" id="rejectionDriverName"></h6>
              <p class="text-muted mb-0" id="rejectionPlateNumber"></p>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-danger btn-sm" id="reportRejectionBtn">
                <i class="fas fa-flag"></i> Report
              </button>
            </div>
          </div>
          <!-- Rejection Details -->
          <div class="card bg-light">
            <div class="card-body">
              <h6 class="card-title mb-3">
                <i class="fas fa-info-circle text-danger me-2"></i>Rejection Details
              </h6>
              <div class="mb-3">
                <small class="text-muted d-block mb-1">Reason for Rejection</small>
                <p id="rejectionReason" class="mb-0"></p>
              </div>
              <div class="text-muted">
                <small id="rejectionDateTime"></small>
              </div>
              <div id="rejectionBookingDetails"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fas fa-flag me-2"></i>Report Driver
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="reportForm">
          <div class="mb-3">
            <label class="form-label">Driver Name</label>
            <input type="text" class="form-control" id="reportDriverName" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Plate Number</label>
            <input type="text" class="form-control" id="reportPlateNumber" readonly>
          </div>
          <div class="mb-3">
            <label for="reportReason" class="form-label">Reason for Report</label>
            <select class="form-select" id="reportReason" required>
              <option value="">Select a reason</option>
              <option value="unprofessional">Unprofessional Behavior</option>
              <option value="discriminatory">Discriminatory Rejection</option>
              <option value="inappropriate">Inappropriate Reason</option>
              <option value="no_show">Driver No-Show</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="reportDetails" class="form-label">Additional Details</label>
            <textarea class="form-control" id="reportDetails" rows="3" placeholder="Please provide more details about your report..." required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="submitReport">Submit Report</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirmation Notification Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Booking Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <div class="d-flex align-items-center mb-3">
            <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
            <div class="flex-grow-1">
              <h6 class="mb-1" id="confirmationDriverName"></h6>
              <p class="text-muted mb-0" id="confirmationPlateNumber"></p>
            </div>
            <a href="#" class="btn btn-primary btn-sm" id="callDriverBtn">
              <i class="fas fa-phone"></i>
            </a>
          </div>
          <div class="booking-details">
            <div class="mb-3">
              <small class="text-muted d-block mb-1">Pickup Location</small>
              <p id="confirmationPickup" class="mb-0"></p>
            </div>
            <div class="mb-3">
              <small class="text-muted d-block mb-1">Drop-off Location</small>
              <p id="confirmationDropoff" class="mb-0"></p>
            </div>
            <div class="row">
              <div class="col-6">
                <small class="text-muted d-block mb-1">Seats</small>
                <p id="confirmationSeats" class="mb-0"></p>
              </div>
              <div class="col-6">
                <small class="text-muted d-block mb-1">Total Fare</small>
                <p id="confirmationFare" class="mb-0"></p>
              </div>
            </div>
          </div>
          <div class="text-muted mt-3">
            <small id="confirmationDateTime"></small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

    <!-- Arrival Confirmation Modal -->
    <div class="modal fade" id="arrivalConfirmationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">
              <i class="fas fa-check-circle me-2"></i>Driver Arrived
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
            <div class="mb-4">
              <!-- Driver Information -->
              <div class="d-flex align-items-center mb-3">
                <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                <div class="flex-grow-1">
                  <h6 class="mb-1" id="arrivalDriverName"></h6>
                  <p class="text-muted mb-0" id="arrivalPlateNumber"></p>
            </div>
                <a href="#" class="btn btn-primary btn-sm" id="arrivalCallDriverBtn">
                  <i class="fas fa-phone"></i>
                </a>
            </div>
              <!-- Arrival Details -->
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="card-title mb-3">
                    <i class="fas fa-info-circle text-success me-2"></i>Arrival Details
                  </h6>
                  <div class="mb-3">
                    <small class="text-muted d-block mb-1">Pickup Location</small>
                    <p id="arrivalPickup" class="mb-0"></p>
          </div>
                  <div class="mb-3">
                    <small class="text-muted d-block mb-1">Drop-off Location</small>
                    <p id="arrivalDropoff" class="mb-0"></p>
            </div>
                  <div class="text-muted">
                    <small id="arrivalDateTime"></small>
              </div>
            </div>
          </div>
            </div>
            <div class="text-center">
              <button type="button" class="btn btn-success" id="confirmArrivalBtn">
                <i class="fas fa-check me-2"></i>Confirm Arrival
              </button>
            </div>
            </div>
          </div>
            </div>
          </div>

    <!-- Review & Feedback Modal -->
    <div class="modal fade" id="reviewFeedbackModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Rate Your Ride</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
          <div class="modal-body">
            <form id="reviewForm">
              <!-- Driver Info -->
              <div class="d-flex align-items-center mb-4">
                <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                <div>
                  <h6 class="mb-1" id="reviewDriverName"></h6>
                  <p class="text-muted mb-0" id="reviewPlateNumber"></p>
      </div>
    </div>
              
              <!-- Rating -->
              <div class="mb-4">
                <label class="form-label">Rate your experience</label>
                <div class="star-rating">
                  <i class="far fa-star" data-rating="1"></i>
                  <i class="far fa-star" data-rating="2"></i>
                  <i class="far fa-star" data-rating="3"></i>
                  <i class="far fa-star" data-rating="4"></i>
                  <i class="far fa-star" data-rating="5"></i>
  </div>
</div>

              <!-- Feedback -->
              <div class="mb-4">
                <label for="feedbackText" class="form-label">Your feedback</label>
                <textarea class="form-control" id="feedbackText" rows="3" placeholder="Share your experience..."></textarea>
</div>

              <!-- Report Option -->
              <div class="mb-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="reportIssue">
                  <label class="form-check-label" for="reportIssue">
                    Report an issue
                  </label>
          </div>
                <div id="reportDetails" class="mt-2 d-none">
                  <textarea class="form-control" rows="2" placeholder="Please describe the issue..."></textarea>
            </div>
          </div>
        </form>
      </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Skip</button>
            <button type="button" class="btn btn-primary" id="submitReviewBtn">Submit</button>
      </div>
    </div>
  </div>
</div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book a Ride</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        <div class="mb-3">
                            <label for="pickup" class="form-label">Pickup Location</label>
                            <input type="text" class="form-control" id="pickup" list="pickupOptions" required>
                            <datalist id="pickupOptions"></datalist>
            </div>
                        <div class="mb-3">
                            <label for="destination" class="form-label">Drop-off Location</label>
                            <input type="text" class="form-control" id="destination" list="destinationOptions" required>
                            <datalist id="destinationOptions"></datalist>
          </div>
                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Pickup Date</label>
                            <input type="date" class="form-control" id="booking_date" required>
        </div>
                        <div class="mb-3">
                            <label for="booking_time" class="form-label">Pickup Time</label>
                            <input type="time" class="form-control" id="booking_time" required>
      </div>
                        <div class="mb-3">
                            <label for="seats" class="form-label">Number of Seats</label>
                            <select class="form-select" id="seats" required>
                                <option value="">Select seats</option>
                                <option value="1">1 Seat</option>
                                <option value="2">2 Seats</option>
                                <option value="3">3 Seats</option>
                                <option value="4">4 Seats</option>
                            </select>
          </div>
                        <div class="mb-3">
                            <label for="driver" class="form-label">Select Driver</label>
                            <select class="form-select" id="driver" required>
                                <option value="">Select a driver</option>
                            </select>
          </div>
                        <div class="mb-3">
                            <label for="fare" class="form-label">Total Fare</label>
                            <input type="text" class="form-control" id="fare" readonly>
                        </div>
        </form>
      </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmBooking">Book Now</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const map = L.map('map').setView([14.5995, 120.9842], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

    // Get current location
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const currentMarker = L.marker([lat, lng])
          .addTo(map)
          .bindPopup("<strong>You are here</strong>")
          .openPopup();
        map.setView([lat, lng], 15);
      },
      function(error) {
        console.error("Geolocation failed: ", error.message);
      }
    );
  }

    // Initialize fare matrix
  const fareMatrix = <?php
    $fares_array = [];
    while ($row = $fares->fetch()) {
      $fares_array[] = [
        'origin' => $row['origin'],
        'destination' => $row['destination'],
        'fare' => floatval($row['fare']),
        'students_senior' => floatval($row['students_senior'])
      ];
    }
    echo json_encode($fares_array);
  ?>;

    // Populate datalist options
  const pickupSet = new Set();
  const destinationSet = new Set();
  fareMatrix.forEach(f => {
    pickupSet.add(f.origin);
    destinationSet.add(f.destination);
  });

  const pickupOptions = document.getElementById('pickupOptions');
  const destinationOptions = document.getElementById('destinationOptions');
    
    if (pickupOptions && destinationOptions) {
  pickupSet.forEach(loc => {
    const opt = document.createElement('option');
    opt.value = loc;
    pickupOptions.appendChild(opt);
  });
  destinationSet.forEach(loc => {
    const opt = document.createElement('option');
    opt.value = loc;
    destinationOptions.appendChild(opt);
  });
    }

    // Book Now button click handler
    const bookNowBtn = document.getElementById('bookNowBtn');
    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', function() {
            const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
            bookingModal.show();
        });
    }

    // Set default date and time
    const today = new Date();
    const dateStr = today.toISOString().split('T')[0];
    const bookingDate = document.getElementById('booking_date');
    if (bookingDate) {
        bookingDate.value = dateStr;
        bookingDate.min = dateStr;
    }

    // Set default time to at least 30 minutes in the future
    const now = new Date();
    now.setMinutes(now.getMinutes() + 30);
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = Math.ceil(now.getMinutes() / 30) * 30;
    const timeStr = `${hours}:${minutes.toString().padStart(2, '0')}`;
    const bookingTime = document.getElementById('booking_time');
    if (bookingTime) {
        bookingTime.value = timeStr;
        bookingTime.min = timeStr;
    }

    // Load available drivers
    function loadAvailableDrivers() {
        fetch('get_available_drivers.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const driverSelect = document.getElementById('driver');
                    if (driverSelect) {
                        driverSelect.innerHTML = '<option value="">Select a driver</option>';
                        data.drivers.forEach(driver => {
                            const option = document.createElement('option');
                            option.value = driver.driver_id;
                            option.textContent = `${driver.fullname} (${driver.plate_no})`;
                            driverSelect.appendChild(option);
                        });
                    }
                }
            })
            .catch(error => console.error('Error loading drivers:', error));
    }

    // Load drivers when booking modal opens
    const bookingModal = document.getElementById('bookingModal');
    if (bookingModal) {
        bookingModal.addEventListener('show.bs.modal', loadAvailableDrivers);
    }

    // Update fare calculation
  function calculateFare() {
        const pickup = document.getElementById('pickup');
        const destination = document.getElementById('destination');
        const seats = document.getElementById('seats');
        const fare = document.getElementById('fare');

        if (pickup && destination && seats && fare) {
            const pickupValue = pickup.value;
            const destinationValue = destination.value;
            const seatsValue = parseInt(seats.value) || 0;

            if (pickupValue && destinationValue && seatsValue > 0) {
      const fareInfo = fareMatrix.find(f => 
                    f.origin === pickupValue && f.destination === destinationValue
      );

      if (fareInfo) {
                    const totalFare = fareInfo.fare * seatsValue;
                    fare.value = `₱${totalFare.toFixed(2)}`;
      } else {
                    fare.value = 'Route not found';
      }
    } else {
                fare.value = '';
            }
        }
    }

    // Add event listeners for fare calculation
    const pickupInput = document.getElementById('pickup');
    const destinationInput = document.getElementById('destination');
    const seatsInput = document.getElementById('seats');

    if (pickupInput) pickupInput.addEventListener('change', calculateFare);
    if (destinationInput) destinationInput.addEventListener('change', calculateFare);
    if (seatsInput) seatsInput.addEventListener('input', calculateFare);

    // Update booking submission
    const confirmBookingBtn = document.getElementById('confirmBooking');
    if (confirmBookingBtn) {
        confirmBookingBtn.addEventListener('click', function() {
    const form = document.getElementById('bookingForm');
            if (!form || !form.checkValidity()) {
                if (form) form.reportValidity();
      return;
    }

    const bookingData = {
      pickup: document.getElementById('pickup').value,
                destination: document.getElementById('destination').value,
      booking_date: document.getElementById('booking_date').value,
      booking_time: document.getElementById('booking_time').value,
                seats: parseInt(document.getElementById('seats').value),
                driver_id: document.getElementById('driver').value,
                total_fare: parseFloat(document.getElementById('fare').value.replace('₱', ''))
            };

            // Validate data
            if (!bookingData.pickup || !bookingData.destination) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Please select pickup and drop-off points'
      });
      return;
    }

    if (!bookingData.seats || bookingData.seats < 1 || bookingData.seats > 4) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Please select between 1 and 4 seats'
      });
      return;
    }

            if (!bookingData.driver_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a driver'
      });
      return;
    }

    if (!bookingData.total_fare || isNaN(bookingData.total_fare)) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Invalid fare amount'
      });
      return;
    }

            // Show loading state
            Swal.fire({
                title: 'Processing Booking',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send booking data
    fetch('process_booking.php', {
      method: 'POST',
      headers: {
                    'Content-Type': 'application/json'
      },
      body: JSON.stringify(bookingData)
    })
            .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
                        text: 'Your booking has been submitted.',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
                        const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                        if (bookingModal) bookingModal.hide();
                        if (form) form.reset();
                        loadBookings();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Booking Failed',
          text: data.message || 'An error occurred while processing your booking.'
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'An error occurred while processing your booking. Please try again.'
      });
    });
  });
    }

    // Load notifications
    function loadNotifications() {
        fetch('get_notifications.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
                    updateNotificationsDisplay(data.notifications);
                    const badge = document.querySelector('.notification-badge');
        if (badge) {
                        const unreadCount = data.notifications.filter(n => n.status === 'unread').length;
                        badge.textContent = unreadCount;
                        badge.style.display = unreadCount > 0 ? 'flex' : 'none';
                    }
                }
            })
            .catch(error => console.error('Error loading notifications:', error));
    }

    // Load notifications on page load and set refresh interval
    loadNotifications();
    setInterval(loadNotifications, 30000);

    // Mark all as read functionality
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            fetch('mark_all_notifications_read.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
                }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => console.error('Error marking notifications as read:', error));
        });
    }

    // Update the notification display function
    function updateNotificationsDisplay(notifications) {
        const container = document.querySelector('.dropdown-menu');
        if (!container) {
            console.error('Notification container not found');
      return;
    }

        // Clear all content
        container.innerHTML = '';

        // Rebuild header
        const header = document.createElement('li');
        header.className = 'd-flex justify-content-between align-items-center px-2 mb-2';
        header.innerHTML = `
            <span class="fw-bold">Notifications</span>
            <div>
                <button class="btn btn-sm btn-outline-success me-1" id="markAllReadBtn">Mark all as read</button>
            </div>
        `;
        container.appendChild(header);
        container.appendChild(document.createElement('hr'));

        // Add notifications or no-notifications message
        if (!notifications || notifications.length === 0) {
            const noNotifications = document.createElement('div');
            noNotifications.className = 'dropdown-item text-center py-3';
            noNotifications.innerHTML = `
                <i class="fas fa-bell-slash text-muted mb-2"></i>
                <p class="mb-0">No notifications</p>
            `;
            container.appendChild(noNotifications);
      } else {
            notifications.forEach(notification => {
                if (!notification) return;
                const notificationItem = document.createElement('div');
                notificationItem.className = `dropdown-item notification-item ${notification.status === 'unread' ? 'bg-light' : ''}`;
                notificationItem.style.cursor = 'pointer';
                const content = document.createElement('div');
                content.className = 'd-flex align-items-start';
                const imgDiv = document.createElement('div');
                imgDiv.className = 'me-3';
                const img = document.createElement('img');
                img.className = 'rounded-circle';
                img.style.width = '40px';
                img.style.height = '40px';
                img.style.objectFit = 'cover';
                if (notification.driver_pic) {
                    const baseUrl = window.location.protocol + '//' + window.location.hostname + '/MAMBYAHE';
                    img.src = `${baseUrl}/uploads/driver_ids/${notification.driver_pic}`;
                    img.onerror = function() {
                        this.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
                    };
                } else {
                    img.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
                }
                imgDiv.appendChild(img);
                content.appendChild(imgDiv);
                const textDiv = document.createElement('div');
                textDiv.className = 'flex-grow-1';
                const headerDiv = document.createElement('div');
                headerDiv.className = 'd-flex justify-content-between align-items-center';
                headerDiv.innerHTML = `
                    <h6 class="mb-0">${notification.type}</h6>
                    <small class="text-muted">${new Date(notification.created_at).toLocaleString()}</small>
                `;
                const messageDiv = document.createElement('p');
                messageDiv.className = 'mb-0 text-muted';
                messageDiv.textContent = notification.message;
                textDiv.appendChild(headerDiv);
                textDiv.appendChild(messageDiv);
                content.appendChild(textDiv);
                notificationItem.appendChild(content);
                container.appendChild(notificationItem);
                container.appendChild(document.createElement('hr'));
                notificationItem.addEventListener('click', () => {
                    handleNotificationClick(notification);
                });
            });
        }

        // Rebuild footer
        const footer = document.createElement('li');
        footer.innerHTML = `<a href="view_all_notifications.php" class="dropdown-item text-center">See all notifications</a>`;
        container.appendChild(footer);
    }

    // Update the notification click handler
    function handleNotificationClick(notification) {
        const type = notification.type;
        const bookingId = notification.booking_id;
        const driverId = notification.driver_id;

        // First mark the notification as read
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notification.notification_id })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error('Failed to mark notification as read');
            }

            // Handle different notification types
            switch(type) {
                case 'Booking Accepted':
                case 'Booking Confirmed':
                    // Show confirmation modal
                    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                    document.getElementById('confirmationDriverName').textContent = notification.driver_name || 'Unknown Driver';
                    document.getElementById('confirmationPlateNumber').textContent = notification.plate_number || 'Not available';
                    document.getElementById('confirmationPickup').textContent = notification.pickup || 'Not specified';
                    document.getElementById('confirmationDropoff').textContent = notification.destination || 'Not specified';
                    document.getElementById('confirmationSeats').textContent = notification.seats || 'Not specified';
                    document.getElementById('confirmationFare').textContent = notification.total_fare || 'Not specified';
                    document.getElementById('confirmationDateTime').textContent = new Date(notification.created_at).toLocaleString();
                    
                    // Set call button
                    const callBtn = document.getElementById('callDriverBtn');
                    if (notification.driver_phone) {
                        callBtn.href = `tel:${notification.driver_phone}`;
                        callBtn.style.display = 'block';
            } else {
                        callBtn.style.display = 'none';
                    }
                    
                    confirmationModal.show();
                    break;
                case 'Booking Cancelled':
                case 'booking_rejected':
                case 'rejection':
                    // Show rejection modal
                    const rejectionModal = new bootstrap.Modal(document.getElementById('rejectionModal'));
                    document.getElementById('rejectionDriverName').textContent = notification.driver_name || 'Unknown Driver';
                    document.getElementById('rejectionPlateNumber').textContent = notification.plate_number || 'Not available';
                    let reason = notification.reason || '';
                    if (!reason && notification.message) {
                        const match = notification.message.match(/Reason: ([^\n]+)/);
                        if (match) reason = match[1];
                    }
                    document.getElementById('rejectionReason').textContent = reason || 'No reason provided';
                    document.getElementById('rejectionDateTime').textContent = new Date(notification.created_at).toLocaleString();
                    document.getElementById('rejectionBookingDetails').innerHTML = `
                        <strong>Booking Details:</strong><br>
                        Pickup: ${notification.pickup || 'Not specified'}<br>
                        Drop-off: ${notification.destination || 'Not specified'}<br>
                        Seats: ${notification.seats || 'Not specified'}<br>
                        Total Fare: ${notification.total_fare || 'Not specified'}
                    `;
                    rejectionModal.show();
                    break;
                case 'Driver Arrived':
                    // Show arrival confirmation modal
                    const arrivalModal = new bootstrap.Modal(document.getElementById('arrivalConfirmationModal'));
                    document.getElementById('arrivalDriverName').textContent = notification.driver_name || 'Unknown Driver';
                    document.getElementById('arrivalPlateNumber').textContent = notification.plate_number || 'Not available';
                    document.getElementById('arrivalPickup').textContent = notification.pickup || 'Not specified';
                    document.getElementById('arrivalDropoff').textContent = notification.destination || 'Not specified';
                    document.getElementById('arrivalDateTime').textContent = new Date(notification.created_at).toLocaleString();
                    
                    // Set booking and driver IDs for confirmation
                    const confirmBtn = document.getElementById('confirmArrivalBtn');
                    confirmBtn.dataset.bookingId = bookingId;
                    confirmBtn.dataset.driverId = driverId;
                    
                    arrivalModal.show();
                    break;
            }
        })
        .catch(error => {
            console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                text: 'Failed to process notification'
                });
            });
    }
});
</script>

    <button class="book-now-btn" id="bookNowBtn">
      <i class="fas fa-plus"></i> Book Now
    </button>

</body>
</html>