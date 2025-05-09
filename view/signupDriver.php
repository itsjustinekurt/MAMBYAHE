<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Driver Account Request</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link href="https://unpkg.com/boxicons/css/boxicons.min.css" rel="stylesheet">
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .image-half img { object-fit: cover; height: 25vh; width: 100%; }
    .login-button {
      background: linear-gradient(135deg, #0a5c38, #1fb475);
      color: white; border: none; padding: 10px 40px;
      border-radius: 35px; width: 50%; display: block; margin: auto;
    }
    .login-button:hover { background: linear-gradient(135deg, #1fb475, #0a5c38); }
    .is-invalid { border: 2px solid red !important; }
  </style>
</head>
<body>
<section style="background-color: #8fc4b7;">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card rounded-3">
          <div class="image-half position-relative">
            <a href="javascript:history.back()" class="position-absolute top-0 start-0 ms-3 mt-3">
              <i class="bx bx-arrow-back" style="font-size: 2rem; color: #ffffff;"></i>
            </a>
            <img src="received_1882822929171036.jpeg" alt="Banner">
          </div>
          <div class="card-body p-5">
            <h3 class="mb-4 text-center">Driver Account Request</h3>
            <form id="registrationForm" enctype="multipart/form-data" method="POST" action="save_driver.php">
              <input type="text" name="fullname" placeholder="Full Name" class="form-control mb-3" required>
              <input type="number" name="phone" placeholder="Phone Number" class="form-control mb-3" required>
              <input type="text" name="username" placeholder="Username" class="form-control mb-3" required>
              
              <input type="password" name="password" id="password" placeholder="Password" class="form-control mb-3" required>
              <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" class="form-control mb-3" required>

              <select name="nationality" class="form-select mb-3" required>
                <option disabled selected>Nationality</option>
                <option>Filipino</option><option>American</option><option>Other</option>
              </select>
              <input type="date" name="dob" class="form-control mb-3" required>
              <input type="text" name="address" placeholder="Address" class="form-control mb-3" required>
              <input type="text" name="franchise_no" placeholder="Franchise No." class="form-control mb-3" required>
              <input type="text" name="or_no" placeholder="O.R. No." class="form-control mb-3" required>
              <input type="text" name="make" placeholder="Vehicle Make" class="form-control mb-3" required>
              <input type="text" name="motor_no" placeholder="Motor No. / Engine No." class="form-control mb-3" required>
              <input type="text" name="chassis_no" placeholder="Chassis No. / Serial No." class="form-control mb-3" required>
              <input type="text" name="plate_no" placeholder="Plate Number" class="form-control mb-3" required>

              <select name="gov_id_type" id="gov_id_type" class="form-select mb-3" required>
                <option disabled selected>Select Government ID Type</option>
                <option value="passport">Passport</option>
                <option value="driver's license">Driver's License</option>
                <option value="philhealth">PhilHealth</option>
                <option value="sss">SSS</option>
              </select>

              <label class="form-label">Upload Government-Issued ID Picture</label>
              <input type="file" name="gov_id_picture" id="gov_id_picture" class="form-control mb-3" accept="image/*" required>

              <!-- TODA/Association Dropdown -->
              <div class="mb-3">
                <label for="toda" class="form-label">Select Association (TODA)</label>
                <select name="toda" id="toda" class="form-select" required>
                  <option value="">-- Select Association --</option>
                </select>
              </div>
              <button type="submit" class="login-button">Submit</button>
              <div class="text-center pt-4">
                <p class="mb-0">Already have an account? <a href="login.php">Log In</a></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById('confirm_password').addEventListener('input', () => {
  const pass = document.getElementById('password');
  const confirm = document.getElementById('confirm_password');
  if (pass.value !== confirm.value) {
    pass.classList.add('is-invalid');
    confirm.classList.add('is-invalid');
  } else {
    pass.classList.remove('is-invalid');
    confirm.classList.remove('is-invalid');
  }
});

document.getElementById('gov_id_picture').addEventListener('change', function() {
  const idType = document.getElementById('gov_id_type').value.toLowerCase();
  const file = this.files[0];
  const formData = new FormData();
  formData.append("apikey", "your_ocr_space_api_key");
  formData.append("language", "eng");
  formData.append("isOverlayRequired", false);
  formData.append("file", file);

  fetch("https://api.ocr.space/parse/image", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const text = data?.ParsedResults?.[0]?.ParsedText?.toLowerCase() || "";
    if (!text.includes(idType)) {
      document.getElementById('gov_id_picture').classList.add('is-invalid');
      Swal.fire('Mismatch', `Uploaded ID does not appear to be a ${idType.toUpperCase()}.`, 'error');
    } else {
      document.getElementById('gov_id_picture').classList.remove('is-invalid');
    }
  })
  .catch(err => {
    console.error("OCR error:", err);
    Swal.fire('Error', 'Failed to validate ID picture.', 'error');
  });
});

// Populate TODA dropdown from associations
fetch('get_associations.php')
  .then(res => res.json())
  .then(data => {
    const todaSelect = document.getElementById('toda');
    todaSelect.innerHTML = '<option value="">-- Select Association --</option>';
    data.forEach(assoc => {
      const opt = document.createElement('option');
      opt.value = assoc.id;
      opt.textContent = assoc.name;
      todaSelect.appendChild(opt);
    });
  });
</script>
</body>
</html>
