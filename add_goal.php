<?php
session_start();
include './conn.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=add_goal.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';
$message_class = '';

try {
    $stmt = $conn->prepare("SELECT `full_name` FROM `tbl_user` WHERE `tbl_user_id` = :user_id");
    $stmt->bindParam(':user_id', $userID);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $displayName = htmlspecialchars($row['full_name']);
    } else {
        session_destroy();
        header("Location: login.php?error=invalid_session");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching user name in add_goal.php: " . $e->getMessage());
    session_destroy();
    header("Location: login.php?error=db_error_fetching_user");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_goal') {
    $goalType = trim($_POST['goal_type']);
    $targetValue = trim($_POST['target_value']);
    $startDate = trim($_POST['start_date']);
    $endDate = trim($_POST['end_date']);

    if (empty($goalType) || empty($targetValue) || empty($startDate) || empty($endDate)) {
        $message = "All fields are required.";
        $message_class = "bg-red-500";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO `tbl_goal` (`tbl_user_id`, `goal_type`, `target_value`, `start_date`, `end_date`) VALUES (:user_id, :goal_type, :target_value, :start_date, :end_date)");
            $stmt->bindParam(':user_id', $userID);
            $stmt->bindParam(':goal_type', $goalType);
            $stmt->bindParam(':target_value', $targetValue);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            
            if ($stmt->execute()) {
                header("Location: goals.php?message=Goal_added_successfully!");
                exit();
            } else {
                $message = "Failed to add goal. Please try again.";
                $message_class = "bg-red-500";
            }
        } catch (PDOException $e) {
            error_log("Error adding goal: " . $e->getMessage());
            $message = "Database error: " . htmlspecialchars($e->getMessage());
            $message_class = "bg-red-500";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Add Goal</title>
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
            width: 600px;
            color: #e2e8f0;
            border: 1px solid rgba(71, 85, 105, 0.5);
            transition: transform 0.3s ease;
        }
        .container:hover {
            transform: translateY(-5px);
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
                <span class="block mb-2">Define Your Journey</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">Set a New Fitness Goal</span>
            </h1>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>

            <form id="add-goal-form" action="add_goal.php" method="POST">
                <input type="hidden" name="action" value="add_goal">
                
                <div class="space-y-6">
                    <div>
                        <label for="goal_type" class="block text-gray-300 text-sm font-medium mb-2">Goal Type:</label>
                        <select id="goal_type" name="goal_type" class="form-input w-full" required>
                            <option value="">Select a goal type...</option>
                            <option value="Weight Loss">Weight Loss (kg)</option>
                            <option value="Distance">Distance (km)</option>
                            <option value="Reps">Reps</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label for="target_value" class="block text-gray-300 text-sm font-medium mb-2">Target Value:</label>
                        <input type="number" id="target_value" name="target_value" class="form-input w-full" step="0.1" required placeholder="e.g., 10 for kg or km">
                    </div>
                    <div>
                        <label for="start_date" class="block text-gray-300 text-sm font-medium mb-2">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-gray-300 text-sm font-medium mb-2">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="form-input w-full" required>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
                    <a href="goals.php" class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg">
                        Back to Goals
                    </a>
                    <button type="submit" class="action-button bg-red-600 hover:bg-red-700 text-white shadow-lg">
                        Create Goal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8 w-full">
        <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById('start_date').value = `${year}-${month}-${day}`;
        });
    </script>
</body>
</html>
