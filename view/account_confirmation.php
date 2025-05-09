<?php
// mtfrb_dashboard.php
session_start();
include '../view/connection.php'; // Adjust path as needed

// Fetch all pending driver requests
$query = "SELECT * FROM driver WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MTFRB - Driver Account Review</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap.min.css" rel="stylesheet">
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/boxicons.js"></script>
</head>
<body class="p-4 bg-light">
  <div class="container">
    <h2 class="mb-4">Pending Driver Accounts</h2>
    <table class="table table-bordered table-hover bg-white">
      <thead class="table-dark">
        <tr>
          <th>Name</th>
          <th>Username</th>
          <th>Phone</th>
          <th>Nationality</th>
          <th>Date of Birth</th>
          <th>Address</th>
          <th>Franchise No.</th>
          <th>O.R. No.</th>
          <th>Make</th>
          <th>Motor No./Engine No.</th>
          <th>Chassis No./Serial No.</th>
          <th>Plate No.</th>
          <th>TODA</th>
          <th>Gov. ID Type</th>
          <th>Gov. ID Picture</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['nationality']) ?></td>
            <td><?= htmlspecialchars($row['dob']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($row['franchise_no']) ?></td>
            <td><?= htmlspecialchars($row['or_no']) ?></td>
            <td><?= htmlspecialchars($row['make']) ?></td>
            <td><?= htmlspecialchars($row['motor_no']) ?></td>
            <td><?= htmlspecialchars($row['chassis_no']) ?></td>
            <td><?= htmlspecialchars($row['plate_no']) ?></td>
            <td><?= htmlspecialchars($row['toda']) ?></td>
            <td><?= htmlspecialchars($row['gov_id_type']) ?></td>
            <td>
            <a href="../uploads/<?= htmlspecialchars($row['gov_id_picture']) ?>" target="_blank">View</a>

            </td>
            <td>
              <form method="POST" action="review_driver.php" style="display:inline-block;">
                <input type="hidden" name="driver_id" value="<?= $row['driver_id'] ?>">
                <button name="approve" class="btn btn-success btn-sm">Approve</button>
              </form>
              <form method="POST" action="review_driver.php" style="display:inline-block;">
                <input type="hidden" name="driver_id" value="<?= $row['driver_id'] ?>">
                <button name="reject" class="btn btn-danger btn-sm">Reject</button>
              </form>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <?php include __DIR__.'/tab_session_protect.php'; ?>
</body>
</html>
