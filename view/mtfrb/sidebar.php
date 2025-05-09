<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<!-- Sidebar -->
<div style="position:fixed;top:0;left:0;width:250px;height:100vh;z-index:1050;background:#232e3c;box-shadow:2px 0 12px rgba(44,62,80,0.07);border-top-right-radius:2rem;display:flex;flex-direction:column;">
  <nav class="sidebar-nav" style="flex:1;display:flex;flex-direction:column;gap:0.15rem;font-size:0.93rem;margin-top:1rem;">
    <div class="sidebar-section" style="color:#bfc9d4;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin:0.7rem 1rem 0.3rem 1rem;">Navigation</div>
    <a href="dashboard.php" class="sidebar-link active" style="font-size:0.93rem;padding:0.5rem 1rem;gap:0.5rem;"><i class='bx bx-home'></i> Dashboard</a>
    <div class="sidebar-submenu">
      <a href="#" class="sidebar-link" style="font-size:0.93rem;padding:0.5rem 1rem;gap:0.5rem;">
        <i class='bx bx-user'></i>
        Users
        <i class='bx bx-chevron-down ms-auto'></i>
      </a>
      <div class="submenu-items">
        <a href="passenger_management.php" class="sidebar-link" style="font-size:0.93rem;padding:0.45rem 1rem;">Passenger</a>
        <a href="driver_credentials.php" class="sidebar-link" style="font-size:0.93rem;padding:0.45rem 1rem;">Driver</a>
      </div>
    </div>
    <a href="add_association.php" class="sidebar-link" style="font-size:0.93rem;padding:0.5rem 1rem;gap:0.5rem;"><i class='bx bx-cog'></i> Associations</a>
    <a href="trips.php" class="sidebar-link" style="font-size:0.93rem;padding:0.5rem 1rem;gap:0.5rem;"><i class='bx bx-git-branch'></i> Trips</a>
    <a href="flat_fare.php" class="sidebar-link" style="font-size:0.93rem;padding:0.5rem 1rem;gap:0.5rem;"><i class='bx bx-map-pin'></i> Flat Fare</a>
    <a href="send_notification.php" class="sidebar-link" style="font-size:0.93rem;padding:0.5rem 1rem;gap:0.5rem;"><i class='bx bx-bell'></i> Send Notification</a>
    <a href="reports.php" class="sidebar-link" style="font-size:0.93rem;padding:0.5rem 1rem;gap:0.5rem;"><i class='bx bx-error'></i> Reports & Complaints <span class="badge hot">HOT</span></a>
  </nav>
</div>
<!-- Sidebar Toggle Button (put this in your header/navbar) -->
<i class='bx bx-menu fs-3 me-2' id="sidebarOpen" style="cursor:pointer;"></i> 