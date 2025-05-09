<?php
// Always start with a clean session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();
}
session_start();
ob_start();

// Include database configuration
require_once '../config/database.php';

// Use PDO connection instead of mysqli
try {
    $login_error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $role = $_POST['role'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($role === 'passenger') {
            $stmt = $pdo->prepare("SELECT * FROM passenger WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $table = "passenger";
            $idField = "passenger_id";
        } elseif ($role === 'driver') {
            $stmt = $pdo->prepare("SELECT * FROM driver WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $table = "driver";
            $idField = "driver_id";
        }

        if ($user) {
            // Check if blocked
            if ($user['status'] === 'blocked') {
                $login_error = "Your account has been blocked by MTFRB.";
            }
            // Check suspended
            elseif ($user['status'] === 'suspended') {
                $today = date('Y-m-d');
                if ($today <= $user['suspend_until']) {
                    $login_error = "Account suspended until {$user['suspend_until']}.";
                } else {
                    // Lift suspension
                    $stmt = $pdo->prepare("UPDATE $table SET status='active', suspend_until=NULL WHERE $idField=?");
                    $stmt->execute([$user[$idField]]);
                    $user['status'] = 'active';
                }
            }

            // Password check
            if (empty($login_error)) {
                if (!password_verify($password, $user['password'])) {
                    $login_error = "Invalid password";
                } else {
                    // Set new session data
                    $_SESSION['username'] = $user['username'];
                    $_SESSION[$idField] = $user[$idField];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['role'] = $role;

                    if ($role === 'passenger') {
                        header('Location: dashboardPassenger.php');
                        exit();
                    } elseif ($role === 'driver') {
                        if ($user['status'] === 'pending') {
                            $login_error = "Your account is pending for MTFRB approval.";
                        } elseif ($user['status'] === 'rejected') {
                            $login_error = "Your account has been rejected by MTFRB.";
                        } elseif ($user['status'] !== 'approved') {
                            $login_error = "Your account is not active.";
                        } else {
                            $_SESSION['driver_id'] = $user['driver_id'];
                            // Update driver's online status
                            $stmt = $pdo->prepare("UPDATE driver SET is_online = 'online' WHERE driver_id = ?");
                            $stmt->execute([$user['driver_id']]);
                            header('Location: dashboardDriver.php');
                            exit();
                        }
                    }
                }
            }
        } else {
            $login_error = "Invalid username";
        }
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      min-height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .bg-illustration {
      position: absolute;
      inset: 0;
      width: 100vw;
      height: 100vh;
      z-index: 0;
      background: url('received_1882822929171036.jpeg') center/cover no-repeat;
      filter: blur(1.5px) brightness(0.85);
    }
   
    .login-glass {
      position: relative;
      z-index: 2;
      background: rgba(255,255,255,0.10);
      border-radius: 18px;
      border: 1.5px solid rgba(255,255,255,0.25);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.25);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      padding: 38px 36px 32px 36px;
      max-width: 370px;
      width: 100%;
      margin: 0 auto;
      text-align: center;
      color: #fff;
      box-sizing: border-box;
    }
    .login-glass h2 {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 28px;
      color: #fff;
      letter-spacing: 1px;
    }
    .input-group {
      position: relative;
      margin-bottom: 22px;
    }
    .input-group input {
      width: 100%;
      padding: 13px 44px 13px 18px;
      border-radius: 30px;
      border: none;
      background: rgba(255,255,255,0.18);
      color: #fff;
      font-size: 1.08rem;
      outline: none;
      box-shadow: none;
      transition: background 0.2s;
    }
    .input-group input::placeholder {
      color: #e0e0e0;
      opacity: 1;
    }
    .input-group .input-icon {
      position: absolute;
      right: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #fff;
      font-size: 1.2rem;
      opacity: 0.8;
    }
    .login-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
      font-size: 0.98rem;
      color: #fff;
    }
    .login-options label {
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      font-weight: 400;
    }
    .login-options input[type="checkbox"] {
      accent-color: #fff;
      width: 16px;
      height: 16px;
      margin: 0 4px 0 0;
    }
    .login-options a {
      color: #fff;
      text-decoration: underline;
      font-size: 0.98rem;
      opacity: 0.85;
      transition: color 0.2s;
    }
    .login-options a:hover {
      color: #e0e0e0;
    }
    .login-btn {
      width: 100%;
      padding: 13px 0;
      font-size: 1.1rem;
      color: #fff;
      background: linear-gradient(90deg, #7f7fd5 0%, #86a8e7 100%);
      border: none;
      border-radius: 30px;
      cursor: pointer;
      margin-top: 8px;
      margin-bottom: 10px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(31,38,135,0.10);
      transition: background 0.2s;
    }
    .login-btn:hover {
      background: linear-gradient(90deg, #86a8e7 0%, #7f7fd5 100%);
    }
    .register-link {
      color: #fff;
      font-size: 1rem;
      margin-top: 10px;
      display: block;
      opacity: 0.95;
    }
    .register-link a {
      color: #fff;
      text-decoration: underline;
      font-weight: 500;
      margin-left: 4px;
      opacity: 0.95;
    }
    @media (max-width: 600px) {
      .login-glass {
        padding: 24px 8px 18px 8px;
        max-width: 98vw;
      }
    }
    .input-group select {
      background: rgba(255, 255, 255, 0.18);
      color: #fff;
      border-radius: 30px;
      border: 1px solid rgba(255, 255, 255, 0.5);
      font-size: 1.2rem;
      padding: 15px 20px;
      width: 100%;
      outline: none;
      transition: background 0.2s;
    }

    .input-group select:hover {
      background: rgba(255, 255, 255, 0.25);
    }
  </style>
</head>
<body>
  <div class="bg-illustration"></div>
  <div class="mountain-overlay"></div>
  <div class="login-glass">
    <h2>Login</h2>
    <form method="POST" autocomplete="off">
      <div class="input-group mb-3">
        <select name="role" class="form-select" required style="background:rgba(255,255,255,0.18); color:#fff; border-radius:30px; border:none; font-size:1.08rem; padding:13px 18px;">
          <option value="passenger">Passenger</option>
          <option value="driver">Driver</option>
        </select>
      </div>
      <div class="input-group">
        <input type="text" name="username" placeholder="Username" required />
        <span class="input-icon"><i class="fa fa-user"></i></span>
      </div>
      <div class="input-group">
        <input type="password" name="password" placeholder="Password" required />
        <span class="input-icon"><i class="fa fa-lock"></i></span>
      </div>
      <div class="login-options">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="#" id="forgotPasswordLink" style="text-decoration:underline;">Forgot Password?</a>
      </div>
      <button type="submit" class="login-btn">Login</button>
      <div class="register-link">
        Don't have an account?
        <button type="button" id="createNewBtn" class="btn btn-link p-0 ms-1" style="color:#fff;text-decoration:underline;font-weight:500;">Create New</button>
      </div>
    </form>
    <?php if (!empty($login_error)): ?>
      <div class="alert alert-danger mt-3" style="font-size:0.98rem;"> <?= htmlspecialchars($login_error) ?> </div>
    <?php endif; ?>
  </div>

  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="forgotPasswordForm">
            <div class="mb-3">
              <label for="fp_username" class="form-label">Username</label>
              <input type="text" class="form-control" id="fp_username" name="username" required>
            </div>
            <div class="mb-3" id="fp_email_field">
              <label for="fp_email" class="form-label">Email</label>
              <input type="email" class="form-control" id="fp_email" name="email">
            </div>
            <div class="mb-3" id="fp_franchise_field" style="display:none;">
              <label for="fp_franchise" class="form-label">Franchise Number</label>
              <input type="text" class="form-control" id="fp_franchise" name="franchiseNumber">
            </div>
            <div class="mb-3">
              <label for="fp_new_password" class="form-label">New Password</label>
              <input type="password" class="form-control" id="fp_new_password" name="new_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (required for modal functionality) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 JS (required for Swal) -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', function(event) {
      event.preventDefault();
      const role = document.querySelector('select[name="role"]').value;
      const username = document.getElementById('fp_username').value;
      const email = document.getElementById('fp_email').value;
      const franchiseNumber = document.getElementById('fp_franchise').value;
      const newPassword = document.getElementById('fp_new_password').value;

      const payload = { role, username, newPassword };
      if (role === 'passenger') payload.email = email;
      if (role === 'driver') payload.franchiseNumber = franchiseNumber;

      fetch('reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Password Reset Successful',
            text: 'Your password has been updated successfully.',
            timer: 2000,
            showConfirmButton: false
          });
          $('#forgotPasswordModal').modal('hide');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'Failed to reset password.'
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred while resetting the password.'
        });
      });
    });

    document.querySelector('select[name="role"]').addEventListener('change', function() {
      const role = this.value;
      document.getElementById('fp_email_field').style.display = (role === 'passenger') ? 'block' : 'none';
      document.getElementById('fp_franchise_field').style.display = (role === 'driver') ? 'block' : 'none';
    });

    document.getElementById('createNewBtn').addEventListener('click', function() {
      const role = document.querySelector('select[name="role"]').value;
      if (role === 'passenger') {
        window.location.href = 'signupPassenger.php';
      } else if (role === 'driver') {
        window.location.href = 'signupDriver.php';
      }
    });

    // Show forgot password modal on link click
    document.getElementById('forgotPasswordLink').addEventListener('click', function(e) {
      e.preventDefault();
      var modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
      modal.show();
    });

    // Move focus to main login username input after modal closes (accessibility fix)
    document.getElementById('forgotPasswordModal').addEventListener('hidden.bs.modal', function () {
      var loginInput = document.querySelector('input[name="username"]:not(#fp_username)');
      if (loginInput) {
        loginInput.focus();
      }
    });
  </script>
</body>
</html>
