<?php
// Database configuration
$host = 'localhost'; // Change if your database is hosted remotely
$dbname = 'test_db'; // Replace with your database name
$username = 'root';  // Replace with your database username
$password = '';      // Replace with your database password

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']); // Sanitize input

    // SQL query to insert data
    $sql = "INSERT INTO names (name) VALUES ('$name')";

    if ($conn->query($sql) === TRUE) {
        echo "Name saved successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the connection

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Name to Database</title>
</head>
<body>
    <h1>Enter Your Name</h1>
    <form action="save_name.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>