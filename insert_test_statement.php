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

$user_id = 1; // Replace with the appropriate user ID
$statement_date = '2025-03-01';
$description = 'Test statement';

$sql = "INSERT INTO statements (user_id, statement_date, description) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $statement_date, $description);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Test statement inserted successfully.";
} else {
    echo "Failed to insert test statement.";
}

$stmt->close();
$conn->close();
?>