<?php
require_once '../db_connect.php';
$driver_id = isset($_GET['driver_id']) ? intval($_GET['driver_id']) : 0;
if (!$driver_id) {
    echo '<div class="alert alert-danger">Invalid driver ID.</div>';
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM driver WHERE driver_id = ?');
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$driver) {
    echo '<div class="alert alert-danger">Driver not found.</div>';
    exit;
}
?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <strong>Full Name:</strong> <?= htmlspecialchars($driver['fullname']) ?><br>
            <strong>Username:</strong> <?= htmlspecialchars($driver['username']) ?><br>
            <strong>Date of Birth:</strong> <?= htmlspecialchars($driver['dob']) ?><br>
            <strong>Address:</strong> <?= htmlspecialchars($driver['address']) ?><br>
            <strong>Phone:</strong> <?= htmlspecialchars($driver['phone'] ?? '') ?><br>
            <strong>Nationality:</strong> <?= htmlspecialchars($driver['nationality']) ?><br>
            <strong>Status:</strong> <?= htmlspecialchars($driver['status']) ?><br>
        </div>
        <div class="col-md-6">
            <strong>Franchise No:</strong> <?= htmlspecialchars($driver['franchise_no']) ?><br>
            <strong>OR No:</strong> <?= htmlspecialchars($driver['or_no']) ?><br>
            <strong>Plate No:</strong> <?= htmlspecialchars($driver['plate_no']) ?><br>
            <strong>TODA:</strong> <?= htmlspecialchars($driver['toda']) ?><br>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" id="approveBtn" data-driver-id="<?= $driver_id ?>">Approve</button>
        <button class="btn btn-danger" id="rejectBtn" data-driver-id="<?= $driver_id ?>">Reject</button>
    </div>
    <div id="confirmMsg" class="mt-3"></div>
</div>
<script>
function refreshPendingDrivers() {
    // Reload the pending drivers modal content
    fetch('users.php')
      .then(res => res.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTable = doc.querySelector('#pendingDriversModal .modal-body').innerHTML;
        document.querySelector('#pendingDriversModal .modal-body').innerHTML = newTable;
      });
}
document.getElementById('approveBtn').onclick = function() {
    var driverId = this.getAttribute('data-driver-id');
    fetch('confirm_driver_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'driver_id=' + encodeURIComponent(driverId) + '&action=approve'
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('confirmMsg').innerHTML = data.success ? '<div class="alert alert-success">Driver approved!</div>' : '<div class="alert alert-danger">' + (data.error || 'Error') + '</div>';
        if (data.success) {
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('accountConfirmModal')).hide();
                refreshPendingDrivers();
            }, 1200);
        }
    });
};
document.getElementById('rejectBtn').onclick = function() {
    var driverId = this.getAttribute('data-driver-id');
    fetch('confirm_driver_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'driver_id=' + encodeURIComponent(driverId) + '&action=reject'
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('confirmMsg').innerHTML = data.success ? '<div class="alert alert-warning">Driver rejected.</div>' : '<div class="alert alert-danger">' + (data.error || 'Error') + '</div>';
        if (data.success) {
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('accountConfirmModal')).hide();
                refreshPendingDrivers();
            }, 1200);
        }
    });
};
</script> 