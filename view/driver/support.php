<?php include '../components/driver_sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - MTFRB</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .support-container {
            max-width: 420px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 0;
            overflow: hidden;
        }
        .support-header {
            background: #d3d3d3;
            text-align: center;
            padding: 32px 16px 16px 16px;
        }
        .support-header img {
            width: 80px;
            margin-bottom: 12px;
        }
        .support-title {
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        .support-address {
            font-size: 0.98em;
            color: #222;
            margin-bottom: 0;
        }
        .support-contact-box {
            background: #f8f8f8;
            text-align: center;
            padding: 24px 16px;
        }
        .support-contact-box i {
            font-size: 2.2em;
            color: #888;
            margin-bottom: 8px;
        }
        .support-contact-box .contact-email {
            font-weight: 500;
            color: #222;
            font-size: 1.05em;
        }
        .support-footer {
            background: #eaeaea;
            padding: 18px 16px;
            font-size: 0.98em;
        }
        .support-footer strong {
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="support-container">
    <div class="support-header">
        <img src="https://i.ibb.co/6b8kQ2d/ltfrb-logo.png" alt="MTFRB Logo">
        <div class="support-title">LAND TRANSPORTATION<br>FRANCHISING & REGULATORY<br>BOARD - IV B</div>
        <div class="support-address">6HGV+JRB, National Highway, Mamburao,<br>dike, Mamburao, 5106 Occidental Mindoro</div>
    </div>
    <div class="support-contact-box">
        <i class="fas fa-user-headset"></i>
        <div>For complaints and other concerns, email us:</div>
        <div class="contact-email">example@gmail.com</div>
    </div>
    <div class="support-footer">
        <div><strong>Email Address:</strong><br>example@gmail.com</div>
        <div class="mt-2"><strong>Public Assistance & Complaint Desk:</strong><br>(xx) xxxx-xxxx</div>
        <div class="mt-2"><strong>Admin Office:</strong><br>(xx) xxxx-xxxx</div>
        <div class="mt-2"><strong>Office of the Regional Director:</strong><br>(xx) xxxx-xxxx / 09xxxxxxxxx</div>
    </div>
</div>
<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div class="modal-body">
        <div class="mb-3">
          <i class="fas fa-sign-out-alt fa-3x text-danger"></i>
        </div>
        <h4 class="mb-1">Come back soon!</h4>
        <p class="text-muted">Are you sure you want to logout?</p>
        <div class="d-grid gap-2">
          <form method="POST">
            <button type="submit" name="confirm_logout" class="btn btn-dark">Yes, Logout</button>
          </form>
          <button type="button" class="btn btn-link text-danger" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/../tab_session_protect.php'; ?>
</body>
</html> 