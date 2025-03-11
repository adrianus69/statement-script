<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include 'includes/db.php';
include 'includes/header.php';

// Initialize variables for search and filter
$search_date = '';
$search_amount = '';
$search_description = '';

$sql = "SELECT id, statement_date, amount, description FROM statements WHERE user_id = ?";

// Handling form submission for search and filter
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['search_date'])) {
        $search_date = $_GET['search_date'];
        $sql .= " AND statement_date = '$search_date'";
    }
    if (isset($_GET['search_amount'])) {
        $search_amount = $_GET['search_amount'];
        $sql .= " AND amount = '$search_amount'";
    }
    if (isset($_GET['search_description'])) {
        $search_description = $_GET['search_description'];
        $sql .= " AND description LIKE '%$search_description%'";
    }
}

$sql .= " ORDER BY statement_date DESC";

echo "<h2>Manage Statements</h2>";

// Search and Filter Form
echo "<form method='get' action='manage_statements.php'>";
echo "  <label for='search_date'>Date:</label>";
echo "  <input type='date' name='search_date' value='$search_date'>";
echo "  <label for='search_amount'>Amount:</label>";
echo "  <input type='number' name='search_amount' step='0.01' value='$search_amount'>";
echo "  <label for='search_description'>Description:</label>";
echo "  <input type='text' name='search_description' value='$search_description'>";
echo "  <input type='submit' value='Search'>";
echo "</form>";

$user_id = $_SESSION['id'];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($id, $statement_date, $amount, $description);

    while ($stmt->fetch()) {
        echo "<div>";
        echo "<p>Date: " . htmlspecialchars($statement_date) . "</p>";
        echo "<p>Amount: $" . htmlspecialchars($amount) . "</p>";
        echo "<p>Description: " . htmlspecialchars($description) . "</p>";
        echo "<a href='view_statement.php?id=" . $id . "'>View Statement</a>";
        echo " | ";
        echo "<a href='delete_statement.php?id=" . $id . "'>Delete Statement</a>";
        echo "</div><hr>";
    }
    $stmt->close();
}

$conn->close();
include 'includes/footer.php';
?>