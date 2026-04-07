<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php"); exit;
}
require '../connect.php';

$customers = mysqli_query($conn, "SELECT * FROM Customer ORDER BY Customer_ID DESC");
$vehicles  = mysqli_query($conn, "SELECT v.*, c.Name AS Customer_Name FROM Vehicle v JOIN Customer c ON v.Customer_ID=c.Customer_ID ORDER BY v.Vehicle_ID DESC");
$services  = mysqli_query($conn, "SELECT s.*, v.Vehicle_Number, c.Name AS Customer_Name FROM Service s JOIN Vehicle v ON s.Vehicle_ID=v.Vehicle_ID JOIN Customer c ON v.Customer_ID=c.Customer_ID ORDER BY s.Service_Date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Records – Owner</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav>
    <a href="dashboard.php" class="brand">&#9881; VehicleServ</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="insert.php">Add Records</a>
    <a href="view.php" class="active">View Records</a>
    <a href="../logout.php" style="margin-left:auto">Logout</a>
</nav>

<div class="container">
    <div class="page-header">
        <h1>All Records</h1>
        <a href="insert.php" class="btn btn-primary btn-sm">+ Add New</a>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('customers',this)">Customers</button>
        <button class="tab-btn"        onclick="showTab('vehicles',this)">Vehicles</button>
        <button class="tab-btn"        onclick="showTab('services',this)">Services</button>
    </div>

    <!-- Customers -->
    <div id="customers" class="tab-content active">
        <div class="table-box">
            <h2>&#128100; Customers</h2>
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Address</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($customers) === 0): ?>
                    <tr><td colspan="5" style="text-align:center;color:#999">No customers found.</td></tr>
                <?php else: while ($r = mysqli_fetch_assoc($customers)): ?>
                    <tr>
                        <td><?= $r['Customer_ID'] ?></td>
                        <td><?= htmlspecialchars($r['Name']) ?></td>
                        <td><?= htmlspecialchars($r['Phone_Number']) ?></td>
                        <td><?= htmlspecialchars($r['Address']) ?></td>
                        <td>
                            <a href="update.php?type=customer&id=<?= $r['Customer_ID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?type=customer&id=<?= $r['Customer_ID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this customer?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vehicles -->
    <div id="vehicles" class="tab-content">
        <div class="table-box">
            <h2>&#128663; Vehicles</h2>
            <table>
                <thead><tr><th>ID</th><th>Customer</th><th>Vehicle No.</th><th>Type</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($vehicles) === 0): ?>
                    <tr><td colspan="5" style="text-align:center;color:#999">No vehicles found.</td></tr>
                <?php else: while ($r = mysqli_fetch_assoc($vehicles)): ?>
                    <tr>
                        <td><?= $r['Vehicle_ID'] ?></td>
                        <td><?= htmlspecialchars($r['Customer_Name']) ?></td>
                        <td><?= htmlspecialchars($r['Vehicle_Number']) ?></td>
                        <td><?= htmlspecialchars($r['Vehicle_Type']) ?></td>
                        <td>
                            <a href="update.php?type=vehicle&id=<?= $r['Vehicle_ID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?type=vehicle&id=<?= $r['Vehicle_ID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this vehicle?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Services -->
    <div id="services" class="tab-content">
        <div class="table-box">
            <h2>&#128295; Service Records</h2>
            <table>
                <thead><tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Service Type</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($services) === 0): ?>
                    <tr><td colspan="6" style="text-align:center;color:#999">No service records found.</td></tr>
                <?php else: while ($r = mysqli_fetch_assoc($services)): ?>
                    <tr>
                        <td><?= $r['Service_ID'] ?></td>
                        <td><?= htmlspecialchars($r['Customer_Name']) ?></td>
                        <td><?= htmlspecialchars($r['Vehicle_Number']) ?></td>
                        <td><?= htmlspecialchars($r['Service_Type']) ?></td>
                        <td><?= htmlspecialchars($r['Service_Date']) ?></td>
                        <td>
                            <a href="update.php?type=service&id=<?= $r['Service_ID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?type=service&id=<?= $r['Service_ID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showTab(id, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>
