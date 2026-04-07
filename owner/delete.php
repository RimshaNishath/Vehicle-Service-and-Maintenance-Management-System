<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php"); exit;
}
require '../connect.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id   = isset($_GET['id'])   ? intval($_GET['id']) : 0;

if ($id > 0) {
    if ($type === 'customer') {
        // Also delete the linked user account
        $res = mysqli_query($conn, "SELECT User_ID FROM Customer WHERE Customer_ID=$id");
        $row = mysqli_fetch_assoc($res);
        $stmt = mysqli_prepare($conn, "DELETE FROM Customer WHERE Customer_ID=?");
        mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        if ($row && $row['User_ID']) {
            $stmt2 = mysqli_prepare($conn, "DELETE FROM Users WHERE User_ID=?");
            mysqli_stmt_bind_param($stmt2, "i", $row['User_ID']); mysqli_stmt_execute($stmt2); mysqli_stmt_close($stmt2);
        }
    } elseif ($type === 'vehicle') {
        $stmt = mysqli_prepare($conn, "DELETE FROM Vehicle WHERE Vehicle_ID=?");
        mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    } elseif ($type === 'service') {
        $stmt = mysqli_prepare($conn, "DELETE FROM Service WHERE Service_ID=?");
        mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    }
}

header("Location: view.php"); exit;
?>
