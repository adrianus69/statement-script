<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include 'includes/db.php';

if (isset($_GET['id'])) {
    $statement_id = $_GET['id'];
    $user_id = $_SESSION['id'];

    $sql = "DELETE FROM statements WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $statement_id, $user_id);
        if ($stmt->execute()) {
            header("location: manage_statements.php");
        } else {
            echo "Error deleting statement.";
        }
        $stmt->close();
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>