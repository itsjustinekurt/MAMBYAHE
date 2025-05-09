<?php 
session_start();
include('sidebar.php');
$user_id = $_SESSION['passenger_id'];

$conn = new mysqli("localhost", "root", "", "user_auth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get passenger name
$sql_passenger = "SELECT fullname FROM passenger WHERE passenger_id = ?";
$stmt_passenger = $conn->prepare($sql_passenger);
$stmt_passenger->bind_param("i", $user_id);
$stmt_passenger->execute();
$stmt_passenger->bind_result($passenger_name);
$stmt_passenger->fetch();
$stmt_passenger->close();

// Query to get pending bookings
$sql_pending = "
SELECT b.*, d.fullname AS driver_name, d.phone AS driver_phone
FROM bookings b
JOIN driver d ON b.driver_id = d.driver_id
WHERE b.passenger_name = ? AND b.status = 'pending'
ORDER BY b.id DESC
";
$stmt_pending = $conn->prepare($sql_pending);
$stmt_pending->bind_param("s", $passenger_name);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();

// Query to get history bookings
$sql_history = "
SELECT b.*, d.fullname AS driver_name, d.phone AS driver_phone
FROM bookings b
JOIN driver d ON b.driver_id = d.driver_id
WHERE b.passenger_name = ? AND b.status IN ('confirmed','rejected','completed','cancelled')
ORDER BY b.id DESC
";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("s", $passenger_name);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Rides</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"> <!-- FontAwesome for icons -->
  <style>
    body {
      background-color: #f9f9f9;
    }
    .ride-card {
      background-color: white;
      border-radius: 12px;
      margin-bottom: 15px;
      padding: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .ride-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .ride-status {
      font-weight: bold;
      padding: 5px 10px;
      border-radius: 15px;
    }
    .status-completed {
      color: green;
      border: 1px solid green;
    }
    .status-cancelled {
      color: red;
      border: 1px solid red;
    }
    .amount-bar {
      font-weight: bold;
      font-size: 1.2rem;
    }
    .status-pending {
      color: #ffc107;
      border: 1px solid #ffc107;
    }
  </style>
</head>
<body>

  <!-- Sidebar Toggle Button -->
  <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
    <i class="fas fa-bars"></i> Menu
  </button>

  <!-- Sidebar -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <div class="user-card text-center">
        <!-- Display profile picture or default icon -->
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
          <a href="login.php" class="d-block py-2 text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container mt-4">
    <h4 class="mb-3">My Rides</h4>
    <div class="d-flex mb-3">
      <button class="btn btn-outline-secondary me-2 active" id="pendingTabBtn" onclick="showTab('pending')">Pending</button>
      <button class="btn btn-outline-secondary" id="historyTabBtn" onclick="showTab('history')">History</button>
    </div>
    <div id="pendingTab" class="tab-content">
      <?php while ($ride = $result_pending->fetch_assoc()): ?>
        <div class="ride-card">
          <div class="ride-header">
            <div>
              <strong>Pickup:</strong> <?= htmlspecialchars($ride['pickup']) ?><br>
              <strong>Destination:</strong> <?= htmlspecialchars($ride['destination']) ?>
            </div>
            <div>
              <span class="ride-status status-pending">Pending</span>
            </div>
          </div>
          <p><strong>Driver:</strong> <?= htmlspecialchars($ride['driver_name']) ?> (<?= htmlspecialchars($ride['driver_phone']) ?>)</p>
          <p><strong>Date/Time:</strong> 
            <?php if (!empty($ride['created_at'])): ?>
              <?= date("D d/M/y h:iA", strtotime($ride['created_at'])) ?>
            <?php else: ?>
              <span class="text-muted">N/A</span>
            <?php endif; ?>
          </p>
          <div class="amount-bar">Amount: Php <?= number_format($ride['fare'], 2) ?></div>
        </div>
      <?php endwhile; ?>
      <?php if ($result_pending->num_rows === 0): ?>
        <p class="text-center text-muted">No pending rides.</p>
      <?php endif; ?>
    </div>
    <div id="historyTab" class="tab-content" style="display:none;">
      <?php while ($ride = $result_history->fetch_assoc()): ?>
        <div class="ride-card">
          <div class="ride-header">
            <div>
              <strong>Pickup:</strong> <?= htmlspecialchars($ride['pickup']) ?><br>
              <strong>Destination:</strong> <?= htmlspecialchars($ride['destination']) ?>
            </div>
            <div>
              <?php if ($ride['status'] === 'completed'): ?>
                <span class="ride-status status-completed">Completed</span>
              <?php else: ?>
                <span class="ride-status status-cancelled">Cancelled</span>
              <?php endif; ?>
            </div>
          </div>
          <p><strong>Driver:</strong> <?= htmlspecialchars($ride['driver_name']) ?> (<?= htmlspecialchars($ride['driver_phone']) ?>)</p>
          <p><strong>Date/Time:</strong> 
            <?php if (!empty($ride['created_at'])): ?>
              <?= date("D d/M/y h:iA", strtotime($ride['created_at'])) ?>
            <?php else: ?>
              <span class="text-muted">N/A</span>
            <?php endif; ?>
          </p>
          <div class="amount-bar">Amount: Php <?= number_format($ride['fare'], 2) ?></div>
        </div>
      <?php endwhile; ?>
      <?php if ($result_history->num_rows === 0): ?>
        <p class="text-center text-muted">No ride history found.</p>
      <?php endif; ?>
    </div>
    <?php
    $stmt_pending->close();
    $stmt_history->close();
    $conn->close();
    ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    sessionStorage.setItem('last_page', window.location.pathname);
    function showTab(tab) {
      document.getElementById('pendingTab').style.display = (tab === 'pending') ? 'block' : 'none';
      document.getElementById('historyTab').style.display = (tab === 'history') ? 'block' : 'none';
      document.getElementById('pendingTabBtn').classList.toggle('active', tab === 'pending');
      document.getElementById('historyTabBtn').classList.toggle('active', tab === 'history');
    }
  </script>
</body>
</html>
