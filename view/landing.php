<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>JourneyWave | Landing Page</title>


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background-color: #f2f8f6;
      font-family: 'Segoe UI', sans-serif;
    }

    .container {
      margin-top: 5rem;
    }

    .card-img-top {
      height: 200px;
      object-fit: cover;
    }

    .card {
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease-in-out;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .dropdown-menu button {
      width: 100%;
      text-align: left;
    }
  </style>
</head>
<body>

<div class="container text-center">
  <div class="row justify-content-center g-4">
    
    <div class="col-md-5">
      <div class="card">
        <img src="https://mdbcdn.b-cdn.net/img/new/standard/nature/184.webp" class="card-img-top" alt="Sign In Image"/>
        <div class="card-body">
          <h5 class="card-title">Sign In</h5>
          <p class="card-text">Choose how you'd like to sign in to your JourneyWave account.</p>
          <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              Sign In As
            </button>
            <ul class="dropdown-menu">
              <li><button class="dropdown-item" onclick="location.href='login.php'">Passenger</button></li>
              <li><button class="dropdown-item" onclick="location.href='loginDriver.php'">Driver</button></li>
              <li><button class="dropdown-item" onclick="location.href='loginChairman.php'">Chairman</button></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

  
    <div class="col-md-5">
      <div class="card">
        <img src="https://mdbcdn.b-cdn.net/img/new/standard/nature/185.webp" class="card-img-top" alt="Sign Up Image"/>
        <div class="card-body">
          <h5 class="card-title">Sign Up</h5>
          <p class="card-text">New to JourneyWave? Create an account by selecting your role below.</p>
          <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
              Sign Up As
            </button>
            <ul class="dropdown-menu">
              <li><button class="dropdown-item" onclick="location.href='signupPassenger.php'">Passenger</button></li>
              <li><button class="dropdown-item" onclick="location.href='signupDriver.php'">Driver</button></li>
              <li><button class="dropdown-item" onclick="location.href='signupChairman.php'">Chairman</button></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>