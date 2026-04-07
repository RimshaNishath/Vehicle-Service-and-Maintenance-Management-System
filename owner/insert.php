<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php"); exit;
}
require '../connect.php';

$message = "";
$messageType = "";
$form = isset($_GET['form']) ? $_GET['form'] : 'customer';

// ── Add Customer + create login account ────────────────────────────────────
if (isset($_POST['add_customer'])) {
    $name     = trim($_POST['name']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $username = trim($_POST['cust_username']);
    $password = trim($_POST['cust_password']);

    if (!preg_match('/^\d{7,15}$/', $phone)) {
        $message = "Invalid phone number. Use 7–15 digits only.";
        $messageType = "error";
    } elseif (empty($name) || empty($address) || empty($username) || empty($password)) {
        $message = "All fields are required.";
        $messageType = "error";
    } else {
        // Create user account
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO Users (Username, Password, Role) VALUES (?, ?, 'customer')");
        if (!$stmt) {
            $message = "DB error: " . mysqli_error($conn);
            $messageType = "error";
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed);
            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                $stmt2 = mysqli_prepare($conn, "INSERT INTO Customer (User_ID, Name, Phone_Number, Address) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt2, "isss", $user_id, $name, $phone, $address);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                $message = "Customer added! Login: $username / $password";
                $messageType = "success";
            } else {
                mysqli_stmt_close($stmt);
                $message = "Username already exists. Choose another.";
                $messageType = "error";
            }
        }
    }
    $form = 'customer';
}

// ── Add Vehicle ─────────────────────────────────────────────────────────────
if (isset($_POST['add_vehicle'])) {
    $customer_id    = intval($_POST['customer_id']);
    $vehicle_number = trim($_POST['vehicle_number']);
    $vehicle_type   = trim($_POST['vehicle_type']);

    if (empty($vehicle_number) || empty($vehicle_type) || $customer_id <= 0) {
        $message = "All fields are required."; $messageType = "error";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Vehicle (Customer_ID, Vehicle_Number, Vehicle_Type) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $customer_id, $vehicle_number, $vehicle_type);
        mysqli_stmt_execute($stmt) ? ($message = "Vehicle added!") && ($messageType = "success")
                                   : ($message = mysqli_error($conn)) && ($messageType = "error");
        mysqli_stmt_close($stmt);
    }
    $form = 'vehicle';
}

// ── Add Service ──────────────────────────────────────────────────────────────
if (isset($_POST['add_service'])) {
    $vehicle_id   = intval($_POST['vehicle_id']);
    $service_type = trim($_POST['service_type']);
    $service_date = trim($_POST['service_date']);

    if (empty($service_type) || empty($service_date) || $vehicle_id <= 0) {
        $message = "All fields are required."; $messageType = "error";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Service (Vehicle_ID, Service_Type, Service_Date) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $vehicle_id, $service_type, $service_date);
        mysqli_stmt_execute($stmt) ? ($message = "Service record added!") && ($messageType = "success")
                                   : ($message = mysqli_error($conn)) && ($messageType = "error");
        mysqli_stmt_close($stmt);
    }
    $form = 'service';
}

$customers = mysqli_query($conn, "SELECT Customer_ID, Name FROM Customer ORDER BY Name");
$vehicles  = mysqli_query($conn, "SELECT v.Vehicle_ID, v.Vehicle_Number, c.Name FROM Vehicle v JOIN Customer c ON v.Customer_ID=c.Customer_ID ORDER BY v.Vehicle_Number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Records – Owner</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav>
    <a href="dashboard.php" class="brand">&#9881; VehicleServ</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="insert.php" class="active">Add Records</a>
    <a href="view.php">View Records</a>
    <a href="../logout.php" style="margin-left:auto">Logout</a>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Add Records</h1>
        <div>
            <a href="insert.php?form=customer" class="btn btn-secondary btn-sm">+ Customer</a>
            <a href="insert.php?form=vehicle"  class="btn btn-secondary btn-sm">+ Vehicle</a>
            <a href="insert.php?form=service"  class="btn btn-secondary btn-sm">+ Service</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($form === 'customer'): ?>
    <div class="form-box">
        <h2>&#128100; Add Customer</h2>
        <form method="POST">
            <div class="form-group"><label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. John Smith" required></div>
            <div class="form-group"><label>Phone Number</label>
                <input type="text" name="phone" placeholder="e.g. 0712345678" pattern="\d{7,15}" required></div>
            <div class="form-group"><label>Address</label>
                <input type="text" name="address" placeholder="e.g. 123 Main St" required></div>
            <div class="form-group"><label>Customer Login Username</label>
                <input type="text" name="cust_username" placeholder="e.g. john_smith" required></div>
            <div class="form-group"><label>Customer Login Password</label>
                <input type="text" name="cust_password" placeholder="Set a password" required></div>
            <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
        </form>
    </div>

    <?php elseif ($form === 'vehicle'): ?>
    <div class="form-box">
        <h2>&#128663; Add Vehicle</h2>
        <form method="POST">
            <div class="form-group"><label>Customer</label>
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php while ($r = mysqli_fetch_assoc($customers)): ?>
                        <option value="<?= $r['Customer_ID'] ?>"><?= htmlspecialchars($r['Name']) ?></option>
                    <?php endwhile; ?>
                </select></div>
            <div class="form-group"><label>Vehicle Number</label>
                <input type="text" name="vehicle_number" placeholder="e.g. KAA 123A" required></div>
            <div class="form-group"><label>Vehicle Type</label>
                <select name="vehicle_type" required>
                    <option value="">-- Select Type --</option>
                    <?php foreach (['Sedan','SUV','Truck','Van','Motorcycle','Bus','Other'] as $t): ?>
                        <option><?= $t ?></option>
                    <?php endforeach; ?>
                </select></div>
            <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
        </form>
    </div>

    <?php elseif ($form === 'service'): ?>
    <div class="form-box">
        <h2>&#128295; Add Service Record</h2>
        <form method="POST">
            <div class="form-group"><label>Vehicle</label>
                <select name="vehicle_id" required>
                    <option value="">-- Select Vehicle --</option>
                    <?php while ($r = mysqli_fetch_assoc($vehicles)): ?>
                        <option value="<?= $r['Vehicle_ID'] ?>"><?= htmlspecialchars($r['Vehicle_Number']) ?> (<?= htmlspecialchars($r['Name']) ?>)</option>
                    <?php endwhile; ?>
                </select></div>
            <div class="form-group"><label>Service Type</label>
                <select name="service_type" required>
                    <option value="">-- Select Service --</option>
                    <?php foreach (['Oil Change','Tire Rotation','Brake Inspection','Engine Tune-Up','Battery Replacement','Air Filter Replacement','Transmission Service','Full Service','Other'] as $s): ?>
                        <option><?= $s ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="form-group"><label>Service Date</label>
                <input type="date" name="service_date" required></div>
            <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
        </form>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
