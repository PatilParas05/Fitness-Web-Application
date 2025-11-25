<?php
session_start(); 



include ('./conn.php'); 

// Redirect to login page if user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=home.php");
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
    error_log("Error fetching user name in home.php: " . $e->getMessage());
    session_destroy();
    header("Location: login.php?error=db_error_fetching_user");
    exit();
}

// Handle messages from GET parameters (e.g., after redirect from login/activity submission)
if (isset($_GET['error'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['error']));
} elseif (isset($_GET['message'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message']));
}


if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy(); 
    setcookie(session_name(), '', time() - 3600, '/'); 
    // Corrected message for logout
    header("Location: login.php?message=" . urlencode("You have been successfully logged out."));
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom right, #0a0a0a, #1a1a1a);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        .card-item, .image-card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            background-color: #1f2937;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(71, 85, 105, 0.5);
        }
        .card-item:hover, .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
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
        /* New style for the Go Pro card to make it stand out */
        .pro-card {
            background: linear-gradient(to top left, #ef4444, #dc2626); /* Red/Crimson Gradient */
            border: 3px solid #fcd34d; /* Yellow border for emphasis */
            color: white;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .pro-card:hover {
            background: linear-gradient(to top left, #dc2626, #b91c1c);
            transform: scale(1.02);
            box-shadow: 0 15px 40px rgba(239, 68, 68, 0.7);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navigation Bar -->
    <nav class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg p-4 shadow-lg sticky top-0 z-20">
        <div class="container mx-auto flex justify-between items-center">
            <a href="home.php" class="text-white text-2xl font-bold rounded-md px-3 py-1 transition-colors duration-200 hover:text-red-400">Beast Fitness</a>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="lg:hidden text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-4 6h4"></path>
                </svg>
            </button>

            <!-- Desktop Navigation Links (Cleaned up spacing) -->
            <div id="desktop-menu" class="hidden lg:flex space-x-3 items-center">
                <a href="home.php" class="nav-item text-white px-2 py-2 rounded-md font-medium active text-sm lg:text-base">Dashboard</a>
                <a href="workout.php" class="nav-item text-white px-2 py-2 rounded-md font-medium text-sm lg:text-base">Add Activity</a>
                <a href="past.php" class="nav-item text-white px-2 py-2 rounded-md font-medium text-sm lg:text-base">My Logged Journals</a>
                <a href="trainers.php" class="nav-item text-white px-2 py-2 rounded-md font-medium text-sm lg:text-base">Book Trainer</a>
                <a href="subscriptions.php" class="nav-item text-white px-2 py-2 rounded-md font-medium text-sm lg:text-base">Subscriptions</a>
                <a href="workout_gallery.php" class="nav-item text-white px-2 py-2 rounded-md font-medium text-sm lg:text-base">Workout Gallery</a>
                <a href="profile.php" class="nav-item text-white px-2 py-2 rounded-md font-medium text-sm lg:text-base">Profile</a>
                
                <?php if (isset($_SESSION['user_id'])): // Display if logged in ?>
                    <span class="text-white ml-4 text-sm lg:text-base">Welcome, <?= $displayName ?>!</span>
                    <a href="home.php?action=logout" class="auth-nav-button text-sm lg:text-base">Logout</a>
                <?php else: // Display if not logged in (should be redirected, but for completeness) ?>
                    <a href="login.php" class="auth-nav-button text-sm lg:text-base">Login</a>
                    <a href="signup.php" class="auth-nav-button text-sm lg:text-base">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>


    <div id="mobile-menu" class="lg:hidden bg-gray-800 absolute w-full z-10 hidden shadow-md">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="home.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item active">Dashboard</a>
            <a href="workout.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Add Activity</a>
            <a href="past.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">My Logged Journals</a>
            <a href="goals.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">My Goals</a>
            <a href="trainers.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Book Trainer</a>
            <a href="subscriptions.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Subscriptions</a>
            <a href="fitness_guides.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Fitness Guides</a>
            <a href="workout_gallery.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Workout Gallery</a>
            <a href="profile.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Profile</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="block text-white px-3 py-2 rounded-md text-base font-medium">Welcome, <?= $displayName ?>!</span>
                <a href="home.php?action=logout" class="block text-white bg-red-600 hover:bg-red-700 px-3 py-2 rounded-md text-base font-medium nav-item">Logout</a>
            <?php else: ?>
                <a href="login.php" class="block text-white bg-blue-500 hover:bg-blue-600 px-3 py-2 rounded-md text-base font-medium nav-item">Login</a>
                <a href="signup.php" class="block text-white bg-green-500 hover:bg-green-600 px-3 py-2 rounded-md text-base font-medium nav-item">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>


    <main id="dashboard-section" class="flex-grow container mx-auto p-6">
        <?php if ($message): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $message ?></span>
            </div>
        <?php endif; ?>

        <h1 class="text-4xl font-bold text-red-500 mb-8 text-center">Welcome Back, <?= $displayName ?>!</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- NEW CARD 0: GO PRO / SUBSCRIPTION - ATTRACTIVE REACH UI -->
            <a href="subscriptions.php" class="col-span-1 md:col-span-3 pro-card p-6 flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-8 cursor-pointer transition duration-300 rounded-xl">
                <div class="flex items-center space-x-4">
                    <!-- Star Icon -->
                    <svg class="w-12 h-12 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.5a1.5 1.5 0 011.087 0l2.675 1.621c.854.516 1.487 1.42 1.487 2.457v3.834c0 1.037-.633 1.94-1.487 2.457l-2.675 1.621a1.5 1.5 0 01-1.087 0l-2.675-1.621c-.854-.517-1.487-1.42-1.487-2.457V6.582c0-1.037.633-1.94 1.487-2.457l2.675-1.621z"></path></svg>
                    <h2 class="text-3xl font-extrabold text-white sm:text-4xl">GO PRO!</h2>
                </div>
                <div class="text-center sm:text-left">
                    <p class="text-gray-200 text-lg">Unlock personalized diet plans, advanced analytics, and exclusive content.</p>
                </div>
                <div class="flex-shrink-0">
                    <span class="mt-4 text-sm font-semibold py-2 px-6 bg-yellow-400 text-red-900 rounded-full shadow-lg hover:bg-yellow-300 transition duration-200 transform hover:scale-105">View Subscription Plans</span>
                </div>
            </a>
            
            <!-- Card 1: Log New Workout -->
            <div class="card-item p-6 flex flex-col items-center text-center">
                <div class="bg-indigo-300 text-indigo-700 rounded-full p-4 mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-2">Log New Workout</h2>
                <p class="text-gray-400 mb-4">Record your latest exercises, sets, and reps.</p>
                <a href="workout.php" class="mt-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200">Start Logging</a>
            </div>

            <!-- Card 2: My Logged Journals -->
            <div class="card-item p-6 flex flex-col items-center text-center">
                <div class="bg-green-300 text-green-700 rounded-full p-4 mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-2">My Logged Journals</h2>
                <p class="text-gray-400 mb-4">Review your past workout entries and track your performance.</p>
                <a href="past.php" class="mt-auto bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200">View My Logs</a>
            </div>
            
            <!-- Card 3: Book a Trainer -->
            <div class="card-item p-6 flex flex-col items-center text-center">
                <div class="bg-red-300 text-red-700 rounded-full p-4 mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-4M7 4h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2zm0 0l2-2m-2 2l-2-2m2 2v2m0 0v2m0 0v2m0 0v2m0 0v2m0 0v2"></path></svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-2">Book a Trainer</h2>
                <p class="text-gray-400 mb-4">Find and schedule a session with a certified personal trainer.</p>
                <a href="trainers.php" class="mt-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200">View Trainers</a>
            </div>

            <!-- Card 4: My Goals -->
            <div class="card-item p-6 flex flex-col items-center text-center">
                <div class="bg-purple-300 text-purple-700 rounded-full p-4 mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-2 2h4m-4 0v-4m4 4h2m-2 0V9a2 2 0 012-2h2a2 2 0 012 2v6a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4a2 2 0 012-2z"></path></svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-2">My Goals</h2>
                <p class="text-gray-400 mb-4">Set and track your fitness goals and visualize your progress.</p>
                <a href="goals.php" class="mt-auto bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200">View Goals</a>
            </div>

            <!-- Card 5: Fitness Guides -->
            <div class="card-item p-6 flex flex-col items-center text-center">
                <div class="bg-yellow-300 text-yellow-700 rounded-full p-4 mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.205 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.523 5.795 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.795 5 16.5 5c1.705 0 3.332.477 4.5 1.253v13C19.832 18.523 18.205 18 16.5 18c-1.705 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-2">Fitness Guides</h2>
                <p class="text-gray-400 mb-4">Explore workout routines, diet tips, and wellness information.</p>
                <a href="fitness_guides.php" class="mt-auto bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200">Read Guides</a>
            </div>

            <!-- Card 6: Workout Gallery -->
            <div class="card-item p-6 flex flex-col items-center text-center">
                <div class="bg-blue-300 text-blue-700 rounded-full p-4 mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-2">Workout Gallery</h2>
                <p class="text-gray-400 mb-4">Explore a collection of inspiring workout images.</p>
                <a href="workout_gallery.php" class="mt-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200">View Gallery</a>
            </div>
            
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8">
        <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
    </footer>

    <script>
        // JavaScript for mobile menu toggling
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