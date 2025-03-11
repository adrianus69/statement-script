<?php
include 'includes/db.php';

$sql = file_get_contents('schema.sql');

if ($conn->multi_query($sql) === TRUE) {
    echo "Database initialized successfully.";
} else {
    echo "Error initializing database: " . $conn->error;
}

$conn->close();
?>