<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "ded2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manager_id = $_POST['manager_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM bus_manager_details WHERE manager_id = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $manager_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['manager_id'] = $manager_id;
        header("Location: bus_manager_interface.php");
        exit;
    } else {
        echo "<script>alert('Invalid Manager ID or Password'); window.location.href = 'login2.html';</script>";
    }
}

$conn->close();
?>
