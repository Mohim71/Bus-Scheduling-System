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

$bus_no = $_POST['bus_no'];
$new_route = $_POST['new_route'];

$sql = "UPDATE bus_info SET designated_route = ? WHERE bus_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $new_route, $bus_no);

if ($stmt->execute()) {
    echo "<script>alert('Bus route updated successfully'); window.location.href = 'bus_manager_interface.php';</script>";
} else {
    echo "<script>alert('Failed to update bus route'); window.location.href = 'bus_manager_interface.php';</script>";
}

$stmt->close();
$conn->close();
?>
