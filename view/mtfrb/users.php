<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mtfrb') {
    header('Location: login.php');
    exit;
}
require_once '../db_connect.php';
// Fetch passengers
$passengers = [];
$result = $conn->query('SELECT fullname, username, dob, address, phone, nationality, email, "Passenger" AS role FROM passenger');
while ($row = $result->fetch_assoc()) {
    $passengers[] = $row;
}
// Fetch approved drivers
$drivers = [];
$result = $conn->query("SELECT fullname, username, dob, address, phone, nationality, '' AS email, 'Driver' AS role FROM driver WHERE status = 'approved'");
while ($row = $result->fetch_assoc()) {
    $drivers[] = $row;
}
// Merge users
$users = array_merge($passengers, $drivers);
// Fetch only pending drivers
$pending_drivers = [];
$result = $conn->query("SELECT * FROM driver WHERE status = 'pending'");
while ($row = $result->fetch_assoc()) {
    $pending_drivers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>All Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
body { background: #f5f7fa; }
.main-container, .container.py-4, .content-container { margin-left: 260px !important; }
.table thead th { font-weight: 600; font-size: 0.95rem; }
.table tbody td { font-size: 0.95rem; }
.add-btn { background: #226d2c; color: #fff; font-weight: 600; border-radius: 0.5rem; }
.add-btn:hover { background: #1a4d1f; }
/* Sidebar styles */
.sidebar-overlay { display: none !important; }
.sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background: #8fa195; z-index: 1050; transform: none !important; transition: none; border-top-right-radius: 2rem; }
.sidebar.open { transform: none; }
.sidebar-header { display: flex; align-items: center; gap: 0.75rem; padding: 1.5rem 1rem 1rem 1.5rem; }
.sidebar-logo { width: 40px; height: 40px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; }
.sidebar-title { font-weight: 800; font-size: 1.2rem; color: #fff; }
.sidebar-subtitle { font-size: 0.85rem; color: #e0e7ef; font-weight: 600; }
.sidebar-nav { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
.sidebar-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.7rem 1.5rem; color: #222; font-weight: 500; font-size: 1.05rem; border-radius: 0.5rem; text-decoration: none; transition: background 0.2s; }
.sidebar-link:hover { background: #e5e7eb; color: #111; }
.sidebar-link i { font-size: 1.3rem; }
.sidebar-close { display: none !important; }
@media (max-width: 600px) { .sidebar { width: 90vw; } .main-container, .container.py-4, .content-container { margin-left: 0 !important; } }
</style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex align-items-center mb-4">
      <h4 class="mb-0">USERS</h4>
  </div>
</div>
<div class="main-container">
  <div class="row mb-3">
    <div class="col-md-6 mb-2 mb-md-0">
      <input type="text" id="userSearch" class="form-control" placeholder="Search by name...">
    </div>
    <div class="col-md-4">
      <select id="roleFilter" class="form-select">
        <option value="">All Roles</option>
        <option value="Passenger">Passenger</option>
        <option value="Chairman">Chairman</option>
        <option value="Driver">Driver</option>
      </select>
    </div>
  </div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h4>
    <button class="btn add-btn" data-bs-toggle="modal" data-bs-target="#pendingDriversModal"><i class="fas fa-plus me-1"></i>Add Driver</button>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Full Name</th>
          <th>Username</th>
          <th>Date of Birth</th>
          <th>Address</th>
          <th>Phone</th>
          <th>Nationality</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody id="usersTableBody">
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <a href="#" class="open-modal-btn"
               data-username="<?= htmlspecialchars($u['username']) ?>"
               data-fullname="<?= htmlspecialchars($u['fullname']) ?>"
               data-address="<?= htmlspecialchars($u['address']) ?>"
               data-dob="<?= htmlspecialchars($u['dob']) ?>"
               data-role="<?= htmlspecialchars($u['role']) ?>"
               data-user-id="<?= htmlspecialchars($u['username']) ?>">
              <?= htmlspecialchars($u['fullname']) ?>
            </a>
          </td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['dob']) ?></td>
          <td><?= htmlspecialchars($u['address']) ?></td>
          <td><?= htmlspecialchars($u['phone']) ?></td>
          <td><?= htmlspecialchars($u['nationality']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- Modal for pending drivers -->
<div class="modal fade" id="pendingDriversModal" tabindex="-1" aria-labelledby="pendingDriversModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendingDriversModalLabel">Pending Driver Accounts</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>Full Name</th>
                <th>Username</th>
                <th>Date of Birth</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Nationality</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_drivers as $d): ?>
              <tr>
                <td><?= htmlspecialchars($d['fullname']) ?></td>
                <td><?= htmlspecialchars($d['username']) ?></td>
                <td><?= htmlspecialchars($d['dob']) ?></td>
                <td><?= htmlspecialchars($d['address']) ?></td>
                <td><?= htmlspecialchars($d['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['nationality']) ?></td>
                <td><?= htmlspecialchars($d['status']) ?></td>
                <td>
                  <button class="btn btn-sm btn-primary confirm-btn" data-driver-id="<?= $d['driver_id'] ?>">Confirm</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal for account confirmation -->
<div class="modal fade" id="accountConfirmModal" tabindex="-1" aria-labelledby="accountConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="accountConfirmModalLabel">Driver Account Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="accountConfirmBody">
        <!-- account_confirmation.php will be loaded here via AJAX -->
      </div>
    </div>
  </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Open modal with user details when full name is clicked
  document.querySelectorAll('.open-modal-btn').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();

      document.getElementById('userFullName').textContent = 'Full Name: ' + this.dataset.fullname;
      document.getElementById('userAddress').textContent = 'Address: ' + this.dataset.address;
      document.getElementById('userBirthday').textContent = 'Birthday: ' + this.dataset.dob;
      document.getElementById('userRole').textContent = 'Role: ' + this.dataset.role;

      // Store the user ID in a hidden field for submitting the form
      const actionForm = document.getElementById('actionForm');
      let hiddenUserIdInput = actionForm.querySelector('input[name="user_id"]');
      if (!hiddenUserIdInput) {
        hiddenUserIdInput = document.createElement('input');
        hiddenUserIdInput.type = 'hidden';
        hiddenUserIdInput.name = 'user_id';
        actionForm.appendChild(hiddenUserIdInput);
      }
      hiddenUserIdInput.value = this.dataset.userId;

      new bootstrap.Modal(document.getElementById('actionModal')).show();
    });
  });

  // Show/hide suspend duration field
  document.getElementById('action').addEventListener('change', function () {
    const suspendDiv = document.getElementById('suspendDurationDiv');
    if (this.value === 'suspend') {
      suspendDiv.style.display = 'block';
    } else {
      suspendDiv.style.display = 'none';
    }
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

    fetch('../user_action.php', {
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

  // Reset modal on close
  const actionModal = document.getElementById('actionModal');
  actionModal.addEventListener('hidden.bs.modal', function () {
    document.getElementById('actionForm').reset();
    document.getElementById('suspendDurationDiv').style.display = 'none';
    // Remove hidden user_id input
    const hiddenInput = document.getElementById('actionForm').querySelector('input[name="user_id"]');
    if (hiddenInput) hiddenInput.remove();
  });
});

// Search and filter logic
const searchInput = document.getElementById('userSearch');
const roleFilter = document.getElementById('roleFilter');
const tableBody = document.getElementById('usersTableBody');

function filterTable() {
  const search = searchInput.value.toLowerCase();
  const role = roleFilter.value;
  Array.from(tableBody.rows).forEach(row => {
    const name = row.cells[0].innerText.toLowerCase();
    const userRole = row.cells[6].innerText;
    const matchesName = name.includes(search);
    const matchesRole = !role || userRole === role;
    row.style.display = (matchesName && matchesRole) ? '' : 'none';
  });
}

searchInput.addEventListener('input', filterTable);
roleFilter.addEventListener('change', filterTable);
</script>
</body>
</html> 