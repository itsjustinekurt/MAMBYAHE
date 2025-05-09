<?php
// Get the current page title
$page_title = isset($page_title) ? $page_title : 'Mambyahe';
?>
<div class="header">
    <div class="header-left">
        <?php if (isset($_SESSION['driver_id']) || isset($_SESSION['passenger_id'])): ?>
            <button class="menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                <i class="fas fa-bars fs-4"></i>
            </button>
        <?php endif; ?>
        <h5 class="mb-0"><?php echo htmlspecialchars($page_title); ?></h5>
    </div>
    <div class="header-right">
        <?php if (isset($_SESSION['driver_id']) || isset($_SESSION['passenger_id'])): ?>
            <div class="notification-container">
                <div class="notification-icon dropdown">
                    <a class="dropdown-toggle text-dark" href="#" role="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fs-4"></i>
                        <?php if (isset($unread_count) && $unread_count > 0): ?>
                            <span class="badge">
                                <?= $unread_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu notification-dropdown" aria-labelledby="notifDropdown">
                        <div class="notification-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notifications</h6>
                            <?php if (isset($unread_count) && $unread_count > 0): ?>
                                <button class="btn btn-link btn-sm text-muted" id="markAllReadBtn">
                                    Mark all as read
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-divider"></div>
                        <!-- Notification content will be loaded dynamically -->
                        <div class="dropdown-item text-center">
                            <button class="btn btn-link text-muted" onclick="loadNotifications()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
    display: flex;
    align-items: center;
    padding: 0 15px;
}

.header-left {
    display: flex;
    align-items: center;
}

.header-right {
    margin-left: auto;
    display: flex;
    align-items: center;
}

.menu-toggle {
    background: white;
    border: none;
    border-radius: 5px;
    padding: 10px;
    margin-right: 10px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.menu-toggle:hover {
    background-color: #f8f9fa;
}

.notification-container {
    position: relative;
}

.notification-icon {
    background: white;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-icon:hover {
    background-color: #f8f9fa;
}

.notification-icon .badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 0.25em 0.6em;
    font-size: 0.75rem;
}

.notification-dropdown {
    width: 350px;
    max-width: 95vw;
    min-width: 250px;
    max-height: 400px;
    overflow-y: auto;
    padding: 0;
    margin-top: 10px;
    border: 1px solid rgba(0,0,0,0.1);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.notification-header {
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.notification-item {
    padding: 10px 15px;
    border-bottom: 1px solid #dee2e6;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.bg-light {
    background-color: #e9ecef;
}

/* Adjust main content to account for header */
body {
    padding-top: 60px;
}

/* Adjust map container if present */
#map {
    height: calc(100vh - 60px);
    top: 60px;
}
</style> 