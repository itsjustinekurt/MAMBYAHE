<?php
include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-color: #f7f7f7;
        }
        .contact-support {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            margin-top: 50px;
        }
        .header-icon {
            font-size: 50px;
            color: #007bff;
        }
        .contact-info {
            margin-top: 20px;
            font-size: 14px;
        }
        .email-link {
            color: #007bff;
        }
    </style>
</head>
<body>

<!-- Sidebar Toggle Button (Only visible on small screens) -->
<button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
    <i class="fas fa-bars"></i> 
</button>

<div class="contact-support text-center">
    <div class="header-icon">
        <a href="javascript:history.back()" class="position-absolute top-0 start-0 ms-3 mt-3">
            <i class="bx bx-arrow-back" style="font-size: 2rem; color: #000; cursor: pointer;"></i>
        </a>
        <i class="fas fa-headset"></i>
    </div>
   
    <h2>Contact Support</h2>
    <h5>MUNICIPAL TRICYCLE FRANCHISING & REGULATORY BOARD - IV B</h5>
    <p>6HGV+JRB, National Highway, Mamburao, dike, Mamburao, 5106 Occidental Mindoro</p>
    <h5>For complaints and other concerns, email us:</h5>
    <div class="contact-info">
        <p>Email Address: <a href="mailto:example@gmail.com" class="email-link">example@gmail.com</a></p>
        <p><i class="fas fa-phone-alt"></i> Public Assistance & Complaint Desk: (xx) xxxx-xxxx</p>
        <p><i class="fas fa-phone-alt"></i> Admin Office: (xx) xxxx-xxxx</p>
        <p><i class="fas fa-phone-alt"></i> Office of the Regional Director: (xx) xxxx-xxxx / 09xxxxxxxxxx</p>
    </div>
</div>

<!-- Sidebar Menu (Offcanvas) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="user-card text-center">
      <img src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg" alt="Profile Picture" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; margin-bottom: 10px;">
      <br>
      <strong>John Doe</strong><br>
      <span class="text-success"><i class="fas fa-circle"></i> Online</span><br>
      <a href="profile.php" class="btn btn-sm btn-outline-primary mt-2">View Profile</a>
    </div>
    <ul class="list-unstyled">
      <li><a href="home.php" class="d-block py-2"><i class="fas fa-home me-2"></i> Home</a></li>
      <li><a href="ride_details.php" class="d-block py-2"><i class="fas fa-car me-2"></i> Ride Details</a></li>
      <li><a href="support.php" class="d-block py-2"><i class="fas fa-headset me-2"></i> Contact Support</a></li>
      <li><a href="../logout.php" class="d-block py-2 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
    </ul>
  </div>
</div>

<!-- Bootstrap 5 JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
