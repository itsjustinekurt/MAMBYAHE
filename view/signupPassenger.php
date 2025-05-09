<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Passenger Registration</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/boxicons.min.css" />
  <link href="https://unpkg.com/boxicons/css/boxicons.min.css" rel="stylesheet">
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .image-half { height: 50%; overflow: hidden; }
    .image-half img { object-fit: cover; height: 25vh; width: 100%; }
    .login-button {
      background: linear-gradient(135deg, #0a5c38, #1fb475);
      color: white; border: none; padding: 10px 40px;
      border-radius: 35px; cursor: pointer; width: 50%;
      display: block; margin: 0 auto; text-align: center;
    }
    .login-button:hover { background: linear-gradient(135deg, #1fb475, #0a5c38); }
  </style>
</head>
<body>

<section style="background-color: #8fc4b7;">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card rounded-3">
          <div class="image-half">
          <a href="javascript:history.back()" class="position-absolute top-0 start-0 ms-3 mt-3">
            <i class="bx bx-arrow-back" style="font-size: 2rem; color: #ffffff; cursor: pointer;"></i>
          </a>
            <img src="received_1882822929171036.jpeg" alt="Banner">
          </div>
          <div class="card-body p-5">
            <h3 class="mb-4"><center>Passenger Registration</center></h3>
            <form id="registrationForm">
              <input type="text" name="fullname" placeholder="Full Name" class="form-control mb-3" required>
              <input type="text" name="username" placeholder="Username" class="form-control mb-3" required>
              <input type="date" name="dob" class="form-control mb-3" required>

              <select name="gender" class="form-select mb-3" required>
                <option disabled selected>Gender</option>
                <option>Male</option><option>Female</option><option>Other</option>
              </select>

              <select name="nationality" class="form-select mb-3" required>
                <option disabled selected>Nationality</option>
                <option>Filipino</option><option>American</option><option>Other</option>
              </select>

              <input type="text" name="address" placeholder="Address" class="form-control mb-3" required>
              <input type="text" name="phone" placeholder="Phone Number" class="form-control mb-3" required>
              <input type="email" name="email" placeholder="Email" class="form-control mb-3" id="email" required>
              <input type="text" name="gov_id" placeholder="Government ID" class="form-control mb-3" required>

              <select name="id_type" class="form-select mb-3" required>
                <option disabled selected>ID Type</option>
                <option>SSS</option><option>Passport</option><option>Driver's License</option>
              </select>

              <input type="password" name="password" placeholder="Password" class="form-control mb-3" required>
              <button type="submit" class="login-button">Submit</button>
              <div class="d-flex align-items-center justify-content-center pb-4">
                      <p class="mb-0 me-2">Don't have an account?</p>
                      <a href="login.php">Log In</a>
                    </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="otpForm" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Enter OTP</h5></div>
      <div class="modal-body">
        <input type="text" id="otpInput" class="form-control" placeholder="Enter OTP" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Confirm</button>
      </div>
    </form>
  </div>
</div>

<script>
  let correctOTP = "";

  document.getElementById("registrationForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const email = formData.get("email");

    window.userFormData = Object.fromEntries(formData.entries());

    try {
      await fetch("store_email.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });

      const otpResponse = await fetch("send_otp.php");
      const otpData = await otpResponse.json();

      if (otpData.status && otpData.status.toLowerCase() === "success") {
        correctOTP = otpData.otp.toString();
        new bootstrap.Modal(document.getElementById("otpModal")).show();
      } else {
        Swal.fire("Error", "Failed to send OTP: " + (otpData.error || "Unknown error"), "error");
      }
    } catch (err) {
      Swal.fire("Error", "Something went wrong: " + err.message, "error");
    }
  });

  document.getElementById("otpForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const enteredOtp = document.getElementById("otpInput").value.trim();

    if (enteredOtp === correctOTP) {
      try {
        const saveResponse = await fetch("save_passenger.php", {
          method: "POST",
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(window.userFormData)
        });

        const result = await saveResponse.json();
        console.log("Response from save_passenger.php:", result);

        if (result && result.status && result.status.toLowerCase() === "success") {
          Swal.fire("Success", "Registered successfully!", "success")
            .then(() => window.location.href = "login.php");
        } else {
          Swal.fire("Error", "Failed to save user: " + (result.error || JSON.stringify(result)), "error");
        }
      } catch (err) {
        Swal.fire("Error", "Unexpected error: " + err.message, "error");
      }
    } else {
      Swal.fire("Error", "Incorrect OTP", "error");
    }
  });
</script>


</body>
</html>