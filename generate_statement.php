<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require('fpdf/fpdf.php');

class PDF extends FPDF
{
    function Header()
    {
        global $artist_name, $company, $total_due, $generated_on, $statement_number;

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, "VKTM", 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, "COMPANY: $company", 0, 1, 'C');
        $this->Cell(0, 10, "Statement for $artist_name", 0, 1, 'C');
        $this->Cell(0, 10, "Total Due: $$total_due", 0, 1, 'C');
        $this->Cell(0, 10, "Generated On: $generated_on", 0, 1, 'C');
        $this->Cell(0, 10, "Statement Number: $statement_number", 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        global $additional_info;

        $this->SetY(-30);
        $this->SetFont('Arial', 'I', 8);
        $this->MultiCell(0, 10, "Additional Info:\n$additional_info", 0, 'C');
    }

    function ChapterTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, $title, 0, 1, 'C');
        $this->Ln(4);
    }

    function ChapterBody($body)
    {
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(0, 10, $body, 0, 'C');
        $this->Ln();
    }

    function FancyTable($header, $data)
    {
        $this->SetFillColor(200, 220, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 12);

        $w = array(35, 60, 35, 35, 35, 35);
        $this->SetX((297 - array_sum($w)) / 2);  // Center the table horizontally for A4 landscape
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetFont('Arial', '', 12);

        foreach ($data as $row) {
            $this->SetX((297 - array_sum($w)) / 2);  // Center the table horizontally for A4 landscape
            for ($j = 0; $j < count($header); $j++) {
                $this->Cell($w[$j], 6, $row[$j], 'LR', 0, 'C');
            }
            $this->Ln();
        }
        $this->SetX((297 - array_sum($w)) / 2);  // Center the table horizontally for A4 landscape
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

try {
    $artist_name = $_POST['artist_name'];
    $company = $_POST['company'];
    $total_due = $_POST['total_due'];
    $additional_info = $_POST['additional_info'];
    $dates = $_POST['date'];
    $show_names = $_POST['show_name'];
    $total_amounts = $_POST['total_amount'];
    $booking_fees = array();
    $mgmt_fees = array();
    $flights = array();

    // Loop through each show and get the expenses
    for ($i = 0; $i < count($dates); $i++) {
        $booking_fees[] = $_POST['booking_fee_' . $i];
        $mgmt_fees[] = $_POST['mgmt_fee_' . $i];
        $flights[] = $_POST['flights_' . $i];
    }

    $payments = $_POST['payment'];
    $statement_number = $_POST['statement_number'];
    $generated_on = date("Y-m-d H:i:s");

    $statement_data = [
        'artist_name' => $artist_name,
        'company' => $company,
        'total_due' => $total_due,
        'additional_info' => $additional_info,
        'dates' => $dates,
        'show_names' => $show_names,
        'total_amounts' => $total_amounts,
        'booking_fees' => $booking_fees,
        'mgmt_fees' => $mgmt_fees,
        'flights' => $flights,
        'payments' => $payments,
        'statement_number' => $statement_number,
        'generated_on' => $generated_on
    ];

    // Save the statement data as JSON
    $json_filename = 'statements/' . $artist_name . '_' . date('Ymd_His') . '.json';
    file_put_contents($json_filename, json_encode($statement_data));

    $pdf = new PDF('L', 'mm', 'A4');  // Landscape orientation
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Add performance details
    $pdf->ChapterTitle('Performance Details');
    $header = array('Date', 'Artist Name + Show Name', 'Total Amount', 'Booking Fee', 'Mgmt Fee', 'Flights');
    $data = [];
    for ($i = 0; $i < count($dates); $i++) {
        $data[] = [
            $dates[$i],
            $show_names[$i],
            '$' . number_format((float)$total_amounts[$i], 2),
            '$' . number_format((float)$booking_fees[$i], 2),
            '$' . number_format((float)$mgmt_fees[$i], 2),
            '$' . number_format((float)$flights[$i], 2)
        ];
    }
    $pdf->FancyTable($header, $data);

    // Add payments if they are not empty
    $pdf->ChapterTitle('Payments');
    $payment_data = [];
    foreach ($payments as $index => $payment) {
        if (!empty($payment)) {
            $payment_data[] = ['Payment ' . ($index + 1), '$' . number_format((float)$payment, 2)];
        }
    }
    if (!empty($payment_data)) {
        $pdf->FancyTable(['Payment', 'Amount'], $payment_data);
    }

    // Save the PDF to the statements directory
    $filename = 'statements/' . $artist_name . '_' . date('Ymd_His') . '.pdf';
    $pdf->Output('F', $filename);

    // Redirect to the Statements page
    header("Location: statements.php");
    exit;
} catch (Exception $e) {
    error_log($e->getMessage());
    echo "An error occurred while generating the statement. Please try again later.";
}
?>