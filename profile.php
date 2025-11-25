<?php
session_start();
// Redirect to login page if user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php'; 

$userID = $_SESSION['user_id'];
$userData = null;
$message = '';
$message_class = '';

try {
    $stmt = $conn->prepare("SELECT `full_name`, `email`, `contact_number`, `weight`, `height`, `birthday` FROM `tbl_user` WHERE `tbl_user_id` = :user_id");
    $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        session_destroy();
        header("Location: login.php?error=invalid_session");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching user data in profile.php: " . $e->getMessage());
    $message = "Database error. Please try again.";
    $message_class = "bg-red-500";
}

if (isset($_GET['message'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message']));
    $message_class = "bg-green-500";
} elseif (isset($_GET['error'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['error']));
    $message_class = "bg-red-500";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Profile</title>
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
        .profile-card {
            background-color: #1f2937;
            border: 1px solid #374151;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }
        .form-input {
            background-color: #374151;
            border-color: #4b5563;
            color: #ffffff;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
        }
        .btn-primary {
            background-color: #ef4444;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.3);
        }
        .btn-primary:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(239, 68, 68, 0.4);
        }
        .btn-secondary {
            background-color: #4b5563;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #6b7280;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg p-4 shadow-lg sticky top-0 z-20">
        <div class="container mx-auto flex justify-between items-center">
            <a href="home.php" class="text-white text-2xl font-bold rounded-md px-3 py-1 transition-colors duration-200 hover:text-red-400">Beast Fitness</a>
            <button id="mobile-menu-button" class="lg:hidden text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-4 6h4"></path>
                </svg>
            </button>
            <div id="desktop-menu" class="hidden lg:flex space-x-4 items-center">
                <a href="home.php" class="nav-item text-white px-3 py-2 rounded-md font-medium">Dashboard</a>
                <a href="workout.php" class="nav-item text-white px-3 py-2 rounded-md font-medium">Add Activity</a>
                <a href="past.php" class="nav-item text-white px-3 py-2 rounded-md font-medium">My Logged Journals</a>
                <a href="fitness_guides.php" class="nav-item text-white px-3 py-2 rounded-md font-medium">Fitness Guides</a>
                <a href="workout_gallery.php" class="nav-item text-white px-3 py-2 rounded-md font-medium">Workout Gallery</a>
                <a href="profile.php" class="nav-item text-white px-3 py-2 rounded-md font-medium active">Profile</a>
                <a href="home.php?action=logout" class="nav-item text-white bg-red-600 hover:bg-red-700 font-bold py-2 px-6 rounded-full shadow-lg">Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Navigation Menu (Hidden by default) -->
    <div id="mobile-menu" class="lg:hidden bg-gray-800 absolute w-full z-10 hidden shadow-md">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="home.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Dashboard</a>
            <a href="workout.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Add Activity</a>
            <a href="past.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">My Logged Journals</a>
            <a href="fitness_guides.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Fitness Guides</a>
            <a href="workout_gallery.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Workout Gallery</a>
            <a href="profile.php" class="block text-white px-3 py-2 rounded-md text-base font-medium nav-item">Profile</a>
            <a href="home.php?action=logout" class="block text-white bg-red-600 hover:bg-red-700 px-3 py-2 rounded-md text-base font-medium nav-item">Logout</a>
        </div>
    </div>

    <main class="flex-grow container mx-auto p-6">
        <div class="max-w-4xl mx-auto profile-card rounded-lg p-8">
            <h1 class="text-4xl font-bold text-center mb-6 text-red-500">My Profile</h1>
            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form action="update_profile.php" method="POST" class="space-y-6">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-200">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($userData['full_name'] ?? '') ?>" required class="mt-1 block w-full rounded-md border-gray-600 shadow-sm sm:text-sm p-2 form-input">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-200">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required class="mt-1 block w-full rounded-md border-gray-600 shadow-sm sm:text-sm p-2 form-input">
                </div>
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-200">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" value="<?= htmlspecialchars($userData['contact_number'] ?? '') ?>" required class="mt-1 block w-full rounded-md border-gray-600 shadow-sm sm:text-sm p-2 form-input">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-200">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" value="<?= htmlspecialchars($userData['weight'] ?? '') ?>" step="0.1" class="mt-1 block w-full rounded-md border-gray-600 shadow-sm sm:text-sm p-2 form-input">
                    </div>
                    <div>
                        <label for="height" class="block text-sm font-medium text-gray-200">Height (cm)</label>
                        <input type="number" id="height" name="height" value="<?= htmlspecialchars($userData['height'] ?? '') ?>" step="0.1" class="mt-1 block w-full rounded-md border-gray-600 shadow-sm sm:text-sm p-2 form-input">
                    </div>
                    <div>
                        <label for="birthday" class="block text-sm font-medium text-gray-200">Birthday</label>
                        <input type="date" id="birthday" name="birthday" value="<?= htmlspecialchars($userData['birthday'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-600 shadow-sm sm:text-sm p-2 form-input">
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="submit" class="btn-primary text-white font-bold py-2 px-6 rounded-full shadow-lg">
                        Update Profile
                    </button>
                    <a href="home.php" class="btn-secondary text-white font-bold py-2 px-6 rounded-full shadow-lg">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-900 bg-opacity-75 backdrop-filter backdrop-blur-lg text-gray-400 p-4 text-center mt-8">
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
