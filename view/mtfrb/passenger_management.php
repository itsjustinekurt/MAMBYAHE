<?php
session_start();
require_once '../db_connect.php';

// Fetch passengers with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    // Count total passengers
    $result = $conn->query("SELECT COUNT(*) as total FROM passenger");
    if (!$result) throw new Exception($conn->error);
    
    $row = $result->fetch_assoc();
    $totalPassengers = $row['total'];
    $totalPages = ceil($totalPassengers / $perPage);

    // Get passenger table columns
    $columns = $conn->query("DESCRIBE passenger");
    $passengerColumns = [];
    while ($row = $columns->fetch_assoc()) {
        $passengerColumns[] = $row['Field'];
    }

    // Check if associations table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'associations'");
    $hasAssociations = $checkTable && $checkTable->num_rows > 0;

    // Fetch passengers with pagination
    if ($hasAssociations) {
        // Try different column names
        $associationColumns = ['association_id', 'association', 'assoc_id', 'assoc'];
        $foundColumn = false;
        
        foreach ($associationColumns as $col) {
            if (in_array($col, $passengerColumns)) {
                $sql = "SELECT p.*, a.name as association_name 
                        FROM passenger p 
                        LEFT JOIN associations a ON p.$col = a.id 
                        ORDER BY p.fullname ASC 
                        LIMIT $perPage OFFSET $offset";
                
                $result = $conn->query($sql);
                if ($result) {
                    $foundColumn = true;
                    break;
                }
            }
        }

        if (!$foundColumn) {
            // If no association column found, just get passengers
            $sql = "SELECT * FROM passenger 
                    ORDER BY fullname ASC 
                    LIMIT $perPage OFFSET $offset";
            $result = $conn->query($sql);
        }
    } else {
        // If no associations table, just get passengers
        $sql = "SELECT * FROM passenger 
                ORDER BY fullname ASC 
                LIMIT $perPage OFFSET $offset";
        $result = $conn->query($sql);
    }

    if (!$result) throw new Exception($conn->error);
    
    $passengers = [];
    while ($row = $result->fetch_assoc()) {
        $passengers[] = $row;
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Passengers - MTFRB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
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
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container-fluid" style="margin-left:260px;">
        <main class="flex-grow-1">
            <div class="container-fluid bg-white-opacity rounded-3 shadow-sm mt-4">
                <div class="p-4">
                    <h1 class="h5 fw-semibold mb-4">Passengers</h1>
                    
                    <!-- Search and Filter Form -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <label for="search" class="search-label mb-0">Search</label>
                        <div class="position-relative flex-grow-1 flex-shrink-1" style="max-width: 12rem;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by name or email...">
                        </div>
                    </div>

                    <!-- Total Passengers Count -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <i class='bx bx-user me-2'></i>
                            <span>Total Passengers: <?= $totalPassengers ?></span>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive rounded-3 border border-secondary-subtle shadow-sm">
                        <table class="table table-sm mb-0 text-nowrap align-middle">
                            <thead class="bg-light-custom">
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($passengers as $passenger): ?>
                                <tr>
                                    <td><?php echo $passengers ? array_search($passenger, $passengers) + 1 : 1; ?></td>
                                    <td><?= htmlspecialchars($passenger['fullname'] ?? $passenger['name']) ?></td>
                                    <td><?= htmlspecialchars($passenger['email'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($passenger['phone'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($passenger['address'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $passenger['status'] === 'active' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($passenger['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Pagination" class="mt-3">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page-1 ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>

    <!-- Add necessary scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add search functionality
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const search = document.getElementById('search').value;
            window.location.href = '?search=' + encodeURIComponent(search);
        });
    </script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.querySelector('table tbody');
    const rows = tableBody.getElementsByTagName('tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        Array.from(rows).forEach(row => {
            const fullName = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            
            if (fullName.includes(searchTerm) || email.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
</body>
</html>
