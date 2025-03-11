<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$statements_directory = 'statements';

// Function to list all JSON files in the statements directory
function list_statements($directory) {
    $files = array_diff(scandir($directory), array('.', '..'));
    $json_files = array_filter($files, function($file) use ($directory) {
        return is_file($directory . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
    });
    return $json_files;
}

// Function to filter files by client and period
function filter_statements($files, $client = '', $period = '') {
    $filtered_files = [];
    $now = time();
    foreach ($files as $file) {
        if ($client && stripos($file, $client) === false) {
            continue;
        }
        if ($period) {
            $file_time = filemtime('statements/' . $file);
            switch ($period) {
                case '7 days':
                    if ($now - $file_time > 7 * 24 * 60 * 60) continue;
                    break;
                case '30 days':
                    if ($now - $file_time > 30 * 24 * 60 * 60) continue;
                    break;
                case '90 days':
                    if ($now - $file_time > 90 * 24 * 60 * 60) continue;
                    break;
                case '360 days':
                    if ($now - $file_time > 360 * 24 * 60 * 60) continue;
                    break;
            }
        }
        $filtered_files[] = $file;
    }
    return $filtered_files;
}

$statement_files = list_statements($statements_directory);
$filter_client = isset($_GET['client']) ? $_GET['client'] : '';
$filter_period = isset($_GET['period']) ? $_GET['period'] : '';
$filtered_files = filter_statements($statement_files, $filter_client, $filter_period);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statements</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function applyFilter() {
            const client = document.getElementById('client_filter').value;
            const period = document.getElementById('period_filter').value;
            window.location.href = `statements.php?client=${client}&period=${period}`;
        }
    </script>
</head>
<body>
    <div class="navbar">
        <div class="brand">Your Company</div>
        <div class="nav-links">
            <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="statements.php" class="<?= $current_page == 'statements.php' ? 'active' : '' ?>">Statements</a>
            <a href="logout.php" class="<?= $current_page == 'logout.php' ? 'active' : '' ?>">Logout</a>
        </div>
    </div>
    <div class="container centered compact">
        <h2>Statements</h2>
        <div class="filter-group">
            <label for="client_filter">Client:</label>
            <input type="text" id="client_filter" value="<?= htmlspecialchars($filter_client) ?>" placeholder="Enter client name">
            <label for="period_filter">Period:</label>
            <select id="period_filter">
                <option value="">All</option>
                <option value="7 days" <?= $filter_period == '7 days' ? 'selected' : '' ?>>Last 7 days</option>
                <option value="30 days" <?= $filter_period == '30 days' ? 'selected' : '' ?>>Last 30 days</option>
                <option value="90 days" <?= $filter_period == '90 days' ? 'selected' : '' ?>>Last 90 days</option>
                <option value="360 days" <?= $filter_period == '360 days' ? 'selected' : '' ?>>Last 360 days</option>
            </select>
            <button onclick="applyFilter()">Apply Filter</button>
        </div>
        <?php if (!empty($filtered_files)): ?>
            <ul>
                <?php foreach ($filtered_files as $file): 
                    $pdf_file = str_replace('.json', '.pdf', $file);
                ?>
                    <li>
                        <a href="<?= $statements_directory . '/' . $pdf_file ?>" target="_blank">View</a>
                        <a href="<?= $statements_directory . '/' . $pdf_file ?>" download>Download</a>
                        <a href="edit_statement.php?file=<?= urlencode($file) ?>">Edit</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No statements found.</p>
        <?php endif; ?>
    </div>
    <div class="footer">
        Your Company &copy; 2025. All rights reserved.
    </div>
</body>
</html>