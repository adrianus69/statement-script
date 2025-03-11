<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require('path/to/fpdf.php'); // Adjust this path to point to your FPDF library
include 'includes/db.php';

if (isset($_GET['id'])) {
    $statement_id = $_GET['id'];
    $user_id = $_SESSION['id'];

    // Fetch statement details from the database
    $sql = "SELECT statement_date, amount, description FROM statements WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $statement_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($statement_date, $amount, $description);
        if ($stmt->fetch()) {
            // Create a new PDF instance
            $pdf = new FPDF();
            $pdf->AddPage();

            // Set font
            $pdf->SetFont('Arial', 'B', 16);

            // Add headers
            $pdf->Cell(0, 10, 'Artist Settlement Statement', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, 'Artist Name: ' . htmlspecialchars($_SESSION["username"]), 0, 1, 'C');
            $pdf->Cell(0, 10, 'Statement Date: ' . $statement_date, 0, 1, 'C');
            $pdf->Cell(0, 10, 'Description: ' . $description, 0, 1, 'C');

            // Add other details and formatting as needed...

            // Output the PDF
            $pdf->Output('D', 'statement_' . $statement_id . '.pdf');
        } else {
            echo "No statement found.";
        }
        $stmt->close();
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>