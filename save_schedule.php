<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "ded2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Retrieve POST data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$route = $data['route'] ?? null;
$time_slot = $data['time_slot'] ?? null;
$type = $data['type'] ?? null; // 'arrival' or 'departure'

if (!$route || !$time_slot || !$type) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// Insert into respective table
if ($type === 'arrival') {
    $sql = "INSERT INTO arrival_info (user_id, route, time_slot) VALUES (?, ?, ?)";
} elseif ($type === 'departure') {
    $sql = "INSERT INTO departure_info (user_id, route, time_slot) VALUES (?, ?, ?)";
} else {
    echo json_encode(["status" => "error", "message" => "Invalid type"]);
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $_SESSION['user_id'], $route, $time_slot);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => ucfirst($type) . " information saved successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save information"]);
}

$stmt->close();
$conn->close();
?>
