<?php 
session_start();
include('components/passenger_sidebar_template.php');

// Add header with page title
$page_title = 'Ride Details';

// Header
?>
<div class="header">
    <div class="header-left">
        <button class="menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
            <i class="fas fa-bars fs-4"></i>
        </button>
        <h5 class="mb-0"><?php echo htmlspecialchars($page_title); ?></h5>
    </div>
    <div class="header-right">
        <div class="notification-container">
            <div class="notification-icon dropdown">
                <a class="dropdown-toggle text-dark" href="#" role="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fs-4"></i>
                </a>
                <div class="dropdown-menu notification-dropdown" aria-labelledby="notifDropdown">
                    <div class="notification-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Notifications</h6>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item text-center">
                        <button class="btn btn-link text-muted">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.header {
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    height: 60px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 1002;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.menu-toggle {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
}

.menu-toggle:hover {
    color: #000;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.notification-container {
    position: relative;
}

.notification-icon {
    cursor: pointer;
}

.notification-dropdown {
    width: 350px;
    padding: 10px;
    margin-top: 10px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.notification-header {
    margin-bottom: 10px;
}

.notification-header h6 {
    margin: 0;
    font-size: 0.9rem;
}
</style>

<?php
$conn = new mysqli("localhost", "root", "", "user_auth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get passenger name


// Query to get pending bookings
$sql_pending = "
SELECT b.*, d.fullname AS driver_name, d.phone AS driver_phone
FROM bookings b
JOIN driver d ON b.driver_id = d.driver_id
WHERE b.passenger_id = ? AND b.status = 'pending'
ORDER BY b.id DESC
";
$stmt_pending = $conn->prepare($sql_pending);
$stmt_pending->bind_param("i", $passenger_id);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();

// Query to get history bookings
$sql_history = "
SELECT b.*, d.fullname AS driver_name, d.phone AS driver_phone
FROM bookings b
JOIN driver d ON b.driver_id = d.driver_id
WHERE b.passenger_id = ? AND b.status IN ('confirmed','rejected','completed','cancelled')
ORDER BY b.id DESC
";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $passenger_id);
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
      margin: 0;
      padding: 0;
      height: 100vh;
    }

    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      width: 250px;
      background: white;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      z-index: 1001;
      display: flex;
      flex-direction: column;
      border-right: 1px solid #dee2e6;
    }

    .sidebar-header {
      padding: 15px;
      border-bottom: 1px solid #dee2e6;
    }

    .sidebar-title {
      margin: 0;
      font-size: 1.25rem;
      font-weight: 600;
    }

    .sidebar-content {
      flex: 1;
      padding: 20px;
    }

    .main-content {
      margin-left: 250px;
      padding: 20px;
      height: calc(100vh - 40px);
      overflow-y: auto;
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

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <h5 class="sidebar-title">Menu</h5>
    </div>
    <div class="sidebar-content">
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
  <div class="main-content" style="margin-left: 250px; padding: 20px;">
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
