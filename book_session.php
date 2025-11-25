<?php
session_start();
include './conn.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=trainers.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';
$message_class = '';
$trainer = null;

// --- 1. Get Trainer ID and Fetch Details ---
if (!isset($_GET['trainer_id']) || !is_numeric($_GET['trainer_id'])) {
    header("Location: trainers.php?error=Invalid_trainer_selected");
    exit();
}

$trainerID = $_GET['trainer_id'];

try {
    // Fetch user details for navigation
    $userStmt = $conn->prepare("SELECT `full_name` FROM `tbl_user` WHERE `tbl_user_id` = :user_id");
    $userStmt->bindParam(':user_id', $userID);
    $userStmt->execute();
    $displayName = $userStmt->fetchColumn() ?: 'User';
    
    // Fetch selected trainer details
    $trainerStmt = $conn->prepare("SELECT `full_name`, `specialization`, `hourly_rate` FROM `tbl_trainer` WHERE `trainer_id` = :trainer_id AND `is_active` = 1");
    $trainerStmt->bindParam(':trainer_id', $trainerID);
    $trainerStmt->execute();
    
    if ($trainerStmt->rowCount() > 0) {
        $trainer = $trainerStmt->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: trainers.php?error=Trainer_not_found_or_inactive.");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error fetching data in book_session.php: " . $e->getMessage());
    $message = "Database error fetching details.";
    $message_class = "bg-red-500";
}

// --- 2. Handle Booking Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'book_session') {
    $bookingDate = trim($_POST['booking_date']);
    $startTime = trim($_POST['start_time']);
    $endTime = trim($_POST['end_time']);
    
    if (empty($bookingDate) || empty($startTime) || empty($endTime)) {
        $message = "All fields are required for booking.";
        $message_class = "bg-red-500";
    } else {
        // Basic time/date validation (e.g., ensuring start time is before end time, date is today or future)
        $startDateTime = strtotime("$bookingDate $startTime");
        $endDateTime = strtotime("$bookingDate $endTime");

        if ($startDateTime >= $endDateTime) {
            $message = "End time must be after the start time on the same day.";
            $message_class = "bg-red-500";
        } elseif ($startDateTime < time() - 60) { // check if booking is in the past (allow for 1 minute buffer)
            $message = "Booking date/time cannot be in the past.";
            $message_class = "bg-red-500";
        } else {
            try {
                // Insert the booking into tbl_booking with status 'Pending'
                $insertStmt = $conn->prepare("INSERT INTO `tbl_booking` (`tbl_user_id`, `trainer_id`, `booking_date`, `start_time`, `end_time`, `status`) VALUES (:user_id, :trainer_id, :booking_date, :start_time, :end_time, 'Pending')");
                
                $insertStmt->bindParam(':user_id', $userID);
                $insertStmt->bindParam(':trainer_id', $trainerID);
                $insertStmt->bindParam(':booking_date', $bookingDate);
                $insertStmt->bindParam(':start_time', $startTime);
                $insertStmt->bindParam(':end_time', $endTime);

                if ($insertStmt->execute()) {
                    // Booking successful - Redirect to a payment or confirmation page
                    header("Location: payment_page.php?booking_id=" . $conn->lastInsertId() . "&message=Booking_successful,_please_complete_payment.");
                    exit();
                } else {
                    $message = "Failed to submit booking. Please try again.";
                    $message_class = "bg-red-500";
                }
            } catch (PDOException $e) {
                // Catch any database constraint errors (e.g., trainer double booked - though advanced conflict check is skipped for now)
                error_log("Error inserting booking: " . $e->getMessage());
                $message = "A system error occurred. Please try a different time.";
                $message_class = "bg-red-500";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Book Session with <?= htmlspecialchars($trainer['full_name'] ?? 'Trainer') ?></title>
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
            border-color: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
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
        .trainer-detail-box {
            background-color: #111827;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
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
                <span class="block text-white font-semibold mb-2">Book Session</span>
                <span class="block text-4xl">with <?= htmlspecialchars($trainer['full_name'] ?? 'Trainer') ?></span>
            </h1>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($trainer): ?>
                <div class="trainer-detail-box text-center">
                    <p class="text-xl font-semibold mb-2 text-red-400"><?= htmlspecialchars($trainer['specialization']) ?></p>
                    <p class="text-gray-300">Rate: <span class="font-bold text-white">$<?= number_format($trainer['hourly_rate'], 2) ?>/hour</span></p>
                </div>
            
                <form action="book_session.php?trainer_id=<?= htmlspecialchars($trainerID) ?>" method="POST">
                    <input type="hidden" name="action" value="book_session">
                    
                    <div class="space-y-6">
                        <div>
                            <label for="booking_date" class="block text-gray-300 text-sm font-medium mb-2">Session Date:</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-input w-full" required>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_time" class="block text-gray-300 text-sm font-medium mb-2">Start Time:</label>
                                <input type="time" id="start_time" name="start_time" class="form-input w-full" required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-gray-300 text-sm font-medium mb-2">End Time:</label>
                                <input type="time" id="end_time" name="end_time" class="form-input w-full" required>
                            </div>
                        </div>
                        <p class="text-gray-400 text-sm italic">Note: Sessions are usually booked in 1-hour increments.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
                        <a href="trainers.php" class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg">
                            Back to Trainers
                        </a>
                        <button type="submit" class="action-button bg-red-600 hover:bg-red-700 text-white shadow-lg">
                            Confirm and Proceed to Payment
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-center text-red-400 text-lg">Could not load trainer details.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8 w-full">
        <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Set today's date as the minimum selectable date
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const todayDateString = `${year}-${month}-${day}`;
            
            const bookingDateInput = document.getElementById('booking_date');
            bookingDateInput.min = todayDateString;
        });
    </script>
</body>
</html>