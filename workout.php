<?php
session_start(); 



include ('./conn.php'); 

// Redirect to login page if user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=workout.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';


try {
    $stmt = $conn->prepare("SELECT `full_name` FROM `tbl_user` WHERE `tbl_user_id` = :user_id");
    $stmt->bindParam(':user_id', $userID);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $displayName = htmlspecialchars($row['full_name']);
    } else {
        // User ID in session but not found in DB, clear session and redirect
        session_destroy();
        header("Location: login.php?error=invalid_session");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching user name in workout.php: " . $e->getMessage());
    session_destroy();
    header("Location: login.php?error=db_error_fetching_user");
    exit();
}

// --- Handle POST request for adding a new activity ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_activity') {
    $date = trim($_POST['workout_date']);
    $timeStart = trim($_POST['time_start']);
    $timeEnd = trim($_POST['time_end']);

    try {
        // Insert into tbl_journal (main workout entry)
        $stmt = $conn->prepare("INSERT INTO `tbl_journal` (`tbl_user_id`, `log_date`, `time_start`, `time_end`) VALUES (:user_id, :log_date, :time_start, :time_end)");
        $stmt->bindParam(':user_id', $userID);
        $stmt->bindParam(':log_date', $date);
        $stmt->bindParam(':time_start', $timeStart);
        $stmt->bindParam(':time_end', $timeEnd);
        $stmt->execute();
        $journalID = $conn->lastInsertId(); // Get the ID of the newly inserted journal entry

        // Loop through and insert individual activities associated with this journal entry
        for ($i = 1; isset($_POST['activity_' . $i]); $i++) {
            $activity = !empty(trim($_POST['activity_' . $i])) ? trim($_POST['activity_' . $i]) : null;
            $time = !empty(trim($_POST['activity_time_' . $i])) ? trim($_POST['activity_time_' . $i]) : null;
            $distance = !empty(trim($_POST['distance_' . $i])) ? trim($_POST['distance_' . $i]) : null;
            $set = !empty(trim($_POST['sets_' . $i])) ? trim($_POST['sets_' . $i]) : null;
            $rep = !empty(trim($_POST['reps_' . $i])) ? trim($_POST['reps_' . $i]) : null;
            $note = !empty(trim($_POST['note_' . $i])) ? trim($_POST['note_' . $i]) : null;

            if ($activity !== null) {
                $activityStmt = $conn->prepare("INSERT INTO `tbl_activity` (`journal_id`, `activity_name`, `activity_time`, `distance`, `sets`, `reps`, `note`) VALUES (:journal_id, :activity_name, :activity_time, :distance, :sets, :reps, :note)");
                $activityStmt->bindParam(':journal_id', $journalID);
                $activityStmt->bindParam(':activity_name', $activity);
                $activityStmt->bindParam(':activity_time', $time);
                $activityStmt->bindParam(':distance', $distance);
                $activityStmt->bindParam(':sets', $set);
                $activityStmt->bindParam(':reps', $rep);
                $activityStmt->bindParam(':note', $note);
                $activityStmt->execute();
            }
        }
        $message = "Workout journal added successfully!";
        header("Location: home.php?message=" . urlencode($message)); // Redirect to dashboard with success message
        exit();
    } catch (PDOException $e) {
        $message = "Error adding workout: " . htmlspecialchars($e->getMessage());
        error_log("Add activity POST error: " . $e->getMessage());
    }
}

// Handle messages from GET parameters
if (isset($_GET['error'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['error']));
} elseif (isset($_GET['message'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message']));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Log Workout</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font: Inter -->
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
            width: 1100px;
            color: #e2e8f0;
            border: 1px solid rgba(71, 85, 105, 0.5);
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
        .table-header {
            background-color: #374151;
            color: #e2e8f0;
            font-weight: 700;
            padding: 1rem 0.75rem;
            text-align: center;
            border-bottom: 2px solid #4a5568;
            font-size: 0.95rem;
        }
        .table-row-input {
            @apply flex-grow form-input w-full px-3 py-2 text-sm;
            background-color: #2d3748;
            border: 1px solid #4a5568;
            border-radius: 0.5rem;
        }
        .table-row-input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.4);
            background-color: #2c313d;
        }
        .table-row-cell {
            padding: 0.75rem 0.5rem;
            border-right: 1px solid #4a5568;
        }
        .table-row-cell:last-child {
            border-right: none;
        }
        tr:nth-child(even) {
            background-color: #374151;
        }
        tr:hover {
            background-color: #4a5568;
        }
        .nav-item {
            transition: all 0.3s ease-in-out;
            position: relative;
        }
        .nav-item::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            display: block;
            margin-top: 5px;
            right: 0;
            background: #f87171;
            transition: width 0.4s ease;
            -webkit-transition: width 0.4s ease;
        }
        .nav-item:hover::after, .nav-item.active::after {
            width: 100%;
            left: 0;
            background: #f87171;
        }
        .auth-nav-button {
            background-color: #ef4444;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            transition: background-color 0.3s ease-in-out, transform 0.3s ease;
            margin-left: 0.5rem;
        }
        .auth-nav-button:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg p-4 shadow-lg w-full">
        <div class="container mx-auto flex justify-between items-center">
            <a href="home.php" class="text-white text-2xl font-bold rounded-md px-3 py-1 transition-colors duration-200 hover:text-red-400">Beast Fitness</a>
            
            <div class="flex items-center">
                <?php if ($userID): ?>
                    <span class="text-white mr-4">Welcome, <?= $displayName ?>!</span>
                    <a href="home.php?action=logout" class="auth-nav-button">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="auth-nav-button">Login</a>
                    <a href="signup.php" class="auth-nav-button">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="container">
            <h1 class="text-5xl lg:text-6xl font-extrabold text-red-500 mb-12 text-center tracking-tight">
                <span class="block mb-2">Unleash Your Power</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">What did you do today?</span>
            </h1>

            <?php if ($message): ?>
                <div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>

            <form id="workout-form" action="workout.php" method="POST">
                <input type="hidden" name="action" value="add_activity">
                <!-- Date and Time Fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div>
                        <label for="workout_date" class="block text-gray-300 text-sm font-medium mb-2">Date:</label>
                        <input type="date" id="workout_date" name="workout_date" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="time_start" class="block text-gray-300 text-sm font-medium mb-2">Time Start:</label>
                        <input type="time" id="time_start" name="time_start" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="time_end" class="block text-gray-300 text-sm font-medium mb-2">Time End:</label>
                        <input type="time" id="time_end" name="time_end" class="form-input w-full" required>
                    </div>
                </div>

                <!-- Dynamic Activity Rows -->
                <div class="overflow-x-auto mb-8">
                    <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden">
                        <thead>
                            <tr>
                                <th class="table-header w-[25%]">Activity:</th>
                                <th class="table-header w-[10%]">Time:</th>
                                <th class="table-header w-[10%]">Dist:</th>
                                <th class="table-header w-[10%]">Set:</th>
                                <th class="table-header w-[10%]">Reps:</th>
                                <th class="table-header w-[25%]">Note:</th>
                                <th class="table-header w-auto">Action:</th>
                            </tr>
                        </thead>
                        <tbody id="activity-rows">
                            <!-- Initial row will be added by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center mb-10">
                    <button type="button" id="add-activity-btn" class="action-button bg-blue-500 hover:bg-blue-600 text-white shadow-lg">
                        Add Activity
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row justify-between gap-4">
                    <a href="home.php" class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg">
                        Back to Dashboard
                    </a>
                    <button type="submit" class="action-button bg-red-600 hover:bg-red-700 text-white shadow-lg">
                        Submit Journal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8 w-full">
        <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const activityRows = document.getElementById('activity-rows');
            const addActivityBtn = document.getElementById('add-activity-btn');
            const workoutForm = document.getElementById('workout-form');

            let activityCount = 0;

            // Function to add a new activity row
            function addActivityRow() {
                activityCount++;
                const row = document.createElement('tr');
                row.classList.add('border-t', 'border-gray-700');
                row.innerHTML = `
                    <td class="table-row-cell">
                        <input type="text" name="activity_${activityCount}" class="table-row-input" placeholder="Activity Name">
                    </td>
                    <td class="table-row-cell">
                        <input type="text" name="activity_time_${activityCount}" class="table-row-input" placeholder="e.g., 30m">
                    </td>
                    <td class="table-row-cell">
                        <input type="text" name="distance_${activityCount}" class="table-row-input" placeholder="e.g., 5km">
                    </td>
                    <td class="table-row-cell">
                        <input type="number" name="sets_${activityCount}" class="table-row-input" value="1" min="1">
                    </td>
                    <td class="table-row-cell">
                        <input type="number" name="reps_${activityCount}" class="table-row-input" value="10" min="0">
                    </td>
                    <td class="table-row-cell">
                        <input type="text" name="note_${activityCount}" class="table-row-input" placeholder="Optional notes">
                    </td>
                    <td class="table-row-cell text-center">
                        <button type="button" class="remove-activity-btn bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm shadow-md transition-colors duration-200">
                            Remove
                        </button>
                    </td>
                `;
                activityRows.appendChild(row);

                // Add event listener to the new remove button
                row.querySelector('.remove-activity-btn').addEventListener('click', function() {
                    // Ensure at least one row remains
                    if (activityRows.children.length > 1) {
                        row.remove();
                    } else {
                        alert("You must have at least one activity row.");
                    }
                });
            }

            // Add initial row on page load
            addActivityRow();

            // Event listener for "Add Activity" button
            addActivityBtn.addEventListener('click', addActivityRow);

            // Set today's date as default
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById('workout_date').value = `${year}-${month}-${day}`;
        });
    </script>
</body>
</html>
