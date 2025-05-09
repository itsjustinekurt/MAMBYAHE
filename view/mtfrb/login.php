<?php
session_start();
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'mtfrb') {
    header('Location: dashboard.php');
    exit;
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($email === 'example@gmail.com' && $password === 'pass123') {
        $_SESSION['user_role'] = 'mtfrb';
        header('Location: dashboard.php');
        exit;
    } else {
        $err = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTFRB Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: url('received_1882822929171036.jpeg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255,255,255,0.18);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.18);
            padding: 2.5rem 2rem 2rem 2rem;
            max-width: 350px;
            width: 100%;
        }
        .glass-card h3 {
            color: #ffe066;
            font-weight: 700;
        }
        .glass-card label {
            font-weight: 500;
        }
        .glass-card input {
            background: rgba(255,255,255,0.5);
            border: none;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .glass-card input:focus {
            box-shadow: 0 0 0 2px #19875433;
        }
        .glass-card .btn {
            background: #226d2c;
            color: #fff;
            font-weight: 600;
            border-radius: 0.5rem;
        }
        .glass-card .btn:hover {
            background: #1a4d1f;
        }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <form class="glass-card mx-auto" method="post" autocomplete="off">
        <h3 class="mb-2">Welcome back!</h3>
        <div class="mb-2 text-white-50" style="font-size:1.05rem;">Enter your Credentials to access your account</div>
        <?php if ($err): ?>
            <div class="alert alert-danger py-2 text-center" role="alert"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>
        <div class="mb-2">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Your email address" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn w-100">Login</button>
    </form>
</body>
</html>