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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Send Notification - MTFRB</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    body { font-family: 'Inter', sans-serif; min-height: 100vh; margin: 0; }
    .header { height: 56px; border-bottom: 1px solid #dee2e6; background-color: #fff; opacity: 0.95; }
    .bg-light-custom { background-color: #f8fafc !important; }
    .table { width: 100%; }
    .table th { font-weight: 600; color: #374151; }
    .table td { color: #4b5563; }
    .table-responsive { overflow-x: auto; }
    .content-container { background-color: rgba(255 255 255 / 0.95); border-radius: 0.75rem; padding: 1rem 1.5rem; max-width: 100%; box-shadow: 0 0 10px rgb(0 0 0 / 0.1); margin: 1rem auto 2rem; }
    table { font-size: 0.875rem; width: 100%; border-collapse: collapse; }
    th { font-size: 0.75rem; font-weight: 600; color: #374151; background-color: #f8fafc; padding: 0.5rem; }
    td { font-size: 0.75rem; color: #4b5563; padding: 0.5rem; }
    tr:nth-child(even) { background-color: #f8fafc; }
    .btn-add { font-size: 0.75rem; padding: 0.25rem 0.75rem; background-color: #2563eb; color: white; border: none; border-radius: 0.375rem; cursor: pointer; transition: background-color 0.2s; }
    .btn-add:hover { background-color: #1d4ed8; }
    .fas { font-size: 0.75rem; margin-right: 0.25rem; }
    .pagination { font-size: 0.75rem; margin-top: 1rem; }
    .pagination .page-link { color: #2563eb; }
    .pagination .page-item.active .page-link { background-color: #2563eb; color: white; }
    .search-label { font-size: 0.75rem; color: #374151; margin-right: 0.5rem; }
    .complainee-link { color: #2563eb; text-decoration: underline; cursor: pointer; }
    .complainee-link:hover { color: #1d4ed8; }
    /* Make table more compact */
    .table-responsive {
        overflow-x: auto;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
        display: none;
        transition: opacity 0.3s;
    }

    .sidebar {
        position: fixed;
        top: 60px;
        left: 0;
        height: calc(100vh - 60px);
        width: 260px;
        background: linear-gradient(135deg, #1e293b, #334155);
        z-index: 99;
        border-top-right-radius: 2rem;
        box-shadow: 2px 0 12px rgba(0,0,0,0.1);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        direction: rtl;
    }

    .sidebar-section {
        color: rgba(255,255,255,0.7);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 1.5rem 1.5rem 0.5rem 1.5rem;
        padding-left: 0.5rem;
        border-left: 3px solid var(--primary-color);
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.8rem 1.5rem;
        color: rgba(255,255,255,0.8);
        font-weight: 500;
        font-size: 1rem;
        border-radius: 0.75rem;
        text-decoration: none;
        transition: all 0.2s ease;
        position: relative;
        direction: ltr;
    }

    .sidebar-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 3px;
        height: 0;
        background: var(--primary-color);
        border-radius: 1.5px;
        transition: height 0.2s ease;
    }

    .sidebar-link.active,
    .sidebar-link:hover {
        color: white;
        background: rgba(255,255,255,0.1);
    }

    .sidebar-link.active::before,
    .sidebar-link:hover::before {
        height: 100%;
        top: 0;
    }
    .content-container { margin-left: 0 !important; }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>
  <div class="container-fluid" style="margin-left:260px;">
    <main class="flex-grow-1">
      <div class="container-fluid bg-white-opacity rounded-3 shadow-sm mt-4">
        <div class="p-4">
          <h1 class="h5 fw-semibold mb-4">Send Notification</h1>
          <div class="d-flex justify-content-end mb-4">
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#notifModal">
                  <i class="fas fa-plus"></i> Send Notification
              </button>
          </div>
          <div class="table-responsive">
              <table class="table table-sm mb-0 text-nowrap align-middle">
                  <thead class="bg-light-custom">
                      <tr>
                          <th>ID</th>
                          <th>Message</th>
                          <th>Receiver</th>
                          <th>Date</th>
                          <th>Status</th>
                      </tr>
                  </thead>
                  <tbody>
        <?php foreach ($notifications as $notif): ?>
        <tr>
          <td><?= htmlspecialchars($notif['id']) ?></td>
          <td><?= htmlspecialchars($notif['message']) ?></td>
          <td>
            <?php if ($notif['receiver_name']): ?>
              <?= htmlspecialchars($notif['receiver_name']) ?>
            <?php else: ?>
              <?php if ($notif['user_type'] === 'driver'): ?>
                All Drivers
              <?php elseif ($notif['user_type'] === 'passenger'): ?>
                All Passengers
              <?php else: ?>
                -
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td><?= date('m-d-y', strtotime($notif['created_at'])) ?></td>
          <td><?= htmlspecialchars($notif['status']) ?></td>
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