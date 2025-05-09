<?php
session_start();

$host = 'localhost';
$db = 'user_auth';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all passengers (with full details)
    $stmt_passenger = $pdo->query("SELECT passenger_id AS user_id, username, fullname, address, dob, 'passenger' AS role FROM passenger");
    $passengers = $stmt_passenger->fetchAll(PDO::FETCH_ASSOC);

    // Fetch approved drivers (with full details)
    $stmt_driver = $pdo->query("SELECT driver_id AS user_id, username, fullname, address, dob, 'driver' AS role FROM driver WHERE status = 'approved'");
    $drivers = $stmt_driver->fetchAll(PDO::FETCH_ASSOC);

    // Combine both into one array
    $all_users = array_merge($passengers, $drivers);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $user_id = $_POST['user_id'];
  $action = $_POST['action'];
  $suspend_duration = $_POST['suspend_duration'] ?? null;
  $message = '';

  if ($action == 'warning') {
      $message = 'You have received a warning from MTFRB.';
  } elseif ($action == 'suspend') {
      $message = "You are suspended for $suspend_duration days.";
      // Logic for suspending user (e.g., update suspension duration in DB)
  } elseif ($action == 'block') {
      $message = 'Your account has been blocked by MTFRB.';
      // Logic for blocking user (e.g., set status as 'blocked' in DB)
  }

  try {
      // Insert notification for user
      $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, status, created_at) 
                             VALUES (:user_id, :message, 'unread', NOW())");
      $stmt->execute(['user_id' => $user_id, 'message' => $message]);

      // If needed, handle any further actions like suspending or blocking users in the database

  } catch (PDOException $e) {
      die("Error: " . $e->getMessage());
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Users - MTFRB Dashboard</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap.min.css" rel="stylesheet">
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/boxicons.js"></script>

</head>
<body class="p-4 bg-light">
  <div class="container">
    <h2 class="mb-4">All Users</h2>
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Address</th>
            <th>Birthday</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($all_users)): ?>
            <?php foreach ($all_users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                        <a href="#" class="open-modal-btn" 
                           data-user-id="<?= $user['user_id'] ?>" 
                           data-username="<?= $user['username'] ?>" 
                           data-role="<?= $user['role'] ?>" 
                           data-fullname="<?= $user['fullname'] ?>" 
                           data-address="<?= $user['address'] ?>" 
                           data-dob="<?= $user['dob'] ?>">
                           <?= htmlspecialchars($user['fullname']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($user['address']) ?></td>
                    <td><?= htmlspecialchars($user['dob']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">No users found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

  </div>

  <!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">User Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="userFullName">Full Name: </p>
                <p id="userAddress">Address: </p>
                <p id="userBirthday">Birthday: </p>
                <p id="userRole">Role: </p>
                <form id="actionForm">
                    <div class="form-group mb-3">
                        <label for="action">Select Action</label>
                        <select id="action" class="form-control">
                            <option value="warning">Warning</option>
                            <option value="suspend">Suspend</option>
                            <option value="block">Block</option>
                        </select>
                    </div>
                    <div id="suspendDurationDiv" class="form-group mb-3" style="display: none;">
                        <label for="suspendDuration">Suspend Duration (days)</label>
                        <input type="number" id="suspendDuration" class="form-control" min="1">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Open modal with user details when full name is clicked
  document.querySelectorAll('.open-modal-btn').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();

      // Populate modal with the selected user's data
      document.getElementById('userFullName').textContent = 'Full Name: ' + this.dataset.fullname;
      document.getElementById('userAddress').textContent = 'Address: ' + this.dataset.address;
      document.getElementById('userBirthday').textContent = 'Birthday: ' + this.dataset.dob;
      document.getElementById('userRole').textContent = 'Role: ' + this.dataset.role;

      // Store the user ID in a hidden field for submitting the form
      const actionForm = document.getElementById('actionForm');
      const hiddenUserIdInput = document.createElement('input');
      hiddenUserIdInput.type = 'hidden';
      hiddenUserIdInput.name = 'user_id';
      hiddenUserIdInput.value = this.dataset.userId;
      actionForm.appendChild(hiddenUserIdInput);

      // Show modal
      new bootstrap.Modal(document.getElementById('actionModal')).show();
    });
  });

  // Handle actions (Warning, Suspend, Block)
  document.getElementById('actionForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const action = document.getElementById('action').value;
    const suspendDuration = document.getElementById('suspendDuration').value || null;
    const userId = e.target.querySelector('input[name="user_id"]').value;

    const formData = new FormData();
    formData.append('action', action);
    formData.append('user_id', userId);
    if (action === 'suspend') formData.append('suspend_duration', suspendDuration);

    fetch('user_action.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        Swal.fire({
          title: 'Action Completed!',
          text: data.message,
          icon: 'success',
          confirmButtonText: 'Okay'
        });
      } else {
        Swal.fire({
          title: 'Error!',
          text: data.message,
          icon: 'error',
          confirmButtonText: 'Okay'
        });
      }

      bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
    })
    .catch(error => {
      Swal.fire({
        title: 'Error!',
        text: 'There was an issue processing the request.',
        icon: 'error',
        confirmButtonText: 'Okay'
      });
    });
  });
});

</script>

<?php include __DIR__.'/tab_session_protect.php'; ?>
</body>
</html>
