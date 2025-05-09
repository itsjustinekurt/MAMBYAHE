<?php
// Get driver information if not already available
if (!isset($driver)) {
    try {
        // First, let's get the basic driver info
        $stmt = $pdo->prepare("
            SELECT * FROM driver WHERE driver_id = :driver_id
        ");
        $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        // Then get the additional stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT b.id) as total_rides,
                COALESCE(AVG(r.rating), 0) as average_rating,
                COUNT(DISTINCT r.id) as total_reviews
            FROM driver d
            LEFT JOIN bookings b ON d.driver_id = b.driver_id AND b.status = 'completed'
            LEFT JOIN driver_reviews r ON d.driver_id = r.driver_id
            WHERE d.driver_id = :driver_id
            GROUP BY d.driver_id
        ");
        $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Merge the stats with driver info
        if ($stats) {
            $driver = array_merge($driver, $stats);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Debug output
error_log("Driver data: " . print_r($driver, true));

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check for active arrival state
$has_active_arrival = isset($_SESSION['arrival_state']) && 
                     time() - $_SESSION['arrival_state']['timestamp'] < 3600;
?>

<!-- Sidebar Toggle Button -->
<button class="btn btn-link sidebar-toggle" type="button" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="text-center">
            <img src="<?php echo !empty($driver['profile_pic']) ? './uploads/driver_ids/' . $driver['profile_pic'] : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'; ?>" 
                 alt="Driver" 
                 class="sidebar-profile-pic mb-2"
                 onerror="this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg'">
            <h5 class="mb-1"><?php echo htmlspecialchars($driver['fullname'] ?? ''); ?></h5>
            <p class="text-muted mb-1 small">@<?php echo htmlspecialchars($driver['username'] ?? ''); ?></p>
            <?php if (!empty($driver['plate_number'])): ?>
            <p class="text-muted mb-0 small">
                <i class="fas fa-car me-1"></i>
                <?php echo htmlspecialchars($driver['plate_number']); ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboardDriver.php" class="sidebar-link <?php echo $current_page === 'dashboardDriver.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="driver_profile.php" class="sidebar-link <?php echo $current_page === 'driver_profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="driver_history.php" class="sidebar-link <?php echo $current_page === 'driver_history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Ride History</span>
            </a>
        </li>
        <li>
            <a href="logout.php" class="sidebar-link text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<?php if ($has_active_arrival): ?>
<script>
// Function to initialize arrival state
function initializeArrivalState() {
    const rideSheet = document.getElementById('rideSheet');
    if (rideSheet) {
        // Set the booking and passenger IDs
        rideSheet.dataset.bookingId = '<?php echo $_SESSION['arrival_state']['booking_id']; ?>';
        rideSheet.dataset.passengerId = '<?php echo $_SESSION['arrival_state']['passenger_id']; ?>';
        
        // Fetch and display passenger info
        fetch('get_passenger_info.php?passenger_id=<?php echo $_SESSION['arrival_state']['passenger_id']; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('sheetPassengerName').textContent = data.passenger_name;
                    document.getElementById('sheetPassengerImg').src = data.profile_pic || 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
                    document.getElementById('sheetPickup').textContent = data.pickup_location;
                    document.getElementById('sheetDropoff').textContent = data.destination;
                    document.getElementById('sheetCallBtn').href = 'tel:' + data.phone;
                    
                    // Show the bottom sheet
                    rideSheet.classList.add('show');
                    
                    // Start updating passenger location
                    updatePassengerLocation('<?php echo $_SESSION['arrival_state']['booking_id']; ?>');
                }
            })
            .catch(error => console.error('Error fetching passenger info:', error));
    }
}

// Initialize arrival state when the page loads
document.addEventListener('DOMContentLoaded', initializeArrivalState);
</script>
<?php endif; ?>

<style>
    .sidebar-toggle {
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1000;
        color: #198754;
        font-size: 1.5rem;
        background: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border: none;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        width: 280px;
        height: 100vh;
        background: white;
        z-index: 1001;
        transition: all 0.3s ease;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

    .sidebar.active {
        left: 0;
    }

    .sidebar-header {
        padding: 2rem 1rem;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    .sidebar-profile-pic {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #198754;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 1rem;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .sidebar-link:hover {
        background: #f8f9fa;
        color: #198754;
    }

    .sidebar-link.active {
        background: #198754;
        color: white;
    }

    .sidebar-link i {
        width: 24px;
        margin-right: 10px;
    }

    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        display: none;
    }

    .sidebar-overlay.active {
        display: block;
    }

    /* Adjust main content when sidebar is active */
    .container {
        transition: all 0.3s ease;
    }

    .sidebar.active + .container {
        margin-left: 280px;
    }
</style>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}
</script> 