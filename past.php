<?php
session_start(); // Always start the session at the very beginning
include ('./conn.php'); // Include the database connection file

// Redirect to login page if user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=past.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';

// Fetch user's name for display in the navigation
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
    error_log("Error fetching user name in past.php: " . $e->getMessage());
    session_destroy();
    header("Location: login.php?error=db_error_fetching_user");
    exit();
}

// Handle Logout action (GET request)
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header("Location: login.php?message=" . urlencode("You have been successfully logged out."));
    exit();
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
    <title>Beast Fitness - Past Journals</title>
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
            width: 1100px;
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
        .journal-entry {
            background-color: #1f2937;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            border: 1px solid #4a5568;
            transition: transform 0.3s ease;
        }
        .journal-entry:hover {
            transform: scale(1.02);
        }
        .journal-entry h2 {
            color: #ef4444;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .journal-entry h3 {
            color: #e2e8f0;
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .journal-entry p, .journal-entry ul {
            color: #cbd5e0;
            line-height: 1.6;
        }
        .journal-entry ul {
            list-style-type: disc;
            margin-left: 1.5rem;
        }
        .journal-entry li {
            margin-bottom: 0.25rem;
        }
        .action-button {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border-radius: 9999px;
            padding: 0.75rem 2rem;
            font-weight: 700;
            text-decoration: none;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
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
                <span class="block mb-2">Your Journey</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">Past Workout Journals</span>
            </h1>

            <?php if ($message): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>

            <section id="journals-list">
                <?php
                    try {
                        $journalsStmt = $conn->prepare("SELECT * FROM `tbl_journal` WHERE `tbl_user_id` = :user_id ORDER BY `log_date` DESC, `time_start` DESC");
                        $journalsStmt->bindParam(':user_id', $userID);
                        $journalsStmt->execute();
                        $journals = $journalsStmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($journals) > 0) {
                            foreach ($journals as $journal) {
                                echo '<div class="journal-entry">';
                                echo '<h2 class="mb-2">' . htmlspecialchars(date('F j, Y', strtotime($journal['log_date']))) . '</h2>';
                                echo '<p class="text-gray-400 mb-3">Time: ' . htmlspecialchars(date('h:i A', strtotime($journal['time_start']))) . ' - ' . htmlspecialchars(date('h:i A', strtotime($journal['time_end']))) . '</p>';

                                $activitiesStmt = $conn->prepare("SELECT * FROM `tbl_activity` WHERE `journal_id` = :journal_id");
                                $activitiesStmt->bindParam(':journal_id', $journal['journal_id']);
                                $activitiesStmt->execute();
                                $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

                                if (count($activities) > 0) {
                                    echo '<h3 class="mb-2">Activities:</h3>';
                                    echo '<div class="space-y-4">';
                                    foreach ($activities as $activity) {
                                        echo '<div class="p-4 bg-gray-700 rounded-md shadow-inner transition-transform duration-200 transform hover:scale-105">';
                                        echo '<p class="text-lg font-semibold text-red-300">' . htmlspecialchars($activity['activity_name']) . '</p>';
                                        echo '<ul class="list-none text-sm text-gray-400 mt-2 space-y-1">';
                                        echo '<li><span class="font-bold text-gray-200">Time:</span> ' . htmlspecialchars($activity['activity_time']) . '</li>';
                                        if (!empty($activity['distance'])) {
                                            echo '<li><span class="font-bold text-gray-200">Distance:</span> ' . htmlspecialchars($activity['distance']) . '</li>';
                                        }
                                        if (!empty($activity['sets']) && !empty($activity['reps'])) {
                                            echo '<li><span class="font-bold text-gray-200">Sets/Reps:</span> ' . htmlspecialchars($activity['sets']) . ' x ' . htmlspecialchars($activity['reps']) . '</li>';
                                        }
                                        if (!empty($activity['note'])) {
                                            echo '<li><span class="font-bold text-gray-200">Note:</span> ' . htmlspecialchars($activity['note']) . '</li>';
                                        }
                                        echo '</ul>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                } else {
                                    echo '<p class="text-center text-gray-500 italic">No specific activities logged for this journal entry.</p>';
                                }
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-center text-gray-400 text-lg">You haven\'t logged any workouts yet. Go to "Add Activity" to start!</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p class="text-red-500">Error retrieving journals: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        error_log("View Journals error: " . $e->getMessage());
                    }
                ?>
            </section>

            <div class="flex justify-center mt-8">
                <a class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg" href="home.php">
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
        // Mobile menu toggling (if needed for this page)
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>
