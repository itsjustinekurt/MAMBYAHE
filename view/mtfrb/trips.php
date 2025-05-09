<?php
session_start();
require_once '../db_connect.php'; // adjust path if needed

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Search
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);
    $where = "WHERE 
        b.pickup LIKE '%$searchEsc%' OR 
        b.destination LIKE '%$searchEsc%' OR 
        d.fullname LIKE '%$searchEsc%' OR 
        p.fullname LIKE '%$searchEsc%'";
}

// Fetch all association names for dropdown (use associations table, not driver)
$assocList = [];
$result = $conn->query("SELECT name FROM associations ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $assocList[] = $row['name'];
}

// Handle TODA filter
$todaFilter = trim($_GET['toda'] ?? '');
if ($todaFilter !== '') {
    $todaEsc = $conn->real_escape_string($todaFilter);
    $where .= ($where ? ' AND ' : 'WHERE ') . "d.toda = '$todaEsc'";
}

// Sorting
$sortable = ['created_at', 'driver_name', 'toda', 'passenger_name', 'pickup', 'destination', 'status', 'fare'];
$sort = in_array($_GET['sort'] ?? '', $sortable) ? $_GET['sort'] : 'created_at';
$dir = ($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

// Count total
$countSql = "SELECT COUNT(*) as cnt FROM bookings b 
    LEFT JOIN driver d ON b.driver_id = d.driver_id 
    LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
    $where";
$result = $conn->query($countSql);
$row = $result->fetch_assoc();
$total = $row['cnt'];
$totalPages = ceil($total / $perPage);

// Fetch trips
$sql = "SELECT b.*, d.fullname AS driver_name, d.toda AS toda, p.fullname AS passenger_name
    FROM bookings b
    LEFT JOIN driver d ON b.driver_id = d.driver_id
    LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
    $where
    ORDER BY $sort $dir
    LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);
$trips = [];
while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Trips</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    body { background: url('https://placehold.co/1920x1080?text=Background+Image+Placeholder') center/cover no-repeat; font-family: 'Segoe UI', sans-serif; min-height: 100vh; margin: 0; padding: 0; }
    .container.py-4, .main-container, main.container, .content-container { margin-left: 260px !important; }
    .bg-white-opacity { background-color: rgba(255, 255, 255, 0.9); }
    .table thead th { background-color: #d1d5db; cursor: pointer; user-select: none; font-size: 0.75rem; color: #374151; }
    .table thead th i { margin-left: 0.25rem; font-size: 0.6rem; color: #374151; }
    .pagination .page-link { font-size: 0.75rem; color: #2563eb; border: none; background: none; }
    .pagination .page-item.active .page-link { background-color: #2563eb; color: white; font-weight: 600; border-radius: 0.25rem; }
    .pagination .page-link:hover { background-color: #e5e7eb; color: #2563eb; }
    .form-select { font-size: 0.75rem; color: #374151; max-width: 8rem; }
    .form-control { font-size: 0.75rem; max-width: 12rem; }
    .search-label { font-weight: 600; font-size: 0.875rem; color: #111827; user-select: none; margin-right: 0.5rem; }
    .header-title { font-weight: 800; font-size: 1.125rem; user-select: none; }
    .header-icons i { font-size: 1.25rem; color: #374151; cursor: pointer; }
    .header-icons i:focus { outline: none; box-shadow: 0 0 0 2px #2563eb; border-radius: 0.25rem; }
    .btn-link { font-size: 0.75rem; color: #2563eb; text-decoration: none; }
    .btn-link:hover { text-decoration: underline; color: #1d4ed8; }
    .pagination .disabled .page-link { color: #9ca3af; cursor: not-allowed; pointer-events: none; }
    /* Sidebar styles (copied from dashboard) */
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
    @media (max-width: 600px) { .sidebar { width: 90vw; } .container.py-4, .main-container, main.container, .content-container { margin-left: 0 !important; } }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="container py-4" style="margin-left:260px;">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0">TRIPS</h4>
    </div>
    <main class="flex-grow-1 p-4">
      <div class="container-fluid bg-white-opacity rounded-3 p-4 shadow-sm">
        <form class="d-flex flex-wrap align-items-center gap-3 mb-4" method="get" id="searchForm">
          <label for="search" class="search-label mb-0">Search</label>
          <div class="position-relative flex-grow-1 flex-shrink-1" style="max-width: 12rem;">
            <input id="search" name="search" type="search" class="form-control ps-3 pe-5" aria-label="Search" value="<?= htmlspecialchars($search) ?>" autocomplete="off"/>
            <i class="fas fa-search position-absolute top-50 end-2 translate-middle-y text-secondary" style="font-size: 0.8rem; pointer-events:none;"></i>
          </div>
          <label for="toda" class="search-label mb-0">TODA</label>
          <select id="toda" name="toda" class="form-select">
            <option value="">All TODA</option>
            <?php foreach ($assocList as $assoc): ?>
              <option value="<?= htmlspecialchars($assoc) ?>" <?= $assoc === $todaFilter ? 'selected' : '' ?>><?= htmlspecialchars($assoc) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <div class="table-responsive rounded border border-secondary">
          <table class="table table-bordered mb-0 text-secondary align-middle" style="font-size: 0.75rem;">
            <thead>
              <tr>
                <th scope="col"><a href="?" class="sort-link" data-sort="created_at">Date <i class="fas fa-sort"></i></a></th>
                <th scope="col"><a href="?" class="sort-link" data-sort="driver_name">Driver <i class="fas fa-sort"></i></a></th>
                <th scope="col"><a href="?" class="sort-link" data-sort="toda">TODA <i class="fas fa-sort"></i></a></th>
                <th scope="col"><a href="?" class="sort-link" data-sort="passenger_name">Passenger <i class="fas fa-sort"></i></a></th>
                <th scope="col"><a href="?" class="sort-link" data-sort="pickup">Origin <i class="fas fa-sort"></i></a></th>
                <th scope="col"><a href="?" class="sort-link" data-sort="destination">Drop Off <i class="fas fa-sort"></i></a></th>
                <th scope="col"><a href="?" class="sort-link" data-sort="status">Status <i class="fas fa-sort"></i></a></th>
                <th scope="col"><a href="?" class="sort-link" data-sort="fare">Fare <i class="fas fa-sort"></i></a></th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($trips) === 0): ?>
                <tr><td colspan="8" class="text-center text-muted">No trips found.</td></tr>
              <?php else: foreach ($trips as $trip): ?>
                <tr>
                  <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($trip['created_at']))) ?></td>
                  <td><?= htmlspecialchars($trip['driver_name'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($trip['toda'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($trip['passenger_name'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($trip['pickup']) ?></td>
                  <td><?= htmlspecialchars($trip['destination']) ?></td>
                  <td><?= htmlspecialchars(ucfirst($trip['status'])) ?></td>
                  <td>₱<?= number_format($trip['fare'], 2) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <nav aria-label="Page navigation" class="d-flex justify-content-center mt-4">
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>" aria-label="Previous">
                <i class="fas fa-arrow-left"></i> Previous
              </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>" aria-label="Next">
                Next <i class="fas fa-arrow-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </main>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-submit search and toda filter
const searchInput = document.getElementById('search');
const todaSelect = document.getElementById('toda');
searchInput.addEventListener('input', function() {
  document.getElementById('searchForm').submit();
});
todaSelect.addEventListener('change', function() {
  document.getElementById('searchForm').submit();
});
// Sorting logic
function getQueryParams() {
  const params = new URLSearchParams(window.location.search);
  return params;
}
document.querySelectorAll('.sort-link').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    const sort = this.getAttribute('data-sort');
    const params = getQueryParams();
    const currentSort = params.get('sort');
    const currentDir = params.get('dir') || 'desc';
    let newDir = 'asc';
    if (currentSort === sort && currentDir === 'asc') newDir = 'desc';
    params.set('sort', sort);
    params.set('dir', newDir);
    window.location.search = params.toString();
  });
});
</script>
</body>
</html> 