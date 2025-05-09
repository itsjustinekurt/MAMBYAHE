<?php
require_once '../db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTFRB Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background: #f5f7fa; }
        .dashboard-card { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .stat-label { font-size: 0.9rem; color: #888; }
        .stat-value { font-size: 1.3rem; font-weight: bold; }
        .icon-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .icon-dropoff { background: #e0e7ff; color: #3730a3; }
        #map { height: 350px; border-radius: 1rem; }
        /* Sidebar styles */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.2);
            z-index: 1040;
            display: none;
        }
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: 250px;
            background: #232e3c;
            z-index: 1050;
            border-top-right-radius: 2rem;
            box-shadow: 2px 0 12px rgba(44,62,80,0.07);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
        }
      
     
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem 1rem 1.5rem;
            background: #1a2230;
            border-radius: 1rem;
            margin: 0 1rem 1rem 1rem;
        }
        .sidebar-user-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: #fff;
        }
        .sidebar-user-name {
            font-weight: 700; color: #fff; font-size: 1rem;
        }
        .sidebar-user-email {
            color: #bfc9d4; font-size: 0.9rem;
        }
        .sidebar-section {
            color: #bfc9d4;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 1.2rem 1.5rem 0.5rem 1.5rem;
        }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1.5rem;
            color: #bfc9d4;
            font-weight: 500;
            font-size: 1.05rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            position: relative;
        }
        .sidebar-link.active, .sidebar-link:hover {
            background: #2d3a4b;
            color: #fff;
        }
        .sidebar-link i { font-size: 1.3rem; }
        .sidebar-close { display: none !important; }
        /* Submenu styles */
        .sidebar-submenu {
            position: relative;
        }
        .submenu-items {
            display: none;
            flex-direction: column;
            padding-left: 2.5rem;
            margin-top: 0.25rem;
        }
        .sidebar-submenu.active .submenu-items {
            display: flex;
        }
        .submenu-link {
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            color: #bfc9d4;
        }
        .submenu-link:hover, .submenu-link.active {
            color: #fff;
            background: #263143;
        }
        .sidebar-submenu .sidebar-link i.bx-chevron-down {
            transition: transform 0.3s;
        }
        .sidebar-submenu.active .sidebar-link i.bx-chevron-down {
            transform: rotate(180deg);
        }
        .badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2em 0.6em;
            border-radius: 1em;
            margin-left: 0.5em;
            vertical-align: middle;
        }
        .badge.new {
            background: #1de9b6;
            color: #232e3c;
        }
        .badge.hot {
            background: #ff1744;
            color: #fff;
        }
        @media (max-width: 900px) {
            .sidebar-overlay { display: none !important; }
    .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background: #8fa195; z-index: 1050; transform: none !important; transition: none; border-top-right-radius: 2rem; }
    .sidebar.open { transform: none; }
    .sidebar-header { display: flex; align-items: center; gap: 0.75rem; padding: 1.5rem 1rem 1rem 1.5rem; }
    .sidebar-logo { width: 40px; height: 40px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; }
    .sidebar-title { font-weight: 800; font-size: 1.2rem; color: #fff; }
    .sidebar-subtitle { font-size: 0.85rem; color: #e0e7ef; font-weight: 600; }
    .sidebar-nav { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
    .sidebar-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.7rem 1.5rem; color: #222; font-weight: 500; font-size: 1.05rem; border-radius: 0.5rem; text-decoration: none; transition: background 0.2s; }
    .sidebar-link:hover { background: #e5e7eb; color: #111; }
    .sidebar-link i { font-size: 1.3rem; }
    .sidebar-close { display: none !important; }
    @media (max-width: 600px) { .sidebar { width: 90vw; } .container.py-4, .main-container, main.container, .content-container { margin-left: 0 !important; } }
    /* Add these styles for the notification dropdown */
    .notification-dropdown {
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 0.5rem;
        z-index: 1000;
        display: none;
    }

    .notification-dropdown.show {
        display: block;
    }

    .notification-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #eee;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    #notifBadge {
        font-size: 0.7rem;
        padding: 0.25em 0.6em;
    }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<!-- Sticky Topbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top" style="z-index:1100;height:60px;">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <span class="fw-bold text-uppercase fs-5">MTFRB</span>
    </div>
</nav>
<div class="container-fluid" style="margin-left:250px;min-height:100vh;background:#f5f7fa;padding-top:60px;">
    <!-- Add this right after the navbar -->
    <div class="notification-container position-relative">
        <button class="btn btn-link position-relative" id="notifBellBtn">
            <i class='bx bx-bell fs-4'></i>
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

    <!-- Add this debug div temporarily -->
    <div id="debugInfo" style="display: none;"></div>

    <div class="row g-2 pt-2">
        <!-- Stat Cards -->
        <div class="col-md-4">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body d-flex align-items-center gap-2 py-2">
                    <div class="icon-circle bg-primary text-white" style="width:32px;height:32px;font-size:1rem;"><i class='bx bx-user'></i></div>
                    <div>
                        <div class="stat-label" style="font-size:0.8rem;">Total Passengers</div>
                        <div class="stat-value" id="statUsers" style="font-size:1.1rem;">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body d-flex align-items-center gap-2 py-2">
                    <div class="icon-circle bg-success text-white" style="width:32px;height:32px;font-size:1rem;"><i class='bx bx-group'></i></div>
                    <div>
                        <div class="stat-label" style="font-size:0.8rem;">Total Associations</div>
                        <div class="stat-value" id="statAssociations" style="font-size:1.1rem;">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body d-flex align-items-center gap-2 py-2">
                    <div class="icon-circle bg-warning text-white" style="width:32px;height:32px;font-size:1rem;"><i class='bx bx-id-card'></i></div>
                    <div>
                        <div class="stat-label" style="font-size:0.8rem;">Total Drivers</div>
                        <div class="stat-value" id="statDrivers" style="font-size:1.1rem;">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-2">
        <!-- Live Map -->
        <div class="col-lg-7">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body p-0">
                    <div id="map" style="height:320px;border-radius:0.5rem;"></div>
                </div>
            </div>
        </div>
        <!-- Association Dropdown + Screentime Graph -->
        <div class="col-lg-5">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body py-2">
                    <label for="associationSelect" class="form-label" style="font-size:0.85rem;">Association Name</label>
                    <div class="d-flex gap-2 mb-2">
                        <select id="associationSelect" class="form-select form-select-sm">
                            <option value="">Select Association</option>
                        </select>
                        <select id="timeRangeSelect" class="form-select form-select-sm" style="max-width:130px;">
                            <option value="this_week">This Week</option>
                            <option value="week">Past Week</option>
                            <option value="month">Past Month</option>
                            <option value="year">Past Year</option>
                        </select>
                    </div>
                    <canvas id="screenTimeChart" height="90"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Sidebar logic
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarOpen = document.getElementById('sidebarOpen');
const sidebarClose = document.getElementById('sidebarClose');
const logoutBtn = document.getElementById('logoutBtn');

// Open sidebar
if (sidebarOpen) {
    sidebarOpen.onclick = function () {
        if (sidebar) sidebar.classList.add('open');
        if (sidebarOverlay) sidebarOverlay.style.display = 'block';
    };
}
// Close sidebar
if (sidebarClose) {
    sidebarClose.onclick = function () {
        if (sidebar) sidebar.classList.remove('open');
        if (sidebarOverlay) sidebarOverlay.style.display = 'none';
    };
}
// Close sidebar when clicking on the overlay
if (sidebarOverlay) {
    sidebarOverlay.onclick = function () {
        if (sidebar) sidebar.classList.remove('open');
        sidebarOverlay.style.display = 'none';
    };
}
// Logout functionality (placeholder)
if (logoutBtn) {
    logoutBtn.onclick = function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'login.php';
        }
    };
}

// Populate associations dropdown
fetch('../get_associations.php')
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('associationSelect');
        select.innerHTML = '<option value="">Select Association</option>';
        data.forEach(assoc => {
            const opt = document.createElement('option');
            opt.value = assoc.id;
            opt.textContent = assoc.name;
            select.appendChild(opt);
        });
        // If an association is already selected (e.g., after reload), trigger the graph
        if (select.value) {
            loadScreenTime();
        }
    });
// Fetch stats
function loadStats() {
    fetch('get_dashboard_stats.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('statAssociations').textContent = data.associations;
                document.getElementById('statDrivers').textContent = data.drivers;
                document.getElementById('statUsers').textContent = data.passengers;
            } else {
                console.error('Failed to load stats:', data.message);
            }
        })
        .catch(err => {
            console.error('Error loading stats:', err);
        });
}

// Load stats on page load and refresh every 30 seconds
loadStats();
setInterval(loadStats, 30000);

// Fetch most visited places
function loadMostVisited() {
    fetch('get_most_visited.php')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('mostVisitedPlaces');
            container.innerHTML = '';
            data.forEach(place => {
                const div = document.createElement('div');
                div.className = 'mb-3';
                div.innerHTML = `
                    <div class="d-flex align-items-center mb-1">
                        <span class="icon-circle icon-dropoff me-2"><i class='bx bx-map-pin'></i></span>
                        <span class="flex-grow-1 fw-semibold">${place.name}</span>
                        <span class="badge bg-primary ms-2">${place.percent}%</span>
                    </div>
                    <div class="progress" style="height: 18px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: ${place.percent}%; transition: width 1s;" aria-valuenow="${place.percent}" aria-valuemin="0" aria-valuemax="100">${place.cnt ? place.cnt : ''}</div>
                    </div>
                `;
                container.appendChild(div);
            });
        });
}
loadMostVisited();
// Chart.js for screentime
let screenTimeChart;
const assocSelect = document.getElementById('associationSelect');
const timeRangeSelect = document.getElementById('timeRangeSelect');
let screenTimeInterval = null;

function startScreenTimeAutoRefresh() {
    if (screenTimeInterval) clearInterval(screenTimeInterval);
    screenTimeInterval = setInterval(loadScreenTime, 10000); // every 10 seconds
}

function stopScreenTimeAutoRefresh() {
    if (screenTimeInterval) clearInterval(screenTimeInterval);
}

function loadScreenTime() {
    const assocId = assocSelect.value;
    const range = timeRangeSelect.value;
    if (!assocId) {
        if (screenTimeChart) screenTimeChart.destroy();
        return;
    }
    
    fetch('get_screentime.php?association_id=' + assocId + '&range=' + range)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to load screentime:', data.message);
                return;
            }
            
            const ctx = document.getElementById('screenTimeChart').getContext('2d');
            if (screenTimeChart) screenTimeChart.destroy();
            
            screenTimeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Driver Screentime (min)',
                        data: data.values,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Minutes'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Drivers'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Error loading screentime:', err);
        });
}

// Event listeners for screentime updates
assocSelect.addEventListener('change', function() {
    loadScreenTime();
    if (assocSelect.value) {
        startScreenTimeAutoRefresh();
    } else {
        stopScreenTimeAutoRefresh();
    }
});

timeRangeSelect.addEventListener('change', loadScreenTime);

// Start auto-refresh if an association is already selected on page load
if (assocSelect.value) {
    startScreenTimeAutoRefresh();
}
// Leaflet map for live locations
const map = L.map('map').setView([13.222, 120.667], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(map);
let driverMarkers = [], passengerMarkers = [];
function loadLiveLocations() {
    fetch('get_live_locations.php')
        .then(res => res.json())
        .then(data => {
            driverMarkers.forEach(m => map.removeLayer(m));
            passengerMarkers.forEach(m => map.removeLayer(m));
            driverMarkers = data.drivers.map(loc => L.marker([loc.lat, loc.lng], {icon: L.divIcon({className:'', html:`<i class='bx bxs-map-pin' style='color:red;font-size:2rem;'></i>`})}).addTo(map));
            passengerMarkers = data.passengers.map(loc => L.marker([loc.lat, loc.lng], {icon: L.divIcon({className:'', html:`<i class='bx bxs-map-pin' style='color:blue;font-size:2rem;'></i>`})}).addTo(map));
        });
}
loadLiveLocations();
setInterval(loadLiveLocations, 10000); // refresh every 10s
// Notifications logic
const notifBellBtn = document.getElementById('notifBellBtn');
const notifDropdown = document.getElementById('notifDropdown');
const notifBadge = document.getElementById('notifBadge');
const notifList = document.getElementById('notifList');
const markAllNotifReadBtn = document.getElementById('markAllNotifReadBtn');

let notifDropdownOpen = false;

notifBellBtn.onclick = function(e) {
    e.stopPropagation();
    notifDropdown.classList.toggle('show');
    notifDropdownOpen = !notifDropdownOpen;
};

document.addEventListener('click', function(e) {
    if (notifDropdownOpen && !notifDropdown.contains(e.target) && e.target !== notifBellBtn) {
        notifDropdown.classList.remove('show');
        notifDropdownOpen = false;
    }
});

function fetchAdminNotifications() {
    console.log('Fetching notifications...');
    fetch('get_admin_notifications.php')
        .then(res => {
            console.log('Response status:', res.status);
            return res.json();
        })
        .then(data => {
            console.log('Received data:', data);
            
            if (!data.success) {
                console.error('Failed to load notifications:', data.message);
                return;
            }

            // Update badge
            const notifBadge = document.getElementById('notifBadge');
            if (notifBadge) {
                console.log('Updating badge with count:', data.unread_count);
                if (data.unread_count > 0) {
                    notifBadge.textContent = data.unread_count;
                    notifBadge.style.display = 'inline-block';
                } else {
                    notifBadge.style.display = 'none';
                }
            }

            // Render notifications
            const notifList = document.getElementById('notifList');
            if (!notifList) {
                console.error('Notification list element not found');
                return;
            }

            console.log('Rendering notifications:', data.notifications);
            notifList.innerHTML = '';
            
            if (!data.notifications || data.notifications.length === 0) {
                notifList.innerHTML = `
                    <div class='dropdown-item text-center py-3'>
                        <i class="bx bx-bell-off text-muted mb-2" style="font-size:2rem;"></i>
                        <p class="mb-0">No notifications</p>
                    </div>`;
            } else {
                data.notifications.forEach(notif => {
                    console.log('Processing notification:', notif);
                    const item = document.createElement('div');
                    item.className = `dropdown-item notification-item ${notif.status === 'unread' ? 'bg-light' : ''}`;
                    item.style.cursor = 'pointer';
                    
                    // Format the date
                    const date = new Date(notif.created_at);
                    const formattedDate = date.toLocaleString();
                    
                    // Create notification content
                    item.innerHTML = `
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class='bx ${getNotificationIcon(notif.type)} text-${getNotificationColor(notif.type)}' style='font-size:1.7rem;'></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">${notif.type || 'Notification'}</h6>
                                    <small class="text-muted">${formattedDate}</small>
                                </div>
                                <p class="mb-0 text-muted">${notif.message}</p>
                                ${getNotificationDetails(notif)}
                            </div>
                        </div>
                    `;

                    // Add click handler
                    item.onclick = function() {
                        if (notif.booking_id) {
                            window.location.href = `booking_details.php?id=${notif.booking_id}`;
                        }
                    };

                    notifList.appendChild(item);
                    notifList.appendChild(document.createElement('hr'));
                });
            }
        })
        .catch(err => {
            console.error('Error loading notifications:', err);
        });
}

// Helper function to get notification icon
function getNotificationIcon(type) {
    switch (type?.toLowerCase()) {
        case 'complaint':
            return 'bx-error-circle';
        case 'report':
            return 'bx-flag';
        case 'booking':
            return 'bx-calendar-check';
        default:
            return 'bx-bell';
    }
}

// Helper function to get notification color
function getNotificationColor(type) {
    switch (type?.toLowerCase()) {
        case 'complaint':
            return 'danger';
        case 'report':
            return 'warning';
        case 'booking':
            return 'success';
        default:
            return 'primary';
    }
}

// Helper function to get notification details
function getNotificationDetails(notif) {
    if (!notif.booking_details) return '';
    
    return `
        <div class="mt-2 small">
            ${notif.passenger_name ? `<div><strong>Passenger:</strong> ${notif.passenger_name}</div>` : ''}
            ${notif.driver_name ? `<div><strong>Driver:</strong> ${notif.driver_name}</div>` : ''}
            ${notif.booking_details.pickup !== 'N/A' ? `<div><strong>Pickup:</strong> ${notif.booking_details.pickup}</div>` : ''}
            ${notif.booking_details.destination !== 'N/A' ? `<div><strong>Destination:</strong> ${notif.booking_details.destination}</div>` : ''}
            ${notif.booking_details.status !== 'N/A' ? `<div><strong>Status:</strong> ${notif.booking_details.status}</div>` : ''}
        </div>
    `;
}

// Initialize notifications
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing notifications...');
    fetchAdminNotifications();
    setInterval(fetchAdminNotifications, 30000);
});

// Submenu toggle functionality
document.querySelectorAll('.sidebar-submenu .sidebar-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const submenu = this.closest('.sidebar-submenu');
        submenu.classList.toggle('active');
    });
});
</script>
</body>
</html>