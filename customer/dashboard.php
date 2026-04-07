<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php"); exit;
}
require '../connect.php';

$uid = intval($_SESSION['user_id']);

// Get customer record linked to this user
$result   = mysqli_query($conn, "SELECT * FROM Customer WHERE User_ID = $uid");
$customer = $result ? mysqli_fetch_assoc($result) : null;

if (!$customer) {
    echo "<p style='font-family:sans-serif;padding:30px'>No customer profile found. Please contact the service owner.</p>"; exit;
}

$cid = intval($customer['Customer_ID']);

// Get vehicles
$vehicles = mysqli_query($conn, "SELECT * FROM Vehicle WHERE Customer_ID = $cid");

// Get service history
$services = mysqli_query($conn,
    "SELECT s.*, v.Vehicle_Number, v.Vehicle_Type
     FROM Service s
     JOIN Vehicle v ON s.Vehicle_ID = v.Vehicle_ID
     WHERE v.Customer_ID = $cid
     ORDER BY s.Service_Date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard – VehicleServ</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .profile-card { background:#fff; border-radius:10px; padding:24px 28px; box-shadow:0 2px 10px rgba(0,0,0,0.08); margin-bottom:28px; display:flex; gap:20px; align-items:center; }
        .profile-card .avatar { font-size:3rem; }
        .profile-card .info h2 { font-size:1.3rem; color:#1a1a2e; margin-bottom:4px; }
        .profile-card .info p  { color:#666; font-size:0.9rem; margin:2px 0; }
    </style>
</head>
<body>
<nav>
    <a href="dashboard.php" class="brand">&#9881; VehicleServ</a>
    <a href="dashboard.php" class="active">My Dashboard</a>
    <a href="../logout.php" style="margin-left:auto">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
</nav>

<div class="container">

    <!-- Profile -->
    <div class="profile-card">
        <div class="avatar">&#128100;</div>
        <div class="info">
            <h2><?= htmlspecialchars($customer['Name']) ?></h2>
            <p>&#128222; <?= htmlspecialchars($customer['Phone_Number']) ?></p>
            <p>&#128205; <?= htmlspecialchars($customer['Address']) ?></p>
        </div>
    </div>

    <!-- My Vehicles -->
    <div class="table-box" style="margin-bottom:28px">
        <h2>&#128663; My Vehicles</h2>
        <table>
            <thead><tr><th>Vehicle Number</th><th>Type</th></tr></thead>
            <tbody>
            <?php if (!$vehicles || mysqli_num_rows($vehicles) === 0): ?>
                <tr><td colspan="2" style="text-align:center;color:#999">No vehicles registered yet.</td></tr>
            <?php else: while ($v = mysqli_fetch_assoc($vehicles)): ?>
                <tr>
                    <td><?= htmlspecialchars($v['Vehicle_Number']) ?></td>
                    <td><?= htmlspecialchars($v['Vehicle_Type']) ?></td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Service History -->
    <div class="table-box">
        <h2>&#128295; My Service History</h2>
        <table>
            <thead><tr><th>Vehicle</th><th>Service Type</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (!$services || mysqli_num_rows($services) === 0): ?>
                <tr><td colspan="3" style="text-align:center;color:#999">No service records found.</td></tr>
            <?php else: while ($s = mysqli_fetch_assoc($services)): ?>
                <tr>
                    <td><?= htmlspecialchars($s['Vehicle_Number']) ?> (<?= htmlspecialchars($s['Vehicle_Type']) ?>)</td>
                    <td><?= htmlspecialchars($s['Service_Type']) ?></td>
                    <td><?= htmlspecialchars($s['Service_Date']) ?></td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
