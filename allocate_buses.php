<?php
session_start();

if (!isset($_SESSION['manager_id'])) {
    header("Location: login2.html");
    exit;
}

$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "ded2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$route = $_POST['route'];
$time_slot = $_POST['time_slot'];
$buses = intval($_POST['buses']);

// Insert or update bus allocation
$sql = "
    INSERT INTO bus_allocations (route, time_slot, buses_allocated)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE buses_allocated = VALUES(buses_allocated)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $route, $time_slot, $buses);

if ($stmt->execute()) {
    echo "<script>alert('Buses allocated successfully'); window.location.href = 'bus_manager_interface.php';</script>";
} else {
    echo "<script>alert('Failed to allocate buses'); window.location.href = 'bus_manager_interface.php';</script>";
}

$stmt->close();
$conn->close();
?>
