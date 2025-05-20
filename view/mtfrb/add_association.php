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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Association - MTFRB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include 'header.php'; ?>
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
                            <table class="table table-hover compact">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">Chairman</th>
                                        <th style="width: 15%;">Contact</th>
                                        <th style="width: 25%;">Association Name</th>
                                        <th style="width: 30%;">Address</th>
                                        <th style="width: 15%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="assocTable">
                                <?php foreach ($associations as $assoc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($assoc['chairman']) ?></td>
                                    <td><?= htmlspecialchars($assoc['chairman_contact']) ?></td>
                                    <td><?= htmlspecialchars($assoc['name']) ?></td>
                                    <td><?= htmlspecialchars($assoc['address']) ?></td>
                                    <td>
                                        <a href="?edit=<?= $assoc['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="?delete=<?= $assoc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this association?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($associations)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No associations found.</td>
                                </tr>
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