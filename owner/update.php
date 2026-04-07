<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php"); exit;
}
require '../connect.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id   = isset($_GET['id'])   ? intval($_GET['id']) : 0;
$message = ""; $messageType = "";

if (isset($_POST['update_customer'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']); $phone = trim($_POST['phone']); $address = trim($_POST['address']);
    if (!preg_match('/^\d{7,15}$/', $phone)) { $message = "Invalid phone."; $messageType = "error"; }
    else {
        $stmt = mysqli_prepare($conn, "UPDATE Customer SET Name=?, Phone_Number=?, Address=? WHERE Customer_ID=?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $address, $id);
        mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        header("Location: view.php"); exit;
    }
    $type = 'customer';
}

if (isset($_POST['update_vehicle'])) {
    $id = intval($_POST['id']); $cid = intval($_POST['customer_id']);
    $vnum = trim($_POST['vehicle_number']); $vtype = trim($_POST['vehicle_type']);
    $stmt = mysqli_prepare($conn, "UPDATE Vehicle SET Customer_ID=?, Vehicle_Number=?, Vehicle_Type=? WHERE Vehicle_ID=?");
    mysqli_stmt_bind_param($stmt, "issi", $cid, $vnum, $vtype, $id);
    mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    header("Location: view.php"); exit;
}

if (isset($_POST['update_service'])) {
    $id = intval($_POST['id']); $vid = intval($_POST['vehicle_id']);
    $stype = trim($_POST['service_type']); $sdate = trim($_POST['service_date']);
    $stmt = mysqli_prepare($conn, "UPDATE Service SET Vehicle_ID=?, Service_Type=?, Service_Date=? WHERE Service_ID=?");
    mysqli_stmt_bind_param($stmt, "issi", $vid, $stype, $sdate, $id);
    mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    header("Location: view.php"); exit;
}

// Fetch record
$record = null;
if ($type === 'customer') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Customer WHERE Customer_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
    $record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
} elseif ($type === 'vehicle') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Vehicle WHERE Vehicle_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
    $record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
} elseif ($type === 'service') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Service WHERE Service_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
    $record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
}
if (!$record) { header("Location: view.php"); exit; }

$customers = mysqli_query($conn, "SELECT Customer_ID, Name FROM Customer ORDER BY Name");
$vehicles  = mysqli_query($conn, "SELECT v.Vehicle_ID, v.Vehicle_Number, c.Name FROM Vehicle v JOIN Customer c ON v.Customer_ID=c.Customer_ID ORDER BY v.Vehicle_Number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update – Owner</title>
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
    <div class="page-header"><h1>Update Record</h1><a href="view.php" class="btn btn-secondary btn-sm">&larr; Back</a></div>
    <?php if ($message): ?><div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <?php if ($type === 'customer'): ?>
    <div class="form-box"><h2>&#128100; Edit Customer</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $record['Customer_ID'] ?>">
            <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($record['Name']) ?>" required></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($record['Phone_Number']) ?>" pattern="\d{7,15}" required></div>
            <div class="form-group"><label>Address</label><input type="text" name="address" value="<?= htmlspecialchars($record['Address']) ?>" required></div>
            <button type="submit" name="update_customer" class="btn btn-primary">Save Changes</button>
        </form></div>

    <?php elseif ($type === 'vehicle'): ?>
    <div class="form-box"><h2>&#128663; Edit Vehicle</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $record['Vehicle_ID'] ?>">
            <div class="form-group"><label>Customer</label>
                <select name="customer_id" required>
                    <?php while ($c = mysqli_fetch_assoc($customers)): ?>
                        <option value="<?= $c['Customer_ID'] ?>" <?= $c['Customer_ID']==$record['Customer_ID']?'selected':'' ?>><?= htmlspecialchars($c['Name']) ?></option>
                    <?php endwhile; ?>
                </select></div>
            <div class="form-group"><label>Vehicle Number</label><input type="text" name="vehicle_number" value="<?= htmlspecialchars($record['Vehicle_Number']) ?>" required></div>
            <div class="form-group"><label>Vehicle Type</label>
                <select name="vehicle_type" required>
                    <?php foreach (['Sedan','SUV','Truck','Van','Motorcycle','Bus','Other'] as $vt): ?>
                        <option <?= $vt===$record['Vehicle_Type']?'selected':'' ?>><?= $vt ?></option>
                    <?php endforeach; ?>
                </select></div>
            <button type="submit" name="update_vehicle" class="btn btn-primary">Save Changes</button>
        </form></div>

    <?php elseif ($type === 'service'): ?>
    <div class="form-box"><h2>&#128295; Edit Service</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $record['Service_ID'] ?>">
            <div class="form-group"><label>Vehicle</label>
                <select name="vehicle_id" required>
                    <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
                        <option value="<?= $v['Vehicle_ID'] ?>" <?= $v['Vehicle_ID']==$record['Vehicle_ID']?'selected':'' ?>><?= htmlspecialchars($v['Vehicle_Number']) ?> (<?= htmlspecialchars($v['Name']) ?>)</option>
                    <?php endwhile; ?>
                </select></div>
            <div class="form-group"><label>Service Type</label>
                <select name="service_type" required>
                    <?php foreach (['Oil Change','Tire Rotation','Brake Inspection','Engine Tune-Up','Battery Replacement','Air Filter Replacement','Transmission Service','Full Service','Other'] as $st): ?>
                        <option <?= $st===$record['Service_Type']?'selected':'' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="form-group"><label>Service Date</label><input type="date" name="service_date" value="<?= htmlspecialchars($record['Service_Date']) ?>" required></div>
            <button type="submit" name="update_service" class="btn btn-primary">Save Changes</button>
        </form></div>
    <?php endif; ?>
</div>
</body></html>
