<?php
session_start();

$host = 'localhost';
$db = 'user_auth';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Simulate user login session (replace this with actual login session ID)
$userId = $_SESSION['user_id'] ?? 1;

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM passenger WHERE passenger_id = :id");
$stmt->execute(['id' => $userId]);
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
        $nationality, $gender, $email, $profilePic, $userId
    ]);

    header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
    exit;
}

// Fetch average rating and reviews for this passenger
$passenger_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_SESSION['passenger_id']) ? (int)$_SESSION['passenger_id'] : 0);
$avg_rating = 'No Rating Available';
$reviews = [];
if ($passenger_id) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=user_auth", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons/css/boxicons.min.css" rel="stylesheet">
  <style>
    body {
      background: #f7f9fc;
    }
    .profile-container {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      margin-top: 25%;
      position: relative; /* Ensure the position for the back button */
    }
    .profile-header {
      text-align: center;
      margin-bottom: 20px;
      position: relative; /* Position context for the back button */
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
      cursor: pointer; /* Make it clickable */
    }
    .back-button {
      position: absolute;
      top: 0;
      left: 0;
      padding: 10px;
    }
  </style>
</head>
<body>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="profile-container" style="width: 700px; max-width: 90%;">    

    <!-- Back Button Inside the Profile Header -->
    <div class="back-button">
      <a href="#" onclick="window.location.href = sessionStorage.getItem('last_page') || 'dashboardPassenger.php'; return false;" class="text-decoration-none">
        <i class="bx bx-arrow-back" style="font-size: 2rem; color: black;"></i>
      </a>
    </div>

    <div class="profile-header">
      <?php if (!empty($user['profile_pic'])): ?>
        <img id="profilePicPreview" src="uploads/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture">
      <?php else: ?>
        <img id="profilePicPreview" src="https://via.placeholder.com/100x100?text=Avatar" alt="Default Avatar">
      <?php endif; ?>
      <h4 class="mt-2"><?= htmlspecialchars($user['username']) ?></h4>
      <div class="star-rating" id="rating" data-bs-toggle="modal" data-bs-target="#ratingModal" style="cursor:pointer;">
        <?php if ($avg_rating !== 'No Rating Available'): ?>
          <?= str_repeat('★', (int)round($avg_rating)) . str_repeat('☆', 5 - (int)round($avg_rating)) ?>
        <?php else: ?>
          <span class="text-primary">No Rating Available</span>
        <?php endif; ?>
      </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group mb-3">
        <label for="profile_pic">Upload Avatar</label>
        <input type="file" name="profile_pic" id="profile_pic" class="form-control" accept="image/*">
      </div>
      <div class="form-group mb-2">
        Full Name
        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
      </div>
      <div class="form-group mb-2">
        Username
        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
      </div>
      <div class="form-group mb-2">
        Phone Number
        <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
      </div>
      <div class="form-group mb-2">
        Email
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>
      <div class="form-group mb-2">
        Date of Birth
        <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($user['dob']) ?>" required>
      </div>
      <div class="form-group mb-2">
        Nationality
        <select name="nationality" class="form-control">
          <option <?= $user['nationality'] == 'Filipino' ? 'selected' : '' ?>>Filipino</option>
          <option <?= $user['nationality'] == 'American' ? 'selected' : '' ?>>American</option>
          <option <?= $user['nationality'] == 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>
      <div class="form-group mb-2">
        Gender
        <select name="gender" class="form-control">
          <option <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
          <option <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
          <option <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100 mt-3">Update Profile</button>
    </form>

  </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Driver Feedback & Reviews</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $review): ?>
            <div class="mb-3 border-bottom pb-2">
              <div class="d-flex align-items-center mb-1">
                <span class="me-2 text-warning"><?= str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5 - (int)$review['rating']) ?></span>
                <span class="small text-muted">by <?= htmlspecialchars($review['driver_name']) ?> &middot; <?= date('M d, Y', strtotime($review['created_at'])) ?></span>
              </div>
              <?php if ($review['review']): ?>
                <div class="small text-dark">"<?= htmlspecialchars($review['review']) ?>"</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-muted text-center">No reviews yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 11;">
  <div id="updateToast" class="toast align-items-center text-white bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        Profile updated successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Show toast if URL has ?updated=1
  const showToast = new URLSearchParams(window.location.search).get('updated') === '1';
  if (showToast) {
    const toastEl = document.getElementById('updateToast');
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    // Clean URL (remove the ?updated=1)
    history.replaceState(null, '', window.location.pathname);
  }
</script>

<?php include __DIR__.'/tab_session_protect.php'; ?>
</body>
</html>
