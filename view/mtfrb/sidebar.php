<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<!-- Sidebar -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$active_classes = [
    'dashboard.php' => 'dashboard.php',
    'dashboardDriver.php' => 'dashboardDriver.php',
    'driver_management.php' => 'driver_management.php',
    'driver_credentials.php' => 'driver_credentials.php',
    'driver_earnings.php' => 'driver_earnings.php',
    'driver_trips.php' => 'driver_trips.php',
    'driver_profile.php' => 'driver_profile.php',
    'driver_settings.php' => 'driver_settings.php'
];
?>
<div class="sidebar fixed-sidebar" id="sidebar">
  <nav class="sidebar-nav">
    <div class="sidebar-section">
      Driver Dashboard
    </div>
    <a href="dashboard.php" class="sidebar-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
      <i class='bx bx-home'></i>
      MTFRB Dashboard
    </a>
    <div class="sidebar-submenu">
      <a href="#" class="sidebar-link driver-btn <?= in_array($current_page, ['dashboardDriver.php', 'driver_management.php', 'driver_credentials.php', 'driver_earnings.php', 'driver_trips.php', 'driver_profile.php', 'driver_settings.php']) ? 'active' : '' ?>">
        <i class='bx bx-car'></i>
        Driver Features
        <i class='bx bx-chevron-down ms-auto'></i>
      </a>
      <div class="submenu-items" style="display: none;">
        <a href="dashboardDriver.php" class="sidebar-link <?= $current_page === 'dashboardDriver.php' ? 'active' : '' ?>">
          <i class='bx bx-dashboard'></i>
          Driver Dashboard
        </a>
        <a href="driver_management.php" class="sidebar-link <?= $current_page === 'driver_management.php' ? 'active' : '' ?>">
          <i class='bx bx-user-circle'></i>
          Driver Management
        </a>
        <a href="driver_credentials.php" class="sidebar-link <?= $current_page === 'driver_credentials.php' ? 'active' : '' ?>">
          <i class='bx bx-id-card'></i>
          Driver Credentials
        </a>
        <a href="driver_earnings.php" class="sidebar-link <?= $current_page === 'driver_earnings.php' ? 'active' : '' ?>">
          <i class='bx bx-money'></i>
          Earnings
        </a>
        <a href="driver_trips.php" class="sidebar-link <?= $current_page === 'driver_trips.php' ? 'active' : '' ?>">
          <i class='bx bx-route'></i>
          My Trips
        </a>
        <a href="driver_profile.php" class="sidebar-link <?= $current_page === 'driver_profile.php' ? 'active' : '' ?>">
          <i class='bx bx-user'></i>
          Profile
        </a>
        <a href="driver_settings.php" class="sidebar-link <?= $current_page === 'driver_settings.php' ? 'active' : '' ?>">
          <i class='bx bx-cog'></i>
          Settings
        </a>
      </div>
    </div>
  </nav>
</div>
<!-- Sidebar Toggle Button (put this in your header/navbar) -->
<i class='bx bx-menu fs-3 me-2' id="sidebarOpen" style="cursor:pointer;"></i>

<style>
.fixed-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 250px;
    background: var(--sidebar-bg);
    z-index: 1000;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.sidebar-overlay {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    display: none;
}

.sidebar-overlay.active {
    display: block;
}

.sidebar-nav {
    padding: 2rem 1rem;
}

.sidebar-section {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    padding-left: 0.5rem;
}

.sidebar-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    margin: 0.25rem 0;
    color: var(--text);
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.sidebar-link:hover {
    background: var(--sidebar-hover);
    color: var(--primary);
}

.sidebar-link.active {
    background: var(--primary);
    color: white;
}

.sidebar-link i {
    margin-right: 0.75rem;
    font-size: 1.1rem;
}

.sidebar-submenu {
    margin: 1rem 0;
}

.submenu-items {
    margin-left: 1rem;
}

.badge {
    background: var(--danger);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.hot {
    background: var(--warning);
}
</style>

<script>
// Toggle sidebar
document.getElementById('sidebarOpen').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('active');
});

// Close sidebar when clicking outside
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.getElementById('sidebar').classList.remove('open');
    this.classList.remove('active');
});

// Toggle submenu items when driver button is clicked
document.querySelector('.driver-btn').addEventListener('click', function(e) {
    e.preventDefault();
    const submenu = this.nextElementSibling;
    const chevron = this.querySelector('.bx-chevron-down');
    
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        submenu.style.display = 'block';
        chevron.classList.remove('bx-chevron-down');
        chevron.classList.add('bx-chevron-up');
    } else {
        submenu.style.display = 'none';
        chevron.classList.remove('bx-chevron-up');
        chevron.classList.add('bx-chevron-down');
    }
});

// Close submenu when clicking outside
document.addEventListener('click', function(e) {
    const submenu = document.querySelector('.submenu-items');
    const driverBtn = document.querySelector('.driver-btn');
    
    if (!driverBtn.contains(e.target) && !submenu.contains(e.target)) {
        submenu.style.display = 'none';
        const chevron = driverBtn.querySelector('.bx-chevron-down');
        chevron.classList.remove('bx-chevron-up');
        chevron.classList.add('bx-chevron-down');
    }
});
</script>