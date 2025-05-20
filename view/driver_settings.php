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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require_once 'theme_handler.php'; add_theme_styles(); ?>
    <style>
        /* Layout */
        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Theme Variables */
        :root {
            --primary-color: #198754;
            --background-color: #f8f9fa;
            --card-background: white;
            --text-color: #333;
            --border-color: #dee2e6;
        }

        .theme-dark {
            --primary-color: #198754;
            --background-color: #1a1a1a;
            --card-background: #2d2d2d;
            --text-color: #ffffff;
            --border-color: #444;
        }

        .sidebar {
            width: 280px;
            background: var(--card-background);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
        }

        .content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            background: var(--background-color);
        }

        /* Settings Card */
        .settings-card {
            background: var(--card-background);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }
        
        .settings-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .form-control {
            background-color: var(--card-background);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #157347;
            border-color: #157347;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
            }

            .content {
                margin-left: 0;
            }

            .sidebar.active {
                left: 0;
            }
        }    padding: 1rem;
            height: 100vh;
        }
        
        .content {
            flex: 1;
            padding: 1rem;
        }
    </style>
</head>
<body <?php add_theme_class(); ?>>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <?php include 'components/driver_sidebar.php'; ?>
        </aside>

        <!-- Content -->
        <main class="content">
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
        </main>
            </div>
        </main>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <?php 
    require_once 'csrf_handler.php';
    session_start();
    ?>
    <script>
        // Get CSRF token from server
        const csrf_token = '<?php echo get_csrf_token(); ?>';

        // Handle form submissions
        document.getElementById('accountForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            formData.append('driver_id', '<?php echo $_SESSION['driver_id']; ?>');
            formData.append('csrf_token', csrf_token);

            try {
                const response = await fetch('update_account_settings.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrf_token,
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating your account settings'
                });
            }
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

        document.getElementById('appForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            formData.append('csrf_token', csrf_token);

            try {
                const response = await fetch('update_app_settings.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrf_token
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Update session variables
                    const { language, theme, distance_unit } = result.settings;
                    
                    // Update the UI
                    const languageSelect = form.querySelector('#language');
                    const themeSelect = form.querySelector('#theme');
                    const distanceSelect = form.querySelector('#distanceUnit');

                    if (languageSelect) languageSelect.value = language;
                    if (themeSelect) themeSelect.value = theme;
                    if (distanceSelect) distanceSelect.value = distance_unit;

                    // Apply theme immediately
                    document.body.classList.remove('theme-light', 'theme-dark');
                    document.body.classList.add(theme === 'dark' ? 'theme-dark' : 'theme-light');
                    localStorage.setItem('theme', theme);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating your app settings'
                });
            }
        });
    </script>
</body>
</html>