<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['passenger_id'])) {
    header('Location: ../login.php');
    exit();
}

try {
    // Get passenger information
    $stmt = $pdo->prepare("
        SELECT * FROM passenger 
        WHERE passenger_id = :passenger_id
    ");
    
    $stmt->execute(['passenger_id' => $_SESSION['passenger_id']]);
    $passenger = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$passenger) {
        throw new Exception('Passenger not found');
    }

} catch (Exception $e) {
    error_log("Error in profile.php: " . $e->getMessage());
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MAMBYAHE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">My Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php else: ?>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <img src="<?php echo $passenger['profile_pic'] ?? 'https://via.placeholder.com/150'; ?>" 
                                         class="img-fluid rounded-circle" alt="Profile Picture">
                                </div>
                                <div class="col-md-8">
                                    <h5><?php echo htmlspecialchars($passenger['fullname']); ?></h5>
                                    <p class="text-muted">@<?php echo htmlspecialchars($passenger['username']); ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($passenger['email']); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($passenger['phone']); ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Address</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($passenger['address']); ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date Joined</label>
                                    <p class="form-control-static">
                                        <?php echo date('F j, Y', strtotime($passenger['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <p class="form-control-static">
                                        <span class="badge bg-success">Active</span>
                                    </p>
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="edit_profile.php" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 