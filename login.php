<?php
session_start();
require 'connect.php';

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM Users WHERE Username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user_id']  = $user['User_ID'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role']     = $user['Role'];

        if ($user['Role'] === 'owner') {
            header("Location: owner/dashboard.php");
        } else {
            header("Location: customer/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – VehicleServ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #1a1a2e; }
        .login-box { background: #fff; border-radius: 12px; padding: 44px 40px; width: 100%; max-width: 400px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); }
        .login-box .brand { text-align: center; font-size: 1.6rem; font-weight: 700; color: #e94560; margin-bottom: 6px; }
        .login-box p.sub { text-align: center; color: #888; font-size: 0.9rem; margin-bottom: 28px; }
        .login-box h2 { font-size: 1.2rem; color: #1a1a2e; margin-bottom: 22px; text-align: center; }
        .btn { width: 100%; padding: 12px; margin-top: 6px; }
        .role-hint { margin-top: 20px; background: #f8f9fa; border-radius: 8px; padding: 14px; font-size: 0.82rem; color: #555; }
        .role-hint span { display: block; margin-bottom: 4px; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="brand">&#9881; VehicleServ</div>
    <p class="sub">Vehicle Service Management</p>
    <h2>Sign In</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary">Login</button>
    </form>

    <div class="role-hint">
        <span>&#128274; Owner login: <strong>admin</strong> / <strong>admin123</strong></span>
        <span>&#128100; Customers log in with credentials set by owner</span>
    </div>
</div>
</body>
</html>
