<?php
require_once '../../db_connect.php';

// Fetch all driver credentials
$result = $conn->query("SELECT * FROM driver_credentials_check");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Driver Credentials Check - MTFRB</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="container mt-5" style="margin-left:250px;">
    <h2 class="mb-4">Driver Credentials Check</h2>
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
          <th>TODA ID</th>
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
          <td><?= htmlspecialchars($row['toda_id']) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html> 