<?php
session_start();
require_once '../db_connect.php';

// Fetch all approved drivers for owner/chairman dropdown
$drivers = [];
$result = $conn->query("SELECT driver_id, fullname, phone FROM driver WHERE status = 'approved' ORDER BY fullname ASC");
while ($row = $result->fetch_assoc()) {
    $drivers[] = $row;
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM associations WHERE id = $del_id");
    header('Location: add_association.php');
    exit;
}

// Handle edit (fetch for form)
$edit_assoc = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM associations WHERE id = $edit_id");
    $edit_assoc = $result->fetch_assoc();
}

// Handle add/update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $chairman = trim($_POST['chairman'] ?? '');
    $chairman_contact = trim($_POST['chairman_contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $edit_id = $_POST['edit_id'] ?? '';
    $created_at = date('Y-m-d H:i:s');

    if ($name && $chairman && $chairman_contact && $address) {
        if ($edit_id) {
            // Update
            $query = "UPDATE associations SET name='$name', chairman='$chairman', chairman_contact='$chairman_contact', address='$address' WHERE id=$edit_id";
            if ($conn->query($query)) {
                $message = '<div class="alert alert-success">Association updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to update association.</div>';
            }
        } else {
            // Add
            $query = "INSERT INTO associations (name, chairman, chairman_contact, address, created_at) VALUES ('$name', '$chairman', '$chairman_contact', '$address', '$created_at')";
            if ($conn->query($query)) {
                $message = '<div class="alert alert-success">Association added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to add association.</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-warning">Please fill in all required fields.</div>';
    }
    $edit_assoc = null;
}

// Fetch all associations
$associations = [];
$result = $conn->query('SELECT * FROM associations ORDER BY id DESC');
while ($row = $result->fetch_assoc()) {
    $associations[] = $row;
}

// Helper: get number of members for a team name
function get_member_count($conn, $name) {
    $result = $conn->query("SELECT COUNT(*) as count FROM driver WHERE toda = '$name' AND status = 'approved'");
    $row = $result->fetch_assoc();
    return $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Association</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .main-container { background: #fff; border-radius: 1rem; max-width: 900px; margin: 2rem auto; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 2rem; margin-left: 260px; }
        .form-label { font-weight: 600; }
        .table thead th { font-weight: 600; font-size: 0.95rem; }
        .table tbody td { font-size: 0.95rem; }
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
        @media (max-width: 600px) { .sidebar { width: 90vw; } .main-container { margin-left: 0; } }
    </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="container py-4" style="margin-left:250px;">
    <div class="main-container">
        
        <?php if ($message) echo $message; ?>
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><?php echo $edit_assoc ? 'Edit Association' : 'Add Association'; ?></h5>
                        <form method="post" autocomplete="off">
                            <input type="hidden" name="edit_id" value="<?= $edit_assoc ? htmlspecialchars($edit_assoc['id']) : '' ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Association Name</label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?= $edit_assoc ? htmlspecialchars($edit_assoc['name']) : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="chairman" class="form-label">Chairman</label>
                                <input type="text" class="form-control" id="chairman" name="chairman" required value="<?= $edit_assoc ? htmlspecialchars($edit_assoc['chairman']) : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chairman Phone Number</label>
                                <input type="text" id="chairman_contact" name="chairman_contact" class="form-control" required value="<?= $edit_assoc ? htmlspecialchars($edit_assoc['chairman_contact']) : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Origin</label>
                                <input type="text" class="form-control" id="address" name="address" required value="<?= $edit_assoc ? htmlspecialchars($edit_assoc['address']) : '' ?>">
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-dark"><?php echo $edit_assoc ? 'Update' : 'Add'; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Associations List</h5>
                            <input type="text" id="searchBox" class="form-control form-control-sm w-50" placeholder="Search...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="assocTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Chairman</th>
                                        <th>No. of Members</th>
                                        <th>Name</th>
                                        <th>Origin</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($associations as $assoc): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($assoc['chairman']) ?></td>
                                        <td><?= get_member_count($conn, $assoc['name']) ?></td>
                                        <td><?= htmlspecialchars($assoc['name']) ?></td>
                                        <td><?= htmlspecialchars($assoc['address']) ?></td>
                                        <td>
                                            <a href="?edit=<?= $assoc['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <a href="?delete=<?= $assoc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this association?');">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($associations)): ?>
                                    <tr><td colspan="5" class="text-center">No associations found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
document.getElementById('sidebarOpen').onclick = function() {
    sidebar.classList.add('open');
    sidebarOverlay.style.display = 'block';
};
document.getElementById('sidebarClose').onclick = function() {
    sidebar.classList.remove('open');
    sidebarOverlay.style.display = 'none';
};
sidebarOverlay.onclick = function() {
    sidebar.classList.remove('open');
    sidebarOverlay.style.display = 'none';
};
// Search filter for associations table
const searchBox = document.getElementById('searchBox');
const assocTable = document.getElementById('assocTable').getElementsByTagName('tbody')[0];
searchBox.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    Array.from(assocTable.rows).forEach(row => {
        const name = row.cells[2].innerText.toLowerCase();
        const chairman = row.cells[0].innerText.toLowerCase();
        const address = row.cells[3].innerText.toLowerCase();
        row.style.display = (name.includes(val) || chairman.includes(val) || address.includes(val)) ? '' : 'none';
    });
});
// Number of members auto-update with improved UX
const nameInput = document.getElementById('name');
const numMembersInput = document.getElementById('num_members');
const numMembersMsg = document.getElementById('numMembersMsg');
const numMembersSpinner = document.getElementById('numMembersSpinner');

async function updateNumMembers() {
    const assocName = nameInput.value.trim();
    if (!assocName) {
        numMembersInput.value = '';
        numMembersMsg.textContent = '';
        return;
    }
    numMembersSpinner.style.display = '';
    numMembersInput.style.background = '#f8f9fa';
    numMembersMsg.textContent = '';
    try {
        const res = await fetch('get_member_count.php?team_name=' + encodeURIComponent(assocName));
        const data = await res.json();
        numMembersSpinner.style.display = 'none';
        if (data.count > 0) {
            numMembersInput.value = data.count;
            numMembersMsg.textContent = '';
        } else {
            numMembersInput.value = '';
            numMembersMsg.textContent = 'No members yet.';
        }
        numMembersInput.style.background = '#d1e7dd';
        setTimeout(() => { numMembersInput.style.background = '#f8f9fa'; }, 500);
    } catch (e) {
        numMembersSpinner.style.display = 'none';
        numMembersInput.value = '';
        numMembersMsg.textContent = 'Error fetching member count.';
    }
}
nameInput.addEventListener('input', updateNumMembers);
<?php if ($edit_assoc): ?>
updateNumMembers();
<?php endif; ?>
// Enable Bootstrap tooltip
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});
// Prevent manual editing or pasting
numMembersInput.addEventListener('keydown', e => e.preventDefault());
numMembersInput.addEventListener('paste', e => e.preventDefault());
</script>
</body>
</html>