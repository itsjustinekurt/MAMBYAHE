<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['passenger_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$passenger_name = htmlspecialchars($_SESSION['username']);
$passenger_id = $_SESSION['passenger_id'];

// Database connection
$host = 'localhost';
$db = 'user_auth';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch user data
    $stmt = $pdo->prepare("SELECT profile_pic FROM passenger WHERE passenger_id = :id");
    $stmt->execute(['id' => $passenger_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set the profile picture if available, otherwise use default
    $profilePic = !empty($user['profile_pic']) ? 'uploads/' . $user['profile_pic'] : 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg';
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!-- Fixed Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h5 class="sidebar-title">Menu</h5>
    </div>
    <div class="sidebar-content">
        <div class="user-card text-center">
            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; margin-bottom: 10px;">
            <br>
            <strong><?php echo $passenger_name; ?></strong><br>
            <span class="text-success"><i class="fas fa-circle"></i> Online</span><br>
            <a href="passenger_profile.php" class="btn btn-sm btn-outline-primary mt-2">View Profile</a>
        </div>
        <ul class="list-unstyled">
            <li><a href="dashboardPassenger.php" class="nav-link d-block py-2"><i class="fas fa-home me-2"></i> Home</a></li>
            <li><a href="ride_details.php" class="nav-link d-block py-2"><i class="fas fa-car me-2"></i> Ride Details</a></li>
            <li><a href="support.php" class="nav-link d-block py-2"><i class="fas fa-headset me-2"></i> Contact Support</a></li>
            <li><a href="passenger_profile.php" class="nav-link d-block py-2"><i class="fas fa-user me-2"></i> Profile</a></li>
            <li><a href="payment.php" class="nav-link d-block py-2"><i class="fas fa-credit-card me-2"></i> Payment</a></li>
            <li><a href="history.php" class="nav-link d-block py-2"><i class="fas fa-history me-2"></i> History</a></li>
            <li>
                <a href="login.php" class="nav-link d-block py-2 text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <div class="mb-3">
                    <i class="fas fa-sign-out-alt fa-3x text-danger"></i>
                </div>
                <h4 class="mb-1">Come back soon!</h4>
                <p class="text-muted">Are you sure you want to logout?</p>
                <div class="d-grid gap-2">
                    <form method="POST">
                        <button type="submit" name="confirm_logout" class="btn btn-dark">Yes, Logout</button>
                    </form>
                    <button type="button" class="btn btn-link text-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// PHP logic to handle logout
if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
