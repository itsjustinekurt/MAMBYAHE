<?php
session_start();
require_once '../db_connect.php';

// Fetch associations for dropdown
$associations = [];
$result = $conn->query("SELECT name FROM associations ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $associations[] = $row['name'];
}
// Fetch addresses for dropdown (from passenger table)
$addresses = [];
$result = $conn->query("SELECT DISTINCT address FROM passenger WHERE address IS NOT NULL AND address != '' ORDER BY address ASC");
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row['address'];
}
// Fetch ready-made notifications
$ready_notifs = [
    'System maintenance scheduled for tonight.',
    'Please update your profile information.',
    'Reminder: Follow traffic rules and regulations.',
    'Fare rates have been updated.',
    'Emergency: Please check your messages immediately.'
];

// Fetch sent notifications (for table)
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$count = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE type = 'MTFRB'");
if ($row = $result->fetch_assoc()) {
    $count = $row['cnt'];
}
$totalPages = ceil($count / $perPage);
$notifications = [];
$sql = "SELECT n.*, 
           CASE 
             WHEN n.user_type = 'driver' THEN d.fullname 
             WHEN n.user_type = 'passenger' THEN p.fullname 
             ELSE NULL 
           END AS receiver_name
    FROM notifications n
    LEFT JOIN driver d ON n.driver_id = d.driver_id
    LEFT JOIN passenger p ON n.passenger_id = p.passenger_id
    WHERE n.type = 'MTFRB'
    ORDER BY n.created_at DESC
    LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Push-Notification</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: url('https://placehold.co/1920x1080?text=Background+Image+of+building+and+trees') center/cover no-repeat; min-height: 100vh; margin: 0; }
    .header { height: 56px; border-bottom: 1px solid #dee2e6; background-color: #fff; opacity: 0.95; }
    .content-container { background-color: rgba(255 255 255 / 0.95); border-radius: 0.75rem; padding: 1rem 1.5rem; max-width: 100%; box-shadow: 0 0 10px rgb(0 0 0 / 0.1); margin: 1rem auto 2rem; }
    table { font-size: 9px; width: 100%; border-collapse: collapse; }
    thead tr { background-color: #d1d5db; color: #374151; }
    thead th { padding: 0.25rem 0.5rem; text-align: left; vertical-align: middle; font-weight: 600; }
    tbody tr:nth-child(odd) { background-color: #f3f4f6; color: #1f2937; }
    tbody tr:nth-child(even) { background-color: #d1d5db; height: 24px; }
    tbody td { padding: 0.25rem 0.5rem; vertical-align: middle; }
    .fa-sort { font-size: 0.6rem; margin-left: 0.15rem; }
    .btn-add { color: #4b5563; font-size: 1.25rem; background: none; border: none; cursor: pointer; }
    .btn-add:hover { color: #111827; }
    .pagination { font-size: 9px; justify-content: center; margin-top: 1.5rem; }
    .page-link { color: #2563eb; cursor: pointer; padding: 0.15rem 0.5rem; }
    .page-link.disabled { color: #d1d5db; cursor: default; }
    .page-item.active .page-link { background-color: #2563eb; border-color: #2563eb; color: white; font-weight: 600; padding: 0.15rem 0.5rem; }
    .pagination .dots { padding: 0.15rem 0.5rem; user-select: none; color: #374151; }
    /* Sidebar styles from dashboard */
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
        <h4 class="mb-0">SEND NOTIFICATION</h4>
    </div>
    <div class="d-flex justify-content-end mb-2">
      <button class="btn-add" aria-label="Add new notification" data-bs-toggle="modal" data-bs-target="#notifModal">
        <i class="fas fa-plus-square"></i>
      </button>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width:15%">Receiver <i class="fas fa-sort"></i></th>
          <th style="width:55%">Message</th>
          <th style="width:15%">Date <i class="fas fa-sort"></i></th>
          <th style="width:15%">Time <i class="fas fa-sort"></i></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($notifications as $notif): ?>
        <tr>
          <td>
            <?php
              if ($notif['user_type'] === 'driver') {
                echo 'Drivers';
              } elseif ($notif['user_type'] === 'passenger') {
                echo 'Passengers';
              } else {
                echo '-';
              }
            ?>
          </td>
          <td><?= htmlspecialchars($notif['message']) ?></td>
          <td><?= date('m-d-y', strtotime($notif['created_at'])) ?></td>
          <td><?= date('g:i A', strtotime($notif['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <nav aria-label="Pagination">
      <ul class="pagination">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>" aria-disabled="true" aria-label="Previous">
          <a class="page-link" tabindex="-1" href="?page=<?= $page-1 ?>"> <i class="fas fa-arrow-left"></i> Previous </a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>" aria-current="page">
          <a class="page-link" href="?page=<?= $i ?>"> <?= $i ?> </a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>">Next <i class="fas fa-arrow-right"></i></a>
        </li>
      </ul>
    </nav>
</div>

  <!-- Modal for sending notification -->
  <div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="notifForm" method="post" action="send_notification_action.php">
          <div class="modal-header">
            <h5 class="modal-title" id="notifModalLabel">Send Notification</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="receiverType" class="form-label">Send To</label>
              <select class="form-select" id="receiverType" name="receiverType" required>
                <option value="">Select Receiver Type</option>
                <option value="passenger">Passengers</option>
                <option value="driver">Drivers</option>
                <option value="association">Associations</option>
              </select>
            </div>
            <div class="mb-3 d-none" id="todaDropdown">
              <label for="todaSelect" class="form-label">Select TODA</label>
              <select class="form-select" id="todaSelect" name="toda">
                <option value="">Select TODA</option>
                <?php foreach ($associations as $assoc): ?>
                  <option value="<?= htmlspecialchars($assoc) ?>"><?= htmlspecialchars($assoc) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3 d-none" id="addressDropdown">
              <label for="addressSelect" class="form-label">Select Address</label>
              <select class="form-select" id="addressSelect" name="address">
                <option value="">Select Address</option>
                <?php foreach ($addresses as $address): ?>
                  <option value="<?= htmlspecialchars($address) ?>"><?= htmlspecialchars($address) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="readyNotif" class="form-label">Ready-made Notifications</label>
              <select class="form-select" id="readyNotif">
                <option value="">Select a ready-made notification</option>
                <?php foreach ($ready_notifs as $msg): ?>
                  <option value="<?= htmlspecialchars($msg) ?>"><?= htmlspecialchars($msg) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="notifMessage" class="form-label">Message</label>
              <textarea class="form-control" id="notifMessage" name="message" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Send Notification</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Show/hide dropdowns based on receiver type
    document.getElementById('receiverType').addEventListener('change', function() {
      document.getElementById('todaDropdown').classList.add('d-none');
      document.getElementById('addressDropdown').classList.add('d-none');
      if (this.value === 'association') {
        document.getElementById('todaDropdown').classList.remove('d-none');
      } else if (this.value === 'passenger') {
        document.getElementById('addressDropdown').classList.remove('d-none');
      }
    });
    // Ready-made notification to textarea
    document.getElementById('readyNotif').addEventListener('change', function() {
      if (this.value) {
        document.getElementById('notifMessage').value = this.value;
      }
    });
  </script>
</body>
</html> 