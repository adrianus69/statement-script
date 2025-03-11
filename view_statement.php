<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include 'includes/db.php';
include 'includes/header.php';

if (isset($_GET['id'])) {
    $statement_id = $_GET['id'];
    $user_id = $_SESSION['id'];

    $sql = "SELECT statement_date, amount, description FROM statements WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $statement_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($statement_date, $amount, $description);

        if ($stmt->fetch()) {
            echo "<h2>Statement Details</h2>";
            echo "<p>Date: " . htmlspecialchars($statement_date) . "</p>";
            echo "<p>Amount: $" . htmlspecialchars($amount) . "</p>";
            echo "<p>Description: " . htmlspecialchars($description) . "</p>";
            echo "<a href='generate_pdf.php?id=" . $statement_id . "'>Download PDF</a>";
            echo " | ";
            echo "<a href='manage_statements.php'>Back to Manage Statements</a>";
        } else {
            echo "<p>No statement found.</p>";
        }
        $stmt->close();
    }
} else {
    echo "<p>Invalid request.</p>";
}

include 'includes/footer.php';
?>