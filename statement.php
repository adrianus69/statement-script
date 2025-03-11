<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $statement_date = $_POST['statement_date'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $user_id = $_SESSION['id'];

    $sql = "INSERT INTO statements (user_id, statement_date, amount, description) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isds", $user_id, $statement_date, $amount, $description);
        if ($stmt->execute()) {
            echo "<p>Statement added successfully!</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
?>

<h2>Generate Statement</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label for="statement_date">Date:</label>
        <input type="date" name="statement_date" required>
    </div>
    <div>
        <label for="amount">Amount:</label>
        <input type="number" name="amount" step="0.01" required>
    </div>
    <div>
        <label for="description">Description:</label>
        <textarea name="description" required></textarea>
    </div>
    <div>
        <input type="submit" value="Generate Statement">
    </div>
</form>

<h2>Your Statements</h2>
<?php
$user_id = $_SESSION['id'];
$sql = "SELECT id, statement_date, amount, description FROM statements WHERE user_id = ?";
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
        echo "</div><hr>";
    }
    $stmt->close();
}
?>

<?php
include 'includes/footer.php';
?>