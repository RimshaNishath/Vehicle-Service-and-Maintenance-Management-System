<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php"); exit;
}
require '../connect.php';

$total_customers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Customer"))[0];
$total_vehicles  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Vehicle"))[0];
$total_services  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Service"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard – VehicleServ</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 36px; }
        .stat-card { background: #fff; border-radius: 10px; padding: 28px 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .stat-card .num { font-size: 2.4rem; font-weight: 700; color: #e94560; }
        .stat-card .label { color: #666; font-size: 0.9rem; margin-top: 6px; }
    </style>
</head>
<body>
<nav>
    <a href="dashboard.php" class="brand">&#9881; VehicleServ</a>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="insert.php?form=customer">Add Records</a>
    <a href="view.php">View Records</a>
    <a href="../logout.php" style="margin-left:auto">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Owner Dashboard</h1>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <div class="num"><?= $total_customers ?></div>
            <div class="label">&#128100; Total Customers</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $total_vehicles ?></div>
            <div class="label">&#128663; Total Vehicles</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $total_services ?></div>
            <div class="label">&#128295; Total Services</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="cards">
        <a href="insert.php?form=customer" class="card">
            <div class="icon">&#128100;</div>
            <h3>Add Customer</h3>
        </a>
        <a href="insert.php?form=vehicle" class="card">
            <div class="icon">&#128663;</div>
            <h3>Add Vehicle</h3>
        </a>
        <a href="insert.php?form=service" class="card">
            <div class="icon">&#128295;</div>
            <h3>Add Service</h3>
        </a>
        <a href="view.php" class="card">
            <div class="icon">&#128203;</div>
            <h3>View All Records</h3>
        </a>
    </div>
</div>
</body>
</html>
