<?php
require 'connect.php';

$message = "";
$messageType = "";

// Determine which form to show (default: customer)
$form = isset($_GET['form']) ? $_GET['form'] : 'customer';

// ── Handle Customer Insert ──────────────────────────────────────────────────
if (isset($_POST['add_customer'])) {
    $name    = trim($_POST['name']);
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Basic phone validation: digits only, 7–15 chars
    if (!preg_match('/^\d{7,15}$/', $phone)) {
        $message = "Invalid phone number. Use 7–15 digits only.";
        $messageType = "error";
    } elseif (empty($name) || empty($address)) {
        $message = "All fields are required.";
        $messageType = "error";
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO Customer (Name, Phone_Number, Address) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $phone, $address);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Customer added successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $messageType = "error";
        }
        mysqli_stmt_close($stmt);
    }
    $form = 'customer';
}

// ── Handle Vehicle Insert ───────────────────────────────────────────────────
if (isset($_POST['add_vehicle'])) {
    $customer_id    = intval($_POST['customer_id']);
    $vehicle_number = trim($_POST['vehicle_number']);
    $vehicle_type   = trim($_POST['vehicle_type']);

    if (empty($vehicle_number) || empty($vehicle_type) || $customer_id <= 0) {
        $message = "All fields are required.";
        $messageType = "error";
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO Vehicle (Customer_ID, Vehicle_Number, Vehicle_Type) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $customer_id, $vehicle_number, $vehicle_type);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Vehicle added successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $messageType = "error";
        }
        mysqli_stmt_close($stmt);
    }
    $form = 'vehicle';
}

// ── Handle Service Insert ───────────────────────────────────────────────────
if (isset($_POST['add_service'])) {
    $vehicle_id   = intval($_POST['vehicle_id']);
    $service_type = trim($_POST['service_type']);
    $service_date = trim($_POST['service_date']);

    if (empty($service_type) || empty($service_date) || $vehicle_id <= 0) {
        $message = "All fields are required.";
        $messageType = "error";
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO Service (Vehicle_ID, Service_Type, Service_Date) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $vehicle_id, $service_type, $service_date);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Service record added successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $messageType = "error";
        }
        mysqli_stmt_close($stmt);
    }
    $form = 'service';
}

// Fetch customers and vehicles for dropdowns
$customers = mysqli_query($conn, "SELECT Customer_ID, Name FROM Customer ORDER BY Name");
$vehicles  = mysqli_query($conn, "SELECT v.Vehicle_ID, v.Vehicle_Number, c.Name
                                   FROM Vehicle v
                                   JOIN Customer c ON v.Customer_ID = c.Customer_ID
                                   ORDER BY v.Vehicle_Number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Records – VehicleServ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a href="index.html" class="brand">&#9881; VehicleServ</a>
    <a href="index.html">Home</a>
    <a href="insert.php" class="active">Add Records</a>
    <a href="view.php">View Records</a>
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

    <!-- ── Customer Form ── -->
    <?php if ($form === 'customer'): ?>
    <div class="form-box">
        <h2>&#128100; Add Customer</h2>
        <form method="POST" action="insert.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="e.g. John Smith" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="e.g. 0712345678"
                       pattern="\d{7,15}" title="7–15 digits only" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" placeholder="e.g. 123 Main St" required>
            </div>
            <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
        </form>
    </div>

    <!-- ── Vehicle Form ── -->
    <?php elseif ($form === 'vehicle'): ?>
    <div class="form-box">
        <h2>&#128663; Add Vehicle</h2>
        <form method="POST" action="insert.php">
            <div class="form-group">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php while ($row = mysqli_fetch_assoc($customers)): ?>
                        <option value="<?= $row['Customer_ID'] ?>">
                            <?= htmlspecialchars($row['Name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="vehicle_number">Vehicle Number</label>
                <input type="text" id="vehicle_number" name="vehicle_number"
                       placeholder="e.g. KAA 123A" required>
            </div>
            <div class="form-group">
                <label for="vehicle_type">Vehicle Type</label>
                <select id="vehicle_type" name="vehicle_type" required>
                    <option value="">-- Select Type --</option>
                    <option>Sedan</option>
                    <option>SUV</option>
                    <option>Truck</option>
                    <option>Van</option>
                    <option>Motorcycle</option>
                    <option>Bus</option>
                    <option>Other</option>
                </select>
            </div>
            <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
        </form>
    </div>

    <!-- ── Service Form ── -->
    <?php elseif ($form === 'service'): ?>
    <div class="form-box">
        <h2>&#128295; Add Service Record</h2>
        <form method="POST" action="insert.php">
            <div class="form-group">
                <label for="vehicle_id">Vehicle</label>
                <select id="vehicle_id" name="vehicle_id" required>
                    <option value="">-- Select Vehicle --</option>
                    <?php while ($row = mysqli_fetch_assoc($vehicles)): ?>
                        <option value="<?= $row['Vehicle_ID'] ?>">
                            <?= htmlspecialchars($row['Vehicle_Number']) ?>
                            (<?= htmlspecialchars($row['Name']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="service_type">Service Type</label>
                <select id="service_type" name="service_type" required>
                    <option value="">-- Select Service --</option>
                    <option>Oil Change</option>
                    <option>Tire Rotation</option>
                    <option>Brake Inspection</option>
                    <option>Engine Tune-Up</option>
                    <option>Battery Replacement</option>
                    <option>Air Filter Replacement</option>
                    <option>Transmission Service</option>
                    <option>Full Service</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="service_date">Service Date</label>
                <input type="date" id="service_date" name="service_date" required>
            </div>
            <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
