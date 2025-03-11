<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include 'includes/header.php';
?>

<h1>Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to your home page.</h1>
<p>
    <a href="statement.php">Generate Statement</a>
    <a href="logout.php">Sign Out</a>
</p>

<?php
include 'includes/footer.php';
?>