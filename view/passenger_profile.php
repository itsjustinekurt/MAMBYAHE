<?php
session_start();
$page_title = 'Profile';
include('sidebar.php');
?>

<!-- Header -->
<div class="header">
    <div class="header-left">
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
    left: 0;
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

<?php
// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM passenger WHERE passenger_id = :id");
$stmt->execute(['id' => $passenger_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profilePic = $user['profile_pic'];

    // Profile picture handling
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('profile_', true) . '.' . $fileExtension;
            $uploadPath = 'uploads/' . $newFileName;

            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $profilePic = $newFileName;
            }
        }
    }

    $fullname = htmlspecialchars($_POST['fullname']);
    $username = htmlspecialchars($_POST['username']);
    $contact = htmlspecialchars($_POST['contact']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $dob = $_POST['birthday'];
    $nationality = htmlspecialchars($_POST['nationality']);
    $gender = htmlspecialchars($_POST['gender']);
    $address = htmlspecialchars($_POST['address'] ?? '');

    $stmt = $pdo->prepare("UPDATE passenger SET fullname = ?, username = ?, address = ?, phone = ?, dob = ?, nationality = ?, gender = ?, email = ?, profile_pic = ? WHERE passenger_id = ?");
    $stmt->execute([
        $fullname, $username, $address, $contact, $dob,
        $nationality, $gender, $email, $profilePic, $passenger_id
    ]);

    header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
    exit;
}

// Fetch average rating and reviews for this passenger
$avg_rating = 'No Rating Available';
$reviews = [];
try {
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM passenger_reviews WHERE passenger_id = ?");
    $stmt->execute([$passenger_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['avg_rating'] !== null) {
        $avg_rating = round($row['avg_rating'], 1);
    }
    $stmt = $pdo->prepare("SELECT r.rating, r.review, r.created_at, d.fullname as driver_name FROM passenger_reviews r JOIN driver d ON r.driver_id = d.driver_id WHERE r.passenger_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$passenger_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $avg_rating = 'No Rating Available';
    $reviews = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile</title>
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

    .profile-container {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      max-width: 700px;
      margin: auto;
    }

    .profile-header {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile-header img {
      border-radius: 50%;
      width: 100px;
      height: 100px;
      object-fit: cover;
      border: 2px solid #007bff;
    }

    .star-rating {
      font-size: 1.5rem;
      color: gold;
      margin-top: 10px;
    }

    .reviews-container {
      margin-top: 30px;
    }

    .review-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .review-date {
      font-size: 0.9rem;
      color: #666;
    }

    .review-rating {
      color: gold;
    }

    .review-content {
      margin-top: 10px;
      line-height: 1.6;
    }

    .update-toast {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      background: #28a745;
      color: white;
      padding: 15px 25px;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      display: none;
    }

    .update-toast.show {
      display: block;
      animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  </style>
</head>
<body>

<div class="main-content">
  <div class="container">
    <div class="profile-container">
      <div class="profile-header">
        <?php if (!empty($user['profile_pic'])): ?>
          <img src="uploads/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture">
        <?php else: ?>
          <img src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/user-circle.svg" alt="Default Avatar">
        <?php endif; ?>
        <h2><?= htmlspecialchars($user['fullname']) ?></h2>
        <p class="text-muted"><?= htmlspecialchars($user['username']) ?></p>
        <div class="star-rating">
          <?php if (is_numeric($avg_rating)): ?>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="fas fa-star"></i>
            <?php endfor; ?>
            <span class="ms-2"><?= $avg_rating ?></span>
          <?php else: ?>
            <span>No Rating Available</span>
          <?php endif; ?>
        </div>
      </div>

      <form method="POST" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="fullname" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="contact" class="form-label">Contact Number</label>
            <input type="tel" class="form-control" id="contact" name="contact" value="<?= htmlspecialchars($user['phone']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="birthday" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="birthday" name="birthday" value="<?= htmlspecialchars($user['dob']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="nationality" class="form-label">Nationality</label>
            <input type="text" class="form-control" id="nationality" name="nationality" value="<?= htmlspecialchars($user['nationality']) ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select class="form-select" id="gender" name="gender" required>
              <option value="" disabled <?= empty($user['gender']) ? 'selected' : ''; ?>>Select Gender</option>
              <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
              <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address']) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label for="profile_pic" class="form-label">Profile Picture</label>
          <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
      </form>

      <!-- Reviews Section -->
      <div class="reviews-container">
        <h3 class="mb-4">Reviews</h3>
        <?php if (empty($reviews)): ?>
          <p class="text-muted">No reviews yet.</p>
        <?php else: ?>
          <?php foreach ($reviews as $review): ?>
            <div class="review-card">
              <div class="review-header">
                <div>
                  <h5 class="mb-0"><?= htmlspecialchars($review['driver_name']) ?></h5>
                  <span class="review-date"><?= date('F j, Y', strtotime($review['created_at'])) ?></span>
                </div>
                <div class="review-rating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star"></i>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="review-content">
                <?= nl2br(htmlspecialchars($review['review'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Success Toast -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 11;">
  <div id="updateToast" class="toast align-items-center text-white bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        Profile updated successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show toast if profile was updated
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('updated')) {
  const toast = new bootstrap.Toast(document.getElementById('updateToast'));
  toast.show();
}
</script>

<?php include __DIR__.'/tab_session_protect.php'; ?>
</body>
</html>
