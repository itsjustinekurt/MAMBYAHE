<!-- Sticky Topbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" style="background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <span class="navbar-brand fw-bold text-uppercase" style="color: #2563eb;">MTFRB</span>
        <div class="notification-container">
            <button class="btn btn-link position-relative" id="notifBellBtn">
                <i class='bx bx-bell fs-4' style="color: #2563eb;"></i>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="notifBadge" style="display: none;">0</span>
            </button>
            <div class="dropdown-menu notification-dropdown" id="notifDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notifications</h6>
                    <button class="btn btn-link btn-sm p-0" id="markAllNotifReadBtn">Mark all as read</button>
                </div>
                <div class="dropdown-divider"></div>
                <div id="notifList"></div>
                <div class="dropdown-divider"></div>
                <a href="notifications.php" class="dropdown-item text-center">View all notifications</a>
            </div>
        </div>
    </div>
</nav>
