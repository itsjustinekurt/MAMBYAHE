<?php
require_once '../db_connect.php';

// Get total passengers and drivers count
$passengers_count = 0;
$drivers_count = 0;

try {
    // Get total passengers
    $result = $conn->query("SELECT COUNT(*) as count FROM passenger");
    if ($row = $result->fetch_assoc()) {
        $passengers_count = $row['count'];
    }

    // Get total drivers
    $result = $conn->query("SELECT COUNT(*) as count FROM driver");
    if ($row = $result->fetch_assoc()) {
        $drivers_count = $row['count'];
    }
} catch (Exception $e) {
    error_log("Error fetching stats: " . $e->getMessage());
}
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
    <link rel="stylesheet" href="styles.css">
    <style>
        #map {
            height: 400px;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            margin-top: 1rem;
        }

        .chart-container {
            height: 300px;
        }

        .association-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background-color: #f8fafc;
        }

        .association-card:hover {
            background-color: #e2e8f0;
        }

        .association-icon {
            width: 40px;
            height: 40px;
            background-color: #0ea5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }

        .toda-screen-time {
            margin-bottom: 2rem;
        }

        .sidebar-link i {
            font-size: 1.2rem;
            transition: transform 0.2s ease;
        }

        .sidebar-link:hover i {
            transform: translateX(3px);
        }

        .sidebar-close {
            display: none !important;
        }

        /* Submenu styles */
        .sidebar-submenu {
            position: relative;
        }

        .submenu-items {
            display: none;
            flex-direction: column;
            padding-left: 2.5rem;
            margin-top: 0.5rem;
        }

        .sidebar-submenu.active .submenu-items {
            display: flex;
        }

        .submenu-link {
            font-size: 0.95rem;
            padding: 0.6rem 1rem;
            color: rgba(255,255,255,0.7);
            transition: all 0.2s ease;
        }

        .submenu-link:hover,
        .submenu-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .sidebar-submenu .sidebar-link i.bx-chevron-down {
            transition: transform 0.3s ease;
        }

        .sidebar-submenu.active .sidebar-link i.bx-chevron-down {
            transform: rotate(180deg);
        }

        .badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3em 0.7em;
            border-radius: 1em;
            margin-left: 0.5em;
            vertical-align: middle;
            background: rgba(255,255,255,0.1);
            color: white;
            transition: transform 0.2s ease;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        .badge.new {
            background: linear-gradient(135deg, #1de9b6, #10b981);
            color: white;
        }

        .badge.hot {
            background: linear-gradient(135deg, #ff1744, #dc2626);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay {
                display: block;
            }

            .sidebar-header {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1.5rem 1rem;
            }

            .sidebar-logo {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                color: var(--primary-color);
            }

            .sidebar-title {
                font-weight: 700;
                font-size: 1.2rem;
                color: white;
            }

            .sidebar-subtitle {
                font-size: 0.9rem;
                color: rgba(255,255,255,0.7);
                font-weight: 500;
            }

            .sidebar-nav {
                margin-top: 1.5rem;
            }

            .sidebar-link {
                padding: 0.7rem 1.2rem;
            }

            @media (max-width: 600px) {
                .sidebar {
                    width: 90vw;
                }
            }
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            z-index: 1000;
            display: none;
            width: 350px;
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: var(--background-color);
        }

        .notification-item .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            font-size: 1.2rem;
        }

        .notification-item .notification-content {
            flex: 1;
        }

        #notifBadge {
            font-size: 0.75rem;
            padding: 0.3em 0.7em;
            background: var(--primary-color);
            color: white;
            border-radius: 1em;
        }

        /* Topbar */
        .navbar {
            background: white;
            box-shadow: var(--shadow-sm);
            padding: 0.75rem 1.5rem;
            position: fixed;
            width: 100%;
            z-index: 100;
            height: 60px;
            top: 0;
            left: 0;
            right: 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary-color) !important;
        }

        .notification-container {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 101;
        }

        /* Content Area */
        .content-area {
            margin-left: 260px;
            padding-top: 60px;
            min-height: 100vh;
            background: var(--background-color);
        }

        /* Charts */
        .chart-container {
            position: relative;
            margin: 1rem 0;
            padding: 1rem;
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        /* Loading States */
        .loading {
            position: relative;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            border: 4px solid var(--border-color);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<!-- Sticky Topbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <span class="navbar-brand fw-bold text-uppercase">MTFRB</span>
        <div class="notification-container">
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
    </div>
</nav>
<div class="content-area">
    <!-- Stat Cards -->
    <div class="row g-2 pt-2">
        <div class="col-md-4">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body d-flex align-items-center gap-2 py-2">
                    <div class="icon-circle bg-primary text-white" style="width:32px;height:32px;font-size:1rem;"><i class='bx bx-user'></i></div>
                    <div>
                        <div class="stat-label" style="font-size:0.8rem;">Total Passengers</div>
                        <div class="stat-value" id="statUsers" style="font-size:1.1rem;">
                            <?php echo number_format($passengers_count); ?>
                        </div>
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
                        <div class="stat-value" id="statDrivers" style="font-size:1.1rem;">
                            <?php echo number_format($drivers_count); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Map Section -->
    <div class="row g-2 pt-2">
        <div class="col-12">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Live Driver Locations</h5>
                        <button class="btn btn-outline-primary btn-sm" id="refreshMapBtn">
                            <i class='bx bx-refresh'></i> Refresh
                        </button>
                    </div>
                    <div id="mapLoading" class="loading" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000;"></div>
                    <div id="liveMap" style="height: 400px; width: 100%; position: relative; z-index: 1;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TODA Screen Time Section -->
    <div class="row g-2 pt-2">
        <div class="col-lg-12 mb-4">
            <div class="card border-0 mb-2" style="background:#fcfcfd;border-radius:0.7rem;box-shadow:0 1px 4px rgba(44,62,80,0.04);">
                <div class="card-body p-3">
                    <h5 class="mb-3">TODA Screen Time</h5>
                    <div class="d-flex align-items-center mb-3">
                        <select id="todaSelect" class="form-select form-select-sm me-2" style="width: 200px;">
                            <option value="">Select TODA</option>
                            <?php
                            // Get all associations with their TODA names
                            $sql = "SELECT DISTINCT name as toda_name FROM associations WHERE name IS NOT NULL ORDER BY name";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($row['toda_name']) . "'>" . htmlspecialchars($row['toda_name']) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="changeTimeRange('today')">Today</button>
                            <button type="button" class="btn btn-outline-primary" onclick="changeTimeRange('week')">Week</button>
                            <button type="button" class="btn btn-outline-primary" onclick="changeTimeRange('month')">Month</button>
                        </div>
                    </div>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="todaScreenTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
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

// Initialize TODA screen time loading
document.addEventListener('DOMContentLoaded', () => {
    const todaSelect = document.getElementById('todaSelect');
    if (todaSelect) {
        todaSelect.addEventListener('change', (e) => {
            if (e.target.value) {
                loadTodaScreenTime(e.target.value, 'today');
            } else if (todaScreenTimeChart) {
                todaScreenTimeChart.destroy();
            }
        });
    }
});

// Function to change time range
function changeTimeRange(range) {
    const todaSelect = document.getElementById('todaSelect');
    const selectedToda = todaSelect.value;
    if (!selectedToda) {
        alert('Please select a TODA first');
        return;
    }
    loadTodaScreenTime(selectedToda, range);
}

// Function to load TODA screen time
function loadTodaScreenTime(todaName, range) {
    fetch(`get_toda_screen_time.php?todaName=${encodeURIComponent(todaName)}&range=${range}`)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (todaScreenTimeChart) todaScreenTimeChart.destroy();
            
            const ctx = document.getElementById('todaScreenTimeChart').getContext('2d');
            todaScreenTimeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Screen Time (hours)',
                        data: data.values,
                        backgroundColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: `Screen Time for ${todaName}`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + 'h';
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Error loading TODA screen time:', err);
            alert('Error loading screen time data. Please try again.');
        });
}

// Leaflet map for live locations
let liveMap = null;
let driverMarkers = new Map();

function initializeLiveMap() {
    // Initialize the map
    liveMap = L.map('liveMap').setView([14.5995, 120.9842], 13); // Default to Metro Manila
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: ' OpenStreetMap contributors'
    }).addTo(liveMap);

    // Add a marker for the map center
    L.marker([14.5995, 120.9842]).addTo(liveMap)
        .bindPopup('Metro Manila Area')
        .openPopup();

    // Initialize the refresh button
    document.getElementById('refreshMapBtn').addEventListener('click', () => {
        updateDriverLocations(true);
    });
}

function updateDriverLocations(forceUpdate = false) {
    // Show loading indicator
    document.getElementById('mapLoading').style.display = 'block';
    
    fetch('get_live_driver_locations.php')
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch driver locations');
            }

            // Clear existing markers
            driverMarkers.forEach(marker => {
                liveMap.removeLayer(marker);
            });
            driverMarkers.clear();

            // Add new markers
            data.drivers.forEach(driver => {
                const { driver_id, latitude, longitude, fullname, last_updated } = driver;
                const position = [latitude, longitude];

                const marker = L.marker(position)
                    .bindPopup(`
                        <div class="p-2">
                            <h6 class="mb-1">${fullname}</h6>
                            <p class="mb-0 small text-muted">Last updated: ${new Date(last_updated).toLocaleString()}</p>
                        </div>
                    `);

                marker.addTo(liveMap);
                driverMarkers.set(driver_id, marker);
            });

            // Fit map to show all markers
            if (driverMarkers.size > 0) {
                const bounds = L.latLngBounds(
                    driverMarkers.values().map(marker => marker.getLatLng())
                );
                liveMap.fitBounds(bounds, { padding: [50, 50] });
            }

            // Hide loading indicator
            document.getElementById('mapLoading').style.display = 'none';

            // Schedule next update if not forced
            if (!forceUpdate) {
                setTimeout(updateDriverLocations, 5000); // Update every 5 seconds
            }
        })
        .catch(err => {
            console.error('Error updating driver locations:', err);
            // Show error message on map
            const errorMarker = L.marker([14.5995, 120.9842])
                .bindPopup(`Error: ${err.message}`);
            errorMarker.addTo(liveMap);
            document.getElementById('mapLoading').style.display = 'none';
        });
}

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
            } catch (parseError) {
                console.error('Failed to parse JSON:', parseError);
                console.error('Raw response:', responseText);
                throw parseError;
            }
        })
        .catch(err => {
            console.error('Error loading notifications:', err);
            // Show error message to user
            const notifList = document.getElementById('notifList');
            notifList.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load notifications. Please try again later.
                </div>
            `;
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
    
    // Initialize live map
    setTimeout(initializeLiveMap, 1000); // Delay initialization to ensure DOM is ready
});

// Submenu toggle functionality
document.querySelectorAll('.sidebar-submenu .sidebar-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const submenu = this.closest('.sidebar-submenu');
        submenu.classList.toggle('active');
    });
});
</body>
</html>