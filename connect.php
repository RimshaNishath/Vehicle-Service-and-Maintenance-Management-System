<?php
$host     = "localhost";
$user     = "root";
$password = "root";
$database = "vehicle_service";

$conn = mysqli_connect($host, $user, $password);
if (!$conn) die("Connection failed: " . mysqli_connect_error());

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `vehicle_service`");
mysqli_select_db($conn, $database);

// Users table for login
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS Users (
        User_ID  INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(100) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        Role     ENUM('owner','customer') NOT NULL
    )
");

// Insert default owner account (username: admin, password: admin123)
$owner_pass = password_hash('admin123', PASSWORD_DEFAULT);
mysqli_query($conn, "INSERT IGNORE INTO Users (Username, Password, Role)
                     VALUES ('admin', '$owner_pass', 'owner')");

// Customer table linked to Users
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS Customer (
        Customer_ID  INT AUTO_INCREMENT PRIMARY KEY,
        User_ID      INT,
        Name         VARCHAR(100) NOT NULL,
        Phone_Number VARCHAR(15) NOT NULL,
        Address      VARCHAR(255) NOT NULL,
        FOREIGN KEY (User_ID) REFERENCES Users(User_ID) ON DELETE SET NULL
    )
");

// Vehicle table
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS Vehicle (
        Vehicle_ID     INT AUTO_INCREMENT PRIMARY KEY,
        Customer_ID    INT NOT NULL,
        Vehicle_Number VARCHAR(20) NOT NULL,
        Vehicle_Type   VARCHAR(50) NOT NULL,
        FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID) ON DELETE CASCADE
    )
");

// Service table
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS Service (
        Service_ID   INT AUTO_INCREMENT PRIMARY KEY,
        Vehicle_ID   INT NOT NULL,
        Service_Type VARCHAR(100) NOT NULL,
        Service_Date DATE NOT NULL,
        FOREIGN KEY (Vehicle_ID) REFERENCES Vehicle(Vehicle_ID) ON DELETE CASCADE
    )
");
?>
