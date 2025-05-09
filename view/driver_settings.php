<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get driver information
    $stmt = $pdo->prepare("SELECT * FROM driver WHERE driver_id = :driver_id");
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Settings</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <style>
        .settings-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .settings-icon {
            font-size: 2rem;
            color: #198754;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'components/driver_sidebar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Settings</h2>
        
        <div class="row">
            <!-- Account Settings -->
            <div class="col-md-6 mb-4">
                <div class="settings-card">
                    <i class="fas fa-user-cog settings-icon"></i>
                    <h4>Account Settings</h4>
                    <form id="accountForm" class="mt-3">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($driver['username'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Account</button>
                    </form>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div class="col-md-6 mb-4">
                <div class="settings-card">
                    <i class="fas fa-bell settings-icon"></i>
                    <h4>Notification Settings</h4>
                    <form id="notificationForm" class="mt-3">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                            <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="smsNotifications" checked>
                            <label class="form-check-label" for="smsNotifications">SMS Notifications</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="rideRequests" checked>
                            <label class="form-check-label" for="rideRequests">Ride Requests</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="rideUpdates" checked>
                            <label class="form-check-label" for="rideUpdates">Ride Updates</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </form>
                </div>
            </div>
            
            <!-- Privacy Settings -->
            <div class="col-md-6 mb-4">
                <div class="settings-card">
                    <i class="fas fa-shield-alt settings-icon"></i>
                    <h4>Privacy Settings</h4>
                    <form id="privacyForm" class="mt-3">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="showProfile" checked>
                            <label class="form-check-label" for="showProfile">Show Profile to Passengers</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="showLocation" checked>
                            <label class="form-check-label" for="showLocation">Share Location with Passengers</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="showRating" checked>
                            <label class="form-check-label" for="showRating">Show Rating to Passengers</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
                    </form>
                </div>
            </div>
            
            <!-- App Settings -->
            <div class="col-md-6 mb-4">
                <div class="settings-card">
                    <i class="fas fa-mobile-alt settings-icon"></i>
                    <h4>App Settings</h4>
                    <form id="appForm" class="mt-3">
                        <div class="mb-3">
                            <label class="form-label">Language</label>
                            <select class="form-select" id="language">
                                <option value="en">English</option>
                                <option value="tl">Tagalog</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Theme</label>
                            <select class="form-select" id="theme">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="system">System Default</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Distance Unit</label>
                            <select class="form-select" id="distanceUnit">
                                <option value="km">Kilometers</option>
                                <option value="mi">Miles</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save App Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        // Handle form submissions
        document.getElementById('accountForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add account update logic here
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Account settings updated successfully',
                showConfirmButton: false,
                timer: 1500
            });
        });

        document.getElementById('notificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add notification settings update logic here
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Notification preferences saved',
                showConfirmButton: false,
                timer: 1500
            });
        });

        document.getElementById('privacyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add privacy settings update logic here
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Privacy settings updated',
                showConfirmButton: false,
                timer: 1500
            });
        });

        document.getElementById('appForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add app settings update logic here
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'App settings saved',
                showConfirmButton: false,
                timer: 1500
            });
        });
    </script>
</body>
</html> 