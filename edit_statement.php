<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$statements_directory = 'statements';
$file = isset($_GET['file']) ? $_GET['file'] : '';

if (!$file || !file_exists($statements_directory . '/' . $file)) {
    echo "Invalid file.";
    exit;
}

// Load statement data from the file (assuming JSON format for simplicity)
$file_path = $statements_directory . '/' . $file;
$statement_data = json_decode(file_get_contents($file_path), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update statement data with posted values
    $statement_data['artist_name'] = $_POST['artist_name'];
    $statement_data['company'] = $_POST['company'];
    $statement_data['total_due'] = $_POST['total_due'];
    $statement_data['additional_info'] = $_POST['additional_info'];
    $statement_data['dates'] = $_POST['date'];
    $statement_data['show_names'] = $_POST['show_name'];
    $statement_data['total_amounts'] = $_POST['total_amount'];
    $statement_data['booking_fees'] = $_POST['booking_fee'];
    $statement_data['mgmt_fees'] = $_POST['mgmt_fee'];
    $statement_data['flights'] = $_POST['flights'];
    $statement_data['payments'] = $_POST['payment'];
    $statement_data['statement_number'] = $_POST['statement_number'];

    // Save the updated statement data back to the file
    file_put_contents($file_path, json_encode($statement_data));

    // Redirect to the Statements page
    header("Location: statements.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Statement</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">Your Company</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="statements.php">Statements</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container centered compact">
        <h2>Edit Statement</h2>
        <form action="edit_statement.php?file=<?= urlencode($file) ?>" method="POST">
            <div class="form-group">
                <label for="statement_number">Statement Number</label>
                <input type="text" id="statement_number" name="statement_number" value="<?= htmlspecialchars($statement_data['statement_number']) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="artist_name">Artist Name</label>
                <input type="text" id="artist_name" name="artist_name" value="<?= htmlspecialchars($statement_data['artist_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="company">Company</label>
                <input type="text" id="company" name="company" value="<?= htmlspecialchars($statement_data['company']) ?>" required>
            </div>
            <div class="form-group">
                <label for="total_due">Total Due</label>
                <input type="number" id="total_due" name="total_due" step="0.01" value="<?= htmlspecialchars($statement_data['total_due']) ?>" required>
            </div>
            <div class="form-group">
                <label for="additional_info">Additional Info</label>
                <textarea id="additional_info" name="additional_info" rows="5"><?= htmlspecialchars($statement_data['additional_info']) ?></textarea>
            </div>
            <div id="show-container">
                <?php foreach ($statement_data['dates'] as $index => $date): ?>
                    <div class="show-details">
                        <div>
                            <h3>Show <?= $index + 1 ?></h3>
                            <table>
                                <tr>
                                    <td><label for="date_<?= $index ?>">Date</label></td>
                                    <td><input type="date" id="date_<?= $index ?>" name="date[]" value="<?= htmlspecialchars($date) ?>" required></td>
                                </tr>
                                <tr>
                                    <td><label for="show_name_<?= $index ?>">Artist Name + Show Name</label></td>
                                    <td><input type="text" id="show_name_<?= $index ?>" name="show_name[]" value="<?= htmlspecialchars($statement_data['show_names'][$index]) ?>" required></td>
                                </tr>
                                <tr>
                                    <td><label for="total_amount_<?= $index ?>">Total Amount</label></td>
                                    <td><input type="number" id="total_amount_<?= $index ?>" name="total_amount[]" step="0.01" value="<?= htmlspecialchars($statement_data['total_amounts'][$index]) ?>" required></td>
                                </tr>
                            </table>
                            <h4>Expenses</h4>
                            <div class="expense-container">
                                <table>
                                    <tr>
                                        <td><label for="booking_fee_<?= $index ?>">Booking Fee</label></td>
                                        <td><input type="number" id="booking_fee_<?= $index ?>" name="booking_fee[]" step="0.01" value="<?= htmlspecialchars($statement_data['booking_fees'][$index]) ?>" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="mgmt_fee_<?= $index ?>">Management Fee</label></td>
                                        <td><input type="number" id="mgmt_fee_<?= $index ?>" name="mgmt_fee[]" step="0.01" value="<?= htmlspecialchars($statement_data['mgmt_fees'][$index]) ?>" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="flights_<?= $index ?>">Flights</label></td>
                                        <td><input type="number" id="flights_<?= $index ?>" name="flights[]" step="0.01" value="<?= htmlspecialchars($statement_data['flights'][$index]) ?>" required></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="payment-container">
                <?php foreach ($statement_data['payments'] as $index => $payment): ?>
                    <div class="payment-details">
                        <table>
                            <tr>
                                <td><label for="payment_<?= $index ?>">Payment <?= $index + 1 ?></label></td>
                                <td><input type="number" id="payment_<?= $index ?>" name="payment[]" step="0.01" value="<?= htmlspecialchars($payment) ?>" required></td>
                            </tr>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit">Update Statement</button>
        </form>
    </div>
    <div class="footer">
        Your Company &copy; 2025. All rights reserved.
    </div>
</body>
</html>