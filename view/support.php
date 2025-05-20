<?php
session_start();
$page_title = 'Support';
include('sidebar.php');
?>

<!-- Header -->
<div class="header">
    <div class="header-left">
        <button class="menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
            <i class="fas fa-bars fs-4"></i>
        </button>
        <h5 class="mb-0"><?php echo htmlspecialchars($page_title); ?></h5>
    </div>
    <div class="header-right">
        <div class="notification-container">
            <div class="notification-icon dropdown">
                <a class="dropdown-toggle text-dark" href="#" role="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fs-4"></i>
                </a>
                <div class="dropdown-menu notification-dropdown" aria-labelledby="notifDropdown">
                    <div class="notification-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Notifications</h6>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item text-center">
                        <button class="btn btn-link text-muted">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.header {
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    height: 60px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 1002;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.menu-toggle {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
}

.menu-toggle:hover {
    color: #000;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.notification-container {
    position: relative;
}

.notification-icon {
    cursor: pointer;
}

.notification-dropdown {
    width: 350px;
    padding: 10px;
    margin-top: 10px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.notification-header {
    margin-bottom: 10px;
}

.notification-header h6 {
    margin: 0;
    font-size: 0.9rem;
}
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 250px;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1001;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #dee2e6;
        }

        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .sidebar-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .sidebar-content {
            flex: 1;
            padding: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .contact-support {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            margin-top: 20px;
        }

        .header-icon {
            font-size: 50px;
            color: #007bff;
            margin-bottom: 20px;
        }

        .contact-info {
            margin-top: 20px;
            font-size: 14px;
        }

        .email-link {
            color: #007bff;
        }

        .phone-number {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .phone-number i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="contact-support text-center">
                <div class="header-icon">
                    <i class="fas fa-headset"></i>
                </div>
               
                <h2>Contact Support</h2>
                <h5>MUNICIPAL TRICYCLE FRANCHISING & REGULATORY BOARD - IV B</h5>
                <p>6HGV+JRB, National Highway, Mamburao, dike, Mamburao, 5106 Occidental Mindoro</p>
                <h5>For complaints and other concerns, email us:</h5>
                <div class="contact-info">
                    <p>Email Address: <a href="mailto:example@gmail.com" class="email-link">example@gmail.com</a></p>
                    <div class="phone-number">
                        <i class="fas fa-phone-alt"></i>
                        <span>Public Assistance & Complaint Desk: (xx) xxxx-xxxx</span>
                    </div>
                    <div class="phone-number">
                        <i class="fas fa-phone-alt"></i>
                        <span>Admin Office: (xx) xxxx-xxxx</span>
                    </div>
                    <div class="phone-number">
                        <i class="fas fa-phone-alt"></i>
                        <span>Office of the Regional Director: (xx) xxxx-xxxx / 09xxxxxxxxxx</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
