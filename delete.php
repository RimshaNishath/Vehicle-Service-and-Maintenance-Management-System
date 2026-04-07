<?php
require 'connect.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id   = isset($_GET['id'])   ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: view.php");
    exit;
}

// ── Delete Customer (cascades to Vehicle → Service via FK) ──────────────────
if ($type === 'customer') {
    $stmt = mysqli_prepare($conn, "DELETE FROM Customer WHERE Customer_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

// ── Delete Vehicle (cascades to Service via FK) ─────────────────────────────
} elseif ($type === 'vehicle') {
    $stmt = mysqli_prepare($conn, "DELETE FROM Vehicle WHERE Vehicle_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

// ── Delete Service record ───────────────────────────────────────────────────
} elseif ($type === 'service') {
    $stmt = mysqli_prepare($conn, "DELETE FROM Service WHERE Service_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Redirect back to view page after deletion
header("Location: view.php");
exit;
?>
