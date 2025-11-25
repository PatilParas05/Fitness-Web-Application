<?php
session_start();
include './conn.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=goals.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';
$message_class = '';
$goal = null;

if (!isset($_GET['id'])) {
    header("Location: goals.php?error=No_goal_specified.");
    exit();
}

$goalID = $_GET['id'];

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

    $goalStmt = $conn->prepare("SELECT * FROM `tbl_goal` WHERE `goal_id` = :goal_id AND `tbl_user_id` = :user_id");
    $goalStmt->bindParam(':goal_id', $goalID);
    $goalStmt->bindParam(':user_id', $userID);
    $goalStmt->execute();
    
    if ($goalStmt->rowCount() > 0) {
        $goal = $goalStmt->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: goals.php?error=Goal_not_found_or_access_denied.");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error fetching user or goal data in edit_goal.php: " . $e->getMessage());
    $message = "Database error. Please try again.";
    $message_class = "bg-red-500";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_goal') {
    $goalType = trim($_POST['goal_type']);
    $targetValue = trim($_POST['target_value']);
    $currentValue = trim($_POST['current_value']);
    $startDate = trim($_POST['start_date']);
    $endDate = trim($_POST['end_date']);
    $status = trim($_POST['status']);

    if (empty($goalType) || empty($targetValue) || empty($currentValue) || empty($startDate) || empty($endDate) || empty($status)) {
        $message = "All fields are required.";
        $message_class = "bg-red-500";
    } else {
        try {
            $updateStmt = $conn->prepare("UPDATE `tbl_goal` SET `goal_type` = :goal_type, `target_value` = :target_value, `current_value` = :current_value, `start_date` = :start_date, `end_date` = :end_date, `status` = :status WHERE `goal_id` = :goal_id AND `tbl_user_id` = :user_id");
            $updateStmt->bindParam(':goal_type', $goalType);
            $updateStmt->bindParam(':target_value', $targetValue);
            $updateStmt->bindParam(':current_value', $currentValue);
            $updateStmt->bindParam(':start_date', $startDate);
            $updateStmt->bindParam(':end_date', $endDate);
            $updateStmt->bindParam(':status', $status);
            $updateStmt->bindParam(':goal_id', $goalID);
            $updateStmt->bindParam(':user_id', $userID);
            
            if ($updateStmt->execute()) {
                header("Location: goals.php?message=Goal_updated_successfully!");
                exit();
            } else {
                $message = "Failed to update goal. Please try again.";
                $message_class = "bg-red-500";
            }
        } catch (PDOException $e) {
            error_log("Error updating goal: " . $e->getMessage());
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
    <title>Beast Fitness - Edit Goal</title>
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
                <span class="block mb-2">Edit Your Goal</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">Update Your Progress</span>
            </h1>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($goal): ?>
            <form id="edit-goal-form" action="edit_goal.php?id=<?= htmlspecialchars($goal['goal_id']) ?>" method="POST">
                <input type="hidden" name="action" value="update_goal">
                
                <div class="space-y-6">
                    <div>
                        <label for="goal_type" class="block text-gray-300 text-sm font-medium mb-2">Goal Type:</label>
                        <select id="goal_type" name="goal_type" class="form-input w-full" required>
                            <option value="Weight Loss" <?= ($goal['goal_type'] == 'Weight Loss') ? 'selected' : '' ?>>Weight Loss (kg)</option>
                            <option value="Distance" <?= ($goal['goal_type'] == 'Distance') ? 'selected' : '' ?>>Distance (km)</option>
                            <option value="Reps" <?= ($goal['goal_type'] == 'Reps') ? 'selected' : '' ?>>Reps</option>
                            <option value="Custom" <?= ($goal['goal_type'] == 'Custom') ? 'selected' : '' ?>>Custom</option>
                        </select>
                    </div>
                    <div>
                        <label for="target_value" class="block text-gray-300 text-sm font-medium mb-2">Target Value:</label>
                        <input type="number" id="target_value" name="target_value" value="<?= htmlspecialchars($goal['target_value']) ?>" class="form-input w-full" step="0.1" required>
                    </div>
                    <div>
                        <label for="current_value" class="block text-gray-300 text-sm font-medium mb-2">Current Value:</label>
                        <input type="number" id="current_value" name="current_value" value="<?= htmlspecialchars($goal['current_value']) ?>" class="form-input w-full" step="0.1" required>
                    </div>
                    <div>
                        <label for="start_date" class="block text-gray-300 text-sm font-medium mb-2">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($goal['start_date']) ?>" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-gray-300 text-sm font-medium mb-2">End Date:</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($goal['end_date']) ?>" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="status" class="block text-gray-300 text-sm font-medium mb-2">Status:</label>
                        <select id="status" name="status" class="form-input w-full" required>
                            <option value="In Progress" <?= ($goal['status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= ($goal['status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="Abandoned" <?= ($goal['status'] == 'Abandoned') ? 'selected' : '' ?>>Abandoned</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
                    <a href="goals.php" class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg">
                        Cancel
                    </a>
                    <button type="submit" class="action-button bg-red-600 hover:bg-red-700 text-white shadow-lg">
                        Update Goal
                    </button>
                </div>
            </form>
            <?php else: ?>
                <p class="text-center text-red-400 text-lg">Goal not found or you don't have permission to edit it.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8 w-full">
        <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
