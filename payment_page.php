<?php
session_start();
include './conn.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=home.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';
$message_class = '';
$bookingData = null;
$trainer = null;
$totalAmount = 0.00;

// --- 1. Get Booking ID and Fetch Details ---
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header("Location: home.php?error=Invalid_booking_specified_for_payment.");
    exit();
}

$bookingID = $_GET['booking_id'];

try {
    // Fetch user details for navigation
    $userStmt = $conn->prepare("SELECT `full_name` FROM `tbl_user` WHERE `tbl_user_id` = :user_id");
    $userStmt->bindParam(':user_id', $userID);
    $userStmt->execute();
    $displayName = $userStmt->fetchColumn() ?: 'User';

    // Fetch booking details and trainer rate
    $bookingStmt = $conn->prepare("
        SELECT 
            b.*, 
            t.full_name AS trainer_name, 
            t.hourly_rate 
        FROM `tbl_booking` b
        JOIN `tbl_trainer` t ON b.trainer_id = t.trainer_id
        WHERE b.booking_id = :booking_id AND b.tbl_user_id = :user_id
    ");
    $bookingStmt->bindParam(':booking_id', $bookingID);
    $bookingStmt->bindParam(':user_id', $userID);
    $bookingStmt->execute();
    
    if ($bookingStmt->rowCount() > 0) {
        $bookingData = $bookingStmt->fetch(PDO::FETCH_ASSOC);

        // Check if payment is already processed or canceled
        if ($bookingData['status'] !== 'Pending') {
             header("Location: home.php?error=Payment_already_processed_or_booking_status_is_not_pending.");
             exit();
        }

        // Calculate session duration in hours
        $start = new DateTime($bookingData['start_time']);
        $end = new DateTime($bookingData['end_time']);
        $interval = $start->diff($end);
        
        // Convert total duration to decimal hours (e.g., 1 hour 30 mins = 1.5)
        $durationHours = $interval->h + ($interval->i / 60);

        $totalAmount = $durationHours * $bookingData['hourly_rate'];
        $trainer = ['full_name' => $bookingData['trainer_name']];

    } else {
        header("Location: home.php?error=Booking_details_not_found.");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error fetching payment data in payment_page.php: " . $e->getMessage());
    $message = "Database error fetching payment details.";
    $message_class = "bg-red-500";
}

// --- 2. Handle Payment Submission (Mock Transaction) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'process_payment') {
    
    // In a real application, this is where you would call a payment gateway API (Stripe, PayPal, etc.)
    // For this mock implementation, we assume success.
    
    $transactionRef = 'TXN_' . uniqid() . rand(1000, 9999);
    $status = 'Completed'; 
    $amountToPay = $totalAmount; // Use the calculated amount

    try {
        // Start transaction for atomicity
        $conn->beginTransaction();

        // A. Insert successful payment record into tbl_payment
        $payStmt = $conn->prepare("INSERT INTO `tbl_payment` (`tbl_user_id`, `booking_id`, `amount`, `status`, `transaction_ref`) VALUES (:user_id, :booking_id, :amount, :status, :transaction_ref)");
        $payStmt->bindParam(':user_id', $userID);
        $payStmt->bindParam(':booking_id', $bookingID);
        $payStmt->bindParam(':amount', $amountToPay);
        $payStmt->bindParam(':status', $status);
        $payStmt->bindParam(':transaction_ref', $transactionRef);
        $payStmt->execute();

        // B. Update tbl_booking status to 'Confirmed'
        $bookStmt = $conn->prepare("UPDATE `tbl_booking` SET `status` = 'Confirmed' WHERE `booking_id` = :booking_id AND `tbl_user_id` = :user_id");
        $bookStmt->bindParam(':booking_id', $bookingID);
        $bookStmt->bindParam(':user_id', $userID);
        $bookStmt->execute();

        $conn->commit();

        header("Location: home.php?message=" . urlencode("Payment_Successful!_Your_session_with_{$trainer['full_name']}_is_Confirmed."));
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error processing payment: " . $e->getMessage());
        $message = "Payment processing failed due to a database error.";
        $message_class = "bg-red-500";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Make Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom right, #0a0a0a, #1a1a1a);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 0;
            color: #e2e8f0;
        }
        .main-content-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .container {
            background-color: #1f2937;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            padding: 3rem 2.5rem;
            max-width: 90%;
            width: 700px;
            color: #e2e8f0;
            border: 1px solid rgba(71, 85, 105, 0.5);
            transition: transform 0.3s ease;
        }
        .summary-box {
            background-color: #111827;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #4a5568;
        }
        .form-input {
            background-color: #374151;
            border: 1px solid #4b5563;
            color: #e2e8f0;
            padding: 0.85rem 1.25rem;
            border-radius: 0.75rem;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            border-color: #10b981; /* Green focus for payment theme */
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.5);
            background-color: #3f4a5a;
        }
        .action-button {
            padding: 0.9rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar (Omitted for brevity, assuming standard nav from other files) -->
    <nav class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg p-4 shadow-lg w-full">
        <div class="container mx-auto flex justify-between items-center max-w-7xl">
            <a href="home.php" class="text-white text-2xl font-bold rounded-md px-3 py-1 transition-colors duration-200 hover:text-red-400">Beast Fitness</a>
            <span class="text-white">Welcome, <?= htmlspecialchars($displayName) ?>!</span>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="container">
            <h1 class="text-5xl lg:text-6xl font-extrabold text-red-500 mb-12 text-center tracking-tight">
                <span class="block text-white font-semibold mb-2">Checkout</span>
                <span class="block text-4xl">Secure Payment</span>
            </h1>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($bookingData): ?>
                <div class="summary-box">
                    <h2 class="text-2xl font-bold text-red-400 mb-4">Booking Summary</h2>
                    <div class="space-y-2 text-gray-300">
                        <p>Trainer: <span class="font-semibold text-white"><?= htmlspecialchars($trainer['full_name']) ?></span></p>
                        <p>Date: <span class="font-semibold text-white"><?= htmlspecialchars($bookingData['booking_date']) ?></span></p>
                        <p>Time: <span class="font-semibold text-white"><?= htmlspecialchars(date('h:i A', strtotime($bookingData['start_time']))) ?> - <?= htmlspecialchars(date('h:i A', strtotime($bookingData['end_time']))) ?></span></p>
                        <div class="pt-2 border-t border-gray-700 mt-2">
                            <p class="text-xl font-extrabold text-green-400">Total Due: <span id="total-amount">$<?= number_format($totalAmount, 2) ?></span></p>
                        </div>
                    </div>
                </div>

                <form action="payment_page.php?booking_id=<?= htmlspecialchars($bookingID) ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="process_payment">
                    
                    <h2 class="text-2xl font-bold text-white mb-4">Payment Details (Mock)</h2>
                    
                    <div>
                        <label for="card_number" class="block text-gray-300 text-sm font-medium mb-2">Card Number:</label>
                        <input type="text" id="card_number" name="card_number" class="form-input w-full" placeholder="XXXX XXXX XXXX XXXX" required>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label for="expiry_date" class="block text-gray-300 text-sm font-medium mb-2">Expiry (MM/YY):</label>
                            <input type="text" id="expiry_date" name="expiry_date" class="form-input w-full" placeholder="MM/YY" required>
                        </div>
                        <div>
                            <label for="cvv" class="block text-gray-300 text-sm font-medium mb-2">CVV:</label>
                            <input type="text" id="cvv" name="cvv" class="form-input w-full" placeholder="XXX" required>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-4 pt-4">
                        <a href="trainers.php" class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg">
                            Cancel Payment
                        </a>
                        <button type="submit" class="action-button bg-green-600 hover:bg-green-700 text-white shadow-lg">
                            Pay $<?= number_format($totalAmount, 2) ?>
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-center text-red-400 text-lg">Failed to load booking details for payment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8 w-full">
        <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
    </footer>

    <script>
        // Basic input mask for better UX (optional)
        document.addEventListener('DOMContentLoaded', () => {
            const cardNumberInput = document.getElementById('card_number');
            const expiryDateInput = document.getElementById('expiry_date');

            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', (e) => {
                    // Remove all non-digits
                    let value = e.target.value.replace(/\D/g, '');
                    // Insert spaces every 4 digits
                    value = value.replace(/(\d{4}(?!\s))/g, '$1 ').trim();
                    e.target.value = value;
                });
            }

            if (expiryDateInput) {
                expiryDateInput.addEventListener('input', (e) => {
                    // Remove all non-digits
                    let value = e.target.value.replace(/\D/g, '');
                    // Insert slash after 2 digits
                    if (value.length > 2) {
                        value = value.slice(0, 2) + '/' + value.slice(2, 4);
                    }
                    e.target.value = value;
                });
            }
        });
    </script>
</body>
</html>