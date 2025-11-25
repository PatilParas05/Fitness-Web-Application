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
    error_log("Error fetching user name in goals.php: " . $e->getMessage());
    session_destroy();
    header("Location: login.php?error=db_error_fetching_user");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header("Location: login.php?message=" . urlencode("You have been successfully logged out."));
    exit();
}

if (isset($_GET['error'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['error']));
    $message_class = "bg-red-500";
} elseif (isset($_GET['message'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message']));
    $message_class = "bg-green-500";
}

try {
    // Note: The original SQL query did not handle the division by zero case if target_value is 0.
    // We will calculate progress in PHP to handle this safely.
    $goalsStmt = $conn->prepare("SELECT * FROM `tbl_goal` WHERE `tbl_user_id` = :user_id ORDER BY `end_date` ASC");
    $goalsStmt->bindParam(':user_id', $userID);
    $goalsStmt->execute();
    $goals = $goalsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error retrieving goals: " . htmlspecialchars($e->getMessage());
    $message_class = "bg-red-500";
    error_log("View Goals error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - My Goals</title>
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
            padding: 1rem;
        }
        .container {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            color: #e2e8f0;
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
        .goal-card {
            background-color: #1f2937;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            border: 1px solid #4a5568;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .goal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
        }
        .progress-bar {
            background-color: #4a5568;
            border-radius: 9999px;
            height: 1rem;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease-in-out;
        }
        .progress-status-completed {
            background-color: #10b981; /* Green */
        }
        .progress-status-in-progress {
            background-color: #3b82f6; /* Blue */
        }
        .progress-status-abandoned {
            background-color: #ef4444; /* Red */
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
        <div class="container py-8">
            <h1 class="text-5xl lg:text-6xl font-extrabold text-red-500 mb-12 text-center tracking-tight">
                <span class="block mb-2">My Goals</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">Track Your Progress</span>
            </h1>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>
            
            <div class="flex justify-end mb-8">
                <a href="add_goal.php" class="action-button bg-green-600 hover:bg-green-700 text-white shadow-lg">Add New Goal</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($goals)): ?>
                    <?php foreach ($goals as $goal): ?>
                        <?php
                            // Calculate progress safely (avoid division by zero)
                            if ((float)$goal['target_value'] > 0) {
                                $progress = ((float)$goal['current_value'] / (float)$goal['target_value']) * 100;
                            } else {
                                $progress = 0; // Or 100 if target is 0, but 0 is safer for most fitness goals
                            }
                            
                            $progress_percent = min(100, max(0, round($progress))); // Cap progress at 100%
                            
                            $progress_color = '';
                            switch ($goal['status']) {
                                case 'Completed':
                                    $progress_color = 'progress-status-completed';
                                    break;
                                case 'Abandoned':
                                    $progress_color = 'progress-status-abandoned';
                                    break;
                                default:
                                    $progress_color = 'progress-status-in-progress';
                                    break;
                            }
                        ?>
                        <div class="goal-card flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-red-400 mb-2"><?= htmlspecialchars($goal['goal_type']) ?></h3>
                                
                                <p class="text-gray-400 text-sm mb-1">Current: <span class="font-bold text-white"><?= htmlspecialchars($goal['current_value']) ?></span> / Target: <span class="font-bold text-white"><?= htmlspecialchars($goal['target_value']) ?></span></p>
                                
                                <!-- PROGRESS BAR START -->
                                <div class="progress-bar my-3">
                                    <div class="progress-bar-fill <?= $progress_color ?>" style="width: <?= $progress_percent ?>%;"></div>
                                </div>
                                <p class="text-sm font-semibold text-right mb-4" style="color: <?= $progress_percent >= 100 ? '#10b981' : '#f87171' ?>;">
                                    <?= $progress_percent ?>% Complete
                                </p>
                                <!-- PROGRESS BAR END -->
                                
                                <p class="text-xs text-center mt-2 text-gray-400 font-bold">Status: <span class="rounded-full px-2 py-0.5 text-xs text-white" style="background-color: 
                                    <?php 
                                        if ($goal['status'] == 'Completed') echo '#10b981';
                                        elseif ($goal['status'] == 'Abandoned') echo '#ef4444';
                                        else echo '#3b82f6';
                                    ?>;"><?= htmlspecialchars($goal['status']) ?></span></p>
                                <p class="text-gray-500 text-xs mt-2">Start Date: <?= htmlspecialchars($goal['start_date']) ?></p>
                                <p class="text-gray-500 text-xs">End Date: <?= htmlspecialchars($goal['end_date']) ?></p>
                            </div>
                            <div class="flex justify-end space-x-2 mt-4">
                                <a href="edit_goal.php?id=<?= $goal['goal_id'] ?>" class="text-blue-400 hover:text-blue-300 font-medium text-sm">Edit</a>
                                <a href="delete_goal.php?id=<?= $goal['goal_id'] ?>" onclick="return confirm('Are you sure you want to delete this goal?');" class="text-red-400 hover:text-red-300 font-medium text-sm">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-400 text-lg col-span-3">No goals found. Add a new goal to get started!</p>
                <?php endif; ?>
            </div>

            <!-- Back to Dashboard Button -->
            <div class="flex justify-center mt-8">
                <a href="home.php" class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8 w-full">
        <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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