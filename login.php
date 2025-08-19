<?php

session_start();

echo "Redirecting to main_interface.php"; // Add in login.php before header()
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "ded2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    // SQL to check login credentials
    $sql = "SELECT * FROM users WHERE user_id = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['user_id'] = $user_id;
        header("Location: main_interface.php");
        exit;
    } else {
        echo "<script>alert('Invalid Username or Password'); window.location.href = 'index.html';</script>";
    }
}

$conn->close();
?>
