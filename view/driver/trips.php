<?php
include '../components/driver_sidebar.php';

// Database connection
$host = 'localhost';
$db = 'user_auth';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$driver_id = $_SESSION['driver_id'];
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';

// Handle complaint submission (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_trip_id'])) {
    $trip_id = $_POST['complaint_trip_id'];
    $reason = trim($_POST['complaint_reason']);
    $created_at = date('Y-m-d H:i:s');
    $response = ['success' => false, 'message' => 'Reason required.'];
    if ($reason !== '') {
        $stmt = $pdo->prepare("INSERT INTO complaints (trip_id, driver_id, reason, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$trip_id, $driver_id, $reason, $created_at]);
        // Email notification to admin (MTFRB)
        $admin_email = 'mtfrb.admin@example.com'; // Change to actual admin email
        $subject = 'New Complaint Submitted';
        $message = "A new complaint has been submitted by driver ID $driver_id for trip ID $trip_id.\n\nReason: $reason\n\nDate: $created_at";
        $headers = 'From: noreply@yourdomain.com' . "\r\n" .
            'Reply-To: noreply@yourdomain.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        @mail($admin_email, $subject, $message, $headers);
        $response = ['success' => true, 'message' => 'Complaint submitted successfully to MTFRB.'];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Build query
$query = "SELECT b.*, p.fullname, p.profile_pic, p.passenger_id FROM bookings b LEFT JOIN passenger p ON b.passenger_id = p.passenger_id WHERE b.driver_id = :driver_id";
$params = ['driver_id' => $driver_id];
if ($from) {
    $query .= " AND DATE(b.created_at) >= :from";
    $params['from'] = $from;
}
if ($to) {
    $query .= " AND DATE(b.created_at) <= :to";
    $params['to'] = $to;
}
$query .= " ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// After fetching $trips
$total_trips = count($trips);
$status_search = isset($_GET['status']) ? $_GET['status'] : '';

// If status filter is set, filter $trips array
if ($status_search !== '') {
    $trips = array_filter($trips, function($trip) use ($status_search) {
        return strtolower($trip['status']) === strtolower($status_search);
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trips History</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <style>
        body { background: #f8f9fa; }
        .trips-header { margin-top: 30px; }
        .table-modern { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden; }
        .table-modern th, .table-modern td { vertical-align: middle !important; }
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }
        .name-cell { font-weight: 600; }
        .sub-info { font-size: 0.85em; color: #888; }
        .amount-pos { color: #28a745; font-weight: 600; }
        .amount-neg { color: #dc3545; font-weight: 600; }
        .status-receive { color: #28a745; font-weight: 500; }
        .status-transfer { color: #fd7e14; font-weight: 500; }
        .status-payment { color: #dc3545; font-weight: 500; }
        .status-withdraw { color: #6f42c1; font-weight: 500; }
        .status-deposit { color: #17a2b8; font-weight: 500; }
        .details-btn { border-radius: 20px; font-size: 0.95em; padding: 4px 18px; }
        .table-modern tbody tr { transition: background 0.2s; }
        .table-modern tbody tr:hover { background: #f1f7ff; }
        .search-bar {
            background: #f4f6fa;
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            margin-bottom: 24px;
        }
        .search-bar input[type="date"] {
            border: none;
            background: transparent;
            outline: none;
            margin-right: 12px;
            font-size: 1em;
        }
        .search-bar input[type="date"]::-webkit-input-placeholder { color: #bbb; }
        .search-bar input[type="date"]::-moz-placeholder { color: #bbb; }
        .search-bar input[type="date"]:-ms-input-placeholder { color: #bbb; }
        .search-bar input[type="date"]::placeholder { color: #bbb; }
        .search-btn {
            background: #4f8cff;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }
        .search-btn i { margin-right: 6px; }
        .search-btn:hover { background: #2563eb; }
    </style>
</head>
<body>
<div class="container trips-header">
    <h2 class="mb-4">Trips History</h2>
    <div class="mb-3">
        <span class="fw-bold">Total Trips:</span> <?= $total_trips ?>
    </div>
    <form class="search-bar" method="get" id="dateFilterForm" style="gap: 10px;">
        <input type="date" id="fromDate" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
        <input type="date" id="toDate" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
        <select name="status" class="form-control" id="statusSelect">
            <option value="">All Status</option>
            <option value="accepted" <?= $status_search === 'accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="pending" <?= $status_search === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="cancelled" <?= $status_search === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <button class="search-btn" id="searchBtn" type="submit"><i class="fas fa-search"></i>Search</button>
    </form>
    <div id="dateError" class="text-danger mb-2" style="display:none;"></div>
    <div class="table-responsive table-modern">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Pickup</th>
                    <th>Drop-off</th>
                    <th>Date and Time</th>
                    <th>Seats</th>
                    <th>Total Fare</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trips) > 0): ?>
                    <?php foreach ($trips as $trip): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php
                                    $avatar = !empty($trip['profile_pic']) ? '../uploads/' . htmlspecialchars($trip['profile_pic']) : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
                                    ?>
                                    <img src="<?= $avatar ?>" class="avatar me-2" alt="Avatar">
                                    <div>
                                        <div class="name-cell"><?= htmlspecialchars($trip['fullname'] ?? 'N/A') ?></div>
                                        <div class="sub-info">ID: <?= htmlspecialchars($trip['passenger_id'] ?? '-') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($trip['pickup'] ?? '') ?></td>
                            <td><?= htmlspecialchars($trip['destination'] ?? '') ?></td>
                            <td>
                                <?= htmlspecialchars(date('d M Y', strtotime($trip['created_at']))) ?><br>
                                <span class="sub-info">@ <?= htmlspecialchars(date('h:i A', strtotime($trip['created_at']))) ?></span>
                            </td>
                            <td><?= htmlspecialchars($trip['seats'] ?? '-') ?></td>
                            <td>
                                <?php
                                $fare = floatval($trip['fare']);
                                if ($fare > 0) {
                                    echo '<span class="amount-pos">₱' . number_format($fare, 2) . '</span>';
                                } else {
                                    echo '<span class="amount-neg">₱' . number_format($fare, 2) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $status = strtolower($trip['status']);
                                if ($status === 'completed' || $status === 'success' || $status === 'accepted') {
                                    echo '<span class="status-receive">Accepted</span>';
                                } elseif ($status === 'cancelled') {
                                    echo '<span class="status-withdraw">Cancelled</span>';
                                } elseif ($status === 'pending') {
                                    echo '<span class="status-payment">Pending</span>';
                                } else {
                                    echo '<span class="text-secondary">' . htmlspecialchars(ucfirst($status)) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-danger details-btn complaint-btn" data-trip-id="<?= $trip['id'] ?>" data-bs-toggle="modal" data-bs-target="#complaintModal">Complaint</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">No trips found. Please select a date range and search.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Complaint Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="complaintForm">
        <div class="modal-header">
          <h5 class="modal-title" id="complaintModalLabel">Submit Complaint to MTFRB</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="complaint_trip_id" id="complaint_trip_id">
          <div class="mb-3">
            <label for="complaint_reason" class="form-label">Reason/Details</label>
            <textarea class="form-control" name="complaint_reason" id="complaint_reason" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Submit Complaint</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Set trip id in modal when complaint button is clicked
const complaintBtns = document.querySelectorAll('.complaint-btn');
complaintBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('complaint_trip_id').value = this.getAttribute('data-trip-id');
        document.getElementById('complaint_reason').value = '';
    });
});

// AJAX submit for complaint form
const complaintForm = document.getElementById('complaintForm');
complaintForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(complaintForm);
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('complaintModal'));
        if (modal) modal.hide();
        // Show SweetAlert
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Submitted!' : 'Error',
            text: data.message
        });
        if (data.success) complaintForm.reset();
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'There was an error submitting your complaint.'
        });
    });
});

document.getElementById('statusSelect').addEventListener('change', function() {
    document.getElementById('dateFilterForm').submit();
});
</script>
<?php include __DIR__.'/../tab_session_protect.php'; ?>
</body>
</html> 