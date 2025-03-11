<?php
$servername = "localhost";
$dbusername = "continualink_statement";
$dbpassword = "Kp6Q7pLQGXNdYD66juUM";
$dbname = "continualink_statement";

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = 'admin';
$password = password_hash('password', PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "User created successfully.";
} else {
    echo "Failed to create user.";
}

$stmt->close();
$conn->close();
?>