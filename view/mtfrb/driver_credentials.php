<?php
session_start();
require_once '../db_connect.php';

// Fetch all driver credentials
try {
    $sql = "SELECT * FROM driver_credentials_check";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Driver Credentials Check - MTFRB</title>
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
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
  <div class="container mt-5" style="margin-left:250px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <i class='bx bx-user me-2'></i>
            <span>Total Drivers: <?php
                $totalDrivers = $conn->query("SELECT COUNT(*) as total FROM driver_credentials_check");
                echo $totalDrivers->fetch_assoc()['total'];
            ?></span>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDriverModal">
                <i class='bx bx-plus me-1'></i> Add Driver
            </button>
            <div class="position-relative flex-grow-1 flex-shrink-1" style="max-width: 12rem;">
                <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search drivers...">
            </div>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDriverModalLabel">Add New Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDriverForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="fullname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nationality</label>
                                <input type="text" class="form-control" name="nationality" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="dob" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Franchise No</label>
                                <input type="text" class="form-control" name="franchise_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">OR No</label>
                                <input type="text" class="form-control" name="or_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Make</label>
                                <input type="text" class="form-control" name="make" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Motor No</label>
                                <input type="text" class="form-control" name="motor_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chassis No</label>
                                <input type="text" class="form-control" name="chassis_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plate No</label>
                                <input type="text" class="form-control" name="plate_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">TODA Name</label>
                                <input type="text" class="form-control" name="toda_name" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveDriverBtn">Save Driver</button>
                </div>
            </div>
        </div>
    </div>
    <div class="content-container">
        <table class="table table-sm mb-0 text-nowrap align-middle">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Phone</th>
              <th>Username</th>
              <th>Nationality</th>
              <th>DOB</th>
              <th>Address</th>
              <th>Franchise No</th>
              <th>OR No</th>
              <th>Make</th>
              <th>Motor No</th>
              <th>Chassis No</th>
              <th>Plate No</th>
              <th>TODA Name</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <?php
              // Check if driver exists in the driver table
              $username = $conn->real_escape_string($row['username']);
              $driverCheck = $conn->query("SELECT 1 FROM driver WHERE username='$username' LIMIT 1");
              $hasAccount = $driverCheck && $driverCheck->num_rows > 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td class="<?= $hasAccount ? 'text-success' : 'text-danger' ?>">
                <?= htmlspecialchars($row['fullname']) ?>
              </td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['nationality']) ?></td>
              <td><?= htmlspecialchars($row['dob']) ?></td>
              <td><?= htmlspecialchars($row['address']) ?></td>
              <td><?= htmlspecialchars($row['franchise_no']) ?></td>
              <td><?= htmlspecialchars($row['or_no']) ?></td>
              <td><?= htmlspecialchars($row['make']) ?></td>
              <td><?= htmlspecialchars($row['motor_no']) ?></td>
              <td><?= htmlspecialchars($row['chassis_no']) ?></td>
              <td><?= htmlspecialchars($row['plate_no']) ?></td>
              <td>
                <a href="#" 
                   class="text-decoration-none text-dark toda-link"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="top" 
                   title="<?= htmlspecialchars($row['toda_id']) ?>">
                  <?= htmlspecialchars($row['toda_id']) ?>
                </a>
              </td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        </div>
    </div>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.querySelector('table tbody');
    const rows = tableBody.getElementsByTagName('tr');
    const saveBtn = document.getElementById('saveDriverBtn');
    const addDriverForm = document.getElementById('addDriverForm');
    const modal = new bootstrap.Modal(document.getElementById('addDriverModal'));

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle TODA link clicks
    document.querySelectorAll('.toda-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const todaId = this.textContent;
            alert(`TODA ID: ${todaId}`);
            // You can add more functionality here, like showing a modal or redirecting
        });
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const searchTerms = searchTerm.split(' ').filter(term => term.trim());
        
        Array.from(rows).forEach(row => {
            const text = [
                row.cells[1].textContent.toLowerCase(), // Full Name
                row.cells[2].textContent.toLowerCase(), // Phone
                row.cells[3].textContent.toLowerCase(), // Username
                row.cells[13].textContent.toLowerCase() // TODA ID
            ].join(' ');
            
            const matches = searchTerms.every(term => text.includes(term));
            row.style.display = matches ? '' : 'none';
        });
    });

    // Save driver functionality
    saveBtn.addEventListener('click', async function() {
        const formData = new FormData(addDriverForm);
        
        try {
            const response = await fetch('save_driver.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                // Close modal
                modal.hide();
                
                // Refresh table
                window.location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving the driver');
        }
    });
});
</script>

});
</script>