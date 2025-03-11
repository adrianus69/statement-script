<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Generate the statement number
$statement_number = "CONTINUA" . str_pad($_SESSION['statement_count'] ?? 1, 3, '0', STR_PAD_LEFT);
$_SESSION['statement_count'] = ($_SESSION['statement_count'] ?? 1) + 1;

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            loadArtists();
            document.getElementById('search_box').addEventListener('input', filterArtists);
        });

        function loadArtists() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_artists.php', true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const artists = this.responseText.split('\n');
                    const artistDropdown = document.getElementById('artist_name');
                    artistDropdown.innerHTML = '';
                    artists.forEach(artist => {
                        if (artist.trim() !== '') {
                            const option = document.createElement('option');
                            option.value = artist;
                            option.textContent = artist;
                            artistDropdown.appendChild(option);
                        }
                    });
                }
            };
            xhr.send();
        }

        function filterArtists() {
            const searchBox = document.getElementById('search_box');
            const filter = searchBox.value.toLowerCase();
            const artistDropdown = document.getElementById('artist_name');
            const options = artistDropdown.getElementsByTagName('option');
            for (let i = 0; i < options.length; i++) {
                const txtValue = options[i].textContent || options[i].innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    options[i].style.display = '';
                } else {
                    options[i].style.display = 'none';
                }
            }
        }

        function addArtist() {
            const newArtist = prompt('Enter the new artist name:');
            if (newArtist) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'add_artist.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (this.status === 200) {
                        loadArtists();
                    }
                };
                xhr.send('artist_name=' + encodeURIComponent(newArtist));
            }
        }

        function addShow() {
            const showContainer = document.getElementById('show-container');
            const showCount = document.getElementsByClassName('show-details').length;
            const showDiv = document.createElement('div');
            showDiv.className = 'show-details';
            showDiv.innerHTML = `
                <div>
                    <h3>Show ${showCount + 1}</h3>
                    <table>
                        <tr>
                            <td><label for="date_${showCount}">Date</label></td>
                            <td><input type="date" id="date_${showCount}" name="date[]" required></td>
                        </tr>
                        <tr>
                            <td><label for="show_name_${showCount}">Artist Name + Show Name</label></td>
                            <td><input type="text" id="show_name_${showCount}" name="show_name[]" required></td>
                        </tr>
                        <tr>
                            <td><label for="total_amount_${showCount}">Total Amount</label></td>
                            <td><input type="number" id="total_amount_${showCount}" name="total_amount[]" step="0.01" required oninput="calculateTotalDue()"></td>
                        </tr>
                    </table>
                    <h4>Expenses</h4>
                    <div id="expense-container_${showCount}" class="expense-container">
                        <div class="expense-details">
                            <table>
                                <tr>
                                    <td><label for="booking_fee_${showCount}">Booking Fee</label></td>
                                    <td><input type="number" id="booking_fee_${showCount}" name="booking_fee_${showCount}[]" step="0.01" required oninput="calculateTotalDue()"></td>
                                </tr>
                                <tr>
                                    <td><label for="mgmt_fee_${showCount}">Management Fee</label></td>
                                    <td><input type="number" id="mgmt_fee_${showCount}" name="mgmt_fee_${showCount}[]" step="0.01" required oninput="calculateTotalDue()"></td>
                                </tr>
                                <tr>
                                    <td><label for="flights_${showCount}">Flights</label></td>
                                    <td><input type="number" id="flights_${showCount}" name="flights_${showCount}[]" step="0.01" required oninput="calculateTotalDue()"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <button type="button" class="remove-btn" onclick="removeElement(this)">Remove Show</button>`;
            showContainer.appendChild(showDiv);
        }

        function addExpense(showIndex) {
            const expenseContainer = document.getElementById(`expense-container_${showIndex}`);
            const expenseCount = expenseContainer.getElementsByClassName('expense-details').length;
            const expenseDiv = document.createElement('div');
            expenseDiv.className = 'expense-details';
            expenseDiv.innerHTML = `
                <table>
                    <tr>
                        <td><label for="booking_fee_${showIndex}_${expenseCount}">Booking Fee</label></td>
                        <td><input type="number" id="booking_fee_${showIndex}_${expenseCount}" name="booking_fee_${showIndex}[]" step="0.01" required oninput="calculateTotalDue()"></td>
                    </tr>
                    <tr>
                        <td><label for="mgmt_fee_${showIndex}_${expenseCount}">Management Fee</label></td>
                        <td><input type="number" id="mgmt_fee_${showIndex}_${expenseCount}" name="mgmt_fee_${showIndex}[]" step="0.01" required oninput="calculateTotalDue()"></td>
                    </tr>
                    <tr>
                        <td><label for="flights_${showIndex}_${expenseCount}">Flights</label></td>
                        <td><input type="number" id="flights_${showIndex}_${expenseCount}" name="flights_${showIndex}[]" step="0.01" required oninput="calculateTotalDue()"></td>
                    </tr>
                </table>
                <button type="button" class="remove-btn" onclick="removeElement(this)">Remove Expense</button>`;
            expenseContainer.appendChild(expenseDiv);
        }

        function removeElement(button) {
            button.parentElement.remove();
            calculateTotalDue();
        }

        function calculateTotalDue() {
            const totalAmounts = document.querySelectorAll('input[name="total_amount[]"]');
            let totalAmount = 0;
            totalAmounts.forEach(input => {
                totalAmount += parseFloat(input.value) || 0;
            });

            let totalExpenses = 0;
            const expenseInputs = document.querySelectorAll('.expense-container input[type="number"]');
            expenseInputs.forEach(input => {
                totalExpenses += parseFloat(input.value) || 0;
            });

            const payments = document.querySelectorAll('input[name="payment[]"]');
            let totalPayments = 0;
            payments.forEach(input => {
                totalPayments += parseFloat(input.value) || 0;
            });

            const totalDue = totalAmount - totalExpenses - totalPayments;
            document.getElementById('total_due').value = totalDue.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            addShow();  // Add the first show with expenses automatically
        });
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
        <h2>Generate Statement</h2>
        <form action="generate_statement.php" method="POST">
            <div class="form-group">
                <label for="statement_number">Statement Number</label>
                <input type="text" id="statement_number" name="statement_number" value="<?php echo $statement_number; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="search_box">Search Artist</label>
                <input type="text" id="search_box" placeholder="Search artist...">
            </div>
            <div class="form-group">
                <label for="artist_name">Artist Name</label>
                <select id="artist_name" name="artist_name" required></select>
            </div>
            <div class="form-group">
                <button type="button" onclick="addArtist()">Add New Artist</button>
            </div>
            <div class="form-group">
                <label for="company">Company</label>
                <input type="text" id="company" name="company" value="Adrian Calin Media LLC" readonly>
            </div>
            <div class="form-group">
                <label for="total_due">Total Due</label>
                <input type="number" id="total_due" name="total_due" step="0.01" readonly>
            </div>
            <div class="form-group">
                <label for="additional_info">Additional Info</label>
                <textarea id="additional_info" name="additional_info" rows="5"></textarea>
            </div>
            <div id="show-container"></div>
            <button type="button" onclick="addShow()">Add Another Show</button>
            <div id="payment-container">
                <div class="payment-details">
                    <table>
                        <tr>
                            <td><label for="payment_3">Payment 3</label></td>
                            <td><input type="number" id="payment_3" name="payment[]" step="0.01" oninput="calculateTotalDue()"></td>
                        </tr>
                    </table>
                </div>
                <div class="payment-details">
                    <table>
                        <tr>
                            <td><label for="payment_4">Payment 4</label></td>
                            <td><input type="number" id="payment_4" name="payment[]" step="0.01" oninput="calculateTotalDue()"></td>
                        </tr>
                    </table>
                </div>
                <div class="payment-details">
                    <table>
                        <tr>
                            <td><label for="payment_5">Payment 5</label></td>
                            <td><input type="number" id="payment_5" name="payment[]" step="0.01" oninput="calculateTotalDue()"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <button type="button" onclick="addPayment()">Add Another Payment</button>
            <button type="submit">Generate Statement</button>
        </form>
    </div>
    <div class="footer">
        Your Company &copy; 2025. All rights reserved.
    </div>
</body>
</html>