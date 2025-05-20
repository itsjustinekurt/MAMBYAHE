<?php
session_start();
require_once '../db_connect.php';

// Fetch all complaints with complainant and complainee info
$sql = "SELECT c.*, 
               p1.fullname AS complainant_name, 
               p2.fullname AS complainee_name, 
               d1.fullname AS driver_complainant_name, 
               d2.fullname AS driver_complainee_name,
               a.name AS association_name
        FROM mtfrb_complaints c
        LEFT JOIN passenger p1 ON c.passenger_id = p1.passenger_id
        LEFT JOIN passenger p2 ON c.respondent_passenger_id = p2.passenger_id
        LEFT JOIN driver d1 ON c.complainant_driver_id = d1.driver_id
        LEFT JOIN driver d2 ON c.respondent_driver_id = d2.driver_id
        LEFT JOIN associations a ON c.association_id = a.id
        ORDER BY c.created_at DESC";
$complaints = [];
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}
// Fetch all associations for the dropdown
$associations = [];
$result = $conn->query("SELECT id, name FROM associations ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $associations[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; min-height: 100vh; margin: 0; }
        .header { height: 56px; border-bottom: 1px solid #dee2e6; background-color: #fff; opacity: 0.95; }
        .content-container { background-color: rgba(255 255 255 / 0.95); border-radius: 0.75rem; padding: 1rem 1.5rem; max-width: 100%; box-shadow: 0 0 10px rgb(0 0 0 / 0.1); margin: 1rem auto 2rem; }
        table { font-size: 0.875rem; width: 100%; border-collapse: collapse; }
        th { font-size: 0.75rem; font-weight: 600; color: #374151; background-color: #f8fafc; padding: 0.5rem; }
        td { font-size: 0.75rem; color: #4b5563; padding: 0.5rem; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .pagination { font-size: 0.75rem; margin-top: 1rem; }
        .pagination .page-link { color: #2563eb; }
        .pagination .page-item.active .page-link { background-color: #2563eb; color: white; }
    .btn-filter { font-size: 0.75rem; padding: 0.25rem 0.75rem; background-color: #2563eb; color: white; border: none; border-radius: 0.375rem; cursor: pointer; transition: background-color 0.2s; }
    .btn-filter:hover { background-color: #1d4ed8; }
    .fas { font-size: 0.75rem; margin-right: 0.25rem; }
    .search-label { font-size: 0.75rem; color: #374151; margin-right: 0.5rem; }
    .complainee-link { color: #2563eb; text-decoration: underline; cursor: pointer; }
    .complainee-link:hover { color: #1d4ed8; }
    .btn-filter:focus, .btn-filter:hover { background-color: #d1d5db; color: #111827; box-shadow: none; }
    .search-input { border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.25rem 0.5rem; font-size: 0.875rem; color: #111827; width: 16rem; }
    .search-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 0.2rem rgb(59 130 246 / 0.5); }
    .search-btn { background: none; border: none; color: #6b7280; font-size: 1rem; cursor: pointer; padding: 0 0.5rem; }
    .search-btn:hover { color: #111827; }
    /* Sidebar styles */
    .sidebar-overlay {
        position: fixed;
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

    .complainee-link { color: #2563eb; cursor: pointer; text-decoration: underline; }
    .complainee-link:hover { color: #1d4ed8; }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>
  <div class="container-fluid" style="margin-left:260px;">
    <main class="flex-grow-1">
      <div class="container-fluid bg-white-opacity rounded-3 shadow-sm mt-4">
        <div class="p-4">
          <h1 class="h5 fw-semibold mb-4">Reports</h1>
          <form class="d-flex flex-wrap align-items-center gap-3 mb-4" role="search" aria-label="Search and filter form">
        <label for="search-filter" class="fw-semibold mb-0" style="user-select:none;">Search</label>
        <select id="search-filter" class="btn-filter" aria-label="Search filter">
          <option>All</option>
        </select>
        <div class="input-group" style="width: 16rem;">
          <input type="search" id="search" class="form-control search-input" aria-label="Search input" placeholder="" />
          <button class="search-btn" type="submit" aria-label="Search">
            <i class="fas fa-search"></i>
          </button>
        </div>
        <select id="associations-filter" class="btn-filter" aria-label="Associations filter">
          <option value="">Associations</option>
          <?php foreach ($associations as $assoc): ?>
            <option value="<?= htmlspecialchars($assoc['id']) ?>"><?= htmlspecialchars($assoc['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <div class="table-responsive">
        <table class="table table-bordered align-middle text-secondary text-nowrap mb-0" style="min-width: 900px;">
          <thead>
            <tr>
              <th scope="col" class="text-start" style="width: 3.5rem;">No.</th>
              <th scope="col" class="text-start" style="width: 9rem;">Complainant <i class="fas fa-sort ms-1" style="font-size: 0.75rem;"></i></th>
              <th scope="col" class="text-start" style="width: 9rem;">Complainee <i class="fas fa-sort ms-1" style="font-size: 0.75rem;"></i></th>
              <th scope="col" class="text-start" style="width: 9rem;">Association Name <i class="fas fa-sort ms-1" style="font-size: 0.75rem;"></i></th>
              <th scope="col" class="text-start" style="width: 9rem;">Address <i class="fas fa-sort ms-1" style="font-size: 0.75rem;"></i></th>
              <th scope="col" class="text-start" style="width: 12rem;">Reason <i class="fas fa-sort ms-1" style="font-size: 0.75rem;"></i></th>
              <th scope="col" class="text-start" style="width: 10rem;">Date and Time <i class="fas fa-sort ms-1" style="font-size: 0.75rem;"></i></th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; foreach ($complaints as $c): ?>
            <tr>
              <td><?= $i++ ?>.</td>
              <td><?= htmlspecialchars($c['complainant_name'] ?? $c['driver_complainant_name'] ?? '-') ?></td>
              <td><span class="complainee-link" data-id="<?= $c['respondent_passenger_id'] ?? $c['respondent_driver_id'] ?>" data-type="<?= $c['respondent_passenger_id'] ? 'passenger' : 'driver' ?>" data-name="<?= htmlspecialchars($c['complainee_name'] ?? $c['driver_complainee_name'] ?? '-') ?>"><?= htmlspecialchars($c['complainee_name'] ?? $c['driver_complainee_name'] ?? '-') ?></span></td>
              <td><?= htmlspecialchars($c['association_name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($c['pickup_location'] ?? '-') ?></td>
              <td class="fw-semibold" style="font-size: 0.75rem; line-height: 1.1;"> <?= htmlspecialchars($c['complaint_details'] ?? '-') ?> </td>
              <td><?= htmlspecialchars(date('m-d-y g:i A', strtotime($c['created_at']))) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Complainee Modal -->
    <div class="modal fade" id="complaineeModal" tabindex="-1" aria-labelledby="complaineeModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="complaineeModalLabel">Complainee Record</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="complaineeInfo"></div>
            <div class="d-flex gap-2 mt-3">
              <button class="btn btn-warning" id="warnBtn">Warning</button>
              <button class="btn btn-secondary" id="suspendBtn">Suspend</button>
              <button class="btn btn-danger" id="blockBtn">Block</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Sidebar logic
  const sidebar = document.getElementById('sidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  document.getElementById('sidebarClose').onclick = function() {
      sidebar.classList.remove('open');
      sidebarOverlay.style.display = 'none';
  };
  sidebarOverlay.onclick = function() {
      sidebar.classList.remove('open');
      sidebarOverlay.style.display = 'none';
  };
  // Complainee modal logic
  let selectedComplainee = { id: null, type: null, name: '' };
  document.querySelectorAll('.complainee-link').forEach(link => {
    link.addEventListener('click', function() {
      selectedComplainee.id = this.getAttribute('data-id');
      selectedComplainee.type = this.getAttribute('data-type');
      selectedComplainee.name = this.getAttribute('data-name');
      fetch('get_complainee_stats.php?id=' + selectedComplainee.id + '&type=' + selectedComplainee.type)
        .then(res => res.json())
        .then(data => {
          document.getElementById('complaineeInfo').innerHTML =
            `<div><b>Name:</b> ${selectedComplainee.name}</div>` +
            `<div><b>Complaints Received:</b> ${data.count}</div>`;
          var modal = new bootstrap.Modal(document.getElementById('complaineeModal'));
          modal.show();
        });
    });
  });
  // Action buttons
  function sendAction(action) {
    fetch('user_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `user_id=${selectedComplainee.id}&action=${action}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      var modal = bootstrap.Modal.getInstance(document.getElementById('complaineeModal'));
      modal.hide();
    });
  }
  document.getElementById('warnBtn').onclick = () => sendAction('warning');
  document.getElementById('suspendBtn').onclick = () => sendAction('suspend');
  document.getElementById('blockBtn').onclick = () => sendAction('block');
  </script>
</body>
</html> 