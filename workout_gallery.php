<?php
session_start(); 



include ('./conn.php'); 

// Redirect to login page if user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=workout_gallery.php");
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
    error_log("Error fetching user name in workout_gallery.php: " . $e->getMessage());
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
    <title>Beast Fitness - Workout Gallery</title>
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
        .image-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            background-color: #1f2937;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(71, 85, 105, 0.5);
            overflow: hidden;
            color: #e2e8f0;
        }
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
        }
        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .image-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #ef4444;
            margin-bottom: 0.5rem;
        }
        .image-card p {
            color: #cbd5e0;
            font-size: 0.9rem;
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
        <div class="container py-8">
            <h1 class="text-5xl lg:text-6xl font-extrabold text-red-500 mb-12 text-center tracking-tight">
                <span class="block mb-2">Visual Motivation</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">Workout Inspiration Gallery</span>
            </h1>

            <?php if ($message): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Image Card 1: Strength Training -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/FFD1DC/FF0000?text=Weightlifting" alt="Person lifting weights">
                    <div class="p-4">
                        <h3>Strength Training</h3>
                        <p>Build muscle and increase your power with foundational strength exercises like squats, deadlifts, and bench presses. Proper form is key to preventing injury and maximizing gains.</p>
                    </div>
                </div>

                <!-- Image Card 2: Yoga & Flexibility -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/D1E7DD/008000?text=Yoga" alt="Person doing yoga pose">
                    <div class="p-4">
                        <h3>Yoga & Flexibility</h3>
                        <p>Improve your balance, core strength, and overall flexibility. Yoga helps to prevent injuries, relieve stress, and improve your mind-body connection.</p>
                    </div>
                </div>

                <!-- Image Card 3: Cardio Endurance -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/C8E6C9/4CAF50?text=Running" alt="Person running outdoors">
                    <div class="p-4">
                        <h3>Cardio Endurance</h3>
                        <p>Boost your cardiovascular health and stamina with regular cardio exercises. Whether it's a brisk run or a long jog, it's great for your heart and lungs.</p>
                    </div>
                </div>

                <!-- Image Card 4: High-Intensity Training -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/BBDEFB/2196F3?text=Crossfit" alt="People doing crossfit">
                    <div class="p-4">
                        <h3>High-Intensity Training</h3>
                        <p>Challenge your limits with intense, functional movements. CrossFit and other HIIT workouts are designed to improve all aspects of your physical fitness.</p>
                    </div>
                </div>

                <!-- Image Card 5: Aquatic Fitness -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/FFF8DC/FFC107?text=Swimming" alt="Person swimming laps">
                    <div class="p-4">
                        <h3>Aquatic Fitness</h3>
                        <p>Enjoy a low-impact, full-body workout that's easy on the joints. Swimming is a fantastic way to build strength and endurance without the strain of land-based exercises.</p>
                    </div>
                </div>

                <!-- Image Card 6: Outdoor Cycling -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/E1BEE7/9C27B0?text=Cycling" alt="Person cycling on a road">
                    <div class="p-4">
                        <h3>Outdoor Cycling</h3>
                        <p>Get your cardio in while enjoying the outdoors. Cycling is a great way to strengthen your legs and improve your cardiovascular health while exploring new routes.</p>
                    </div>
                </div>

                <!-- Image Card 7: Bodyweight Exercises -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/FFECB3/FF9800?text=Bodyweight" alt="Person doing pushups">
                    <div class="p-4">
                        <h3>Bodyweight Exercises</h3>
                        <p>Train anywhere, anytime, using just your body weight. Push-ups, squats, and planks are fundamental exercises that build strength and muscular endurance.</p>
                    </div>
                </div>

                <!-- Image Card 8: Adventure Hiking -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/DCEDC8/8BC34A?text=Hiking" alt="Person hiking on a trail">
                    <div class="p-4">
                        <h3>Adventure Hiking</h3>
                        <p>Challenge yourself on the trails. Hiking combines cardiovascular exercise with nature exploration, providing a unique and rewarding full-body workout experience.</p>
                    </div>
                </div>

                <!-- Additional Workouts -->
                <!-- Image Card 9: Boxing -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/D1C4E9/512DA8?text=Boxing" alt="Person training boxing">
                    <div class="p-4">
                        <h3>Boxing</h3>
                        <p>Improve your agility, power, and coordination. Boxing is a high-intensity full-body workout that also serves as an excellent stress reliever and a way to build mental fortitude.</p>
                    </div>
                </div>

                <!-- Image Card 10: Circuit Training -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/E3F2FD/1976D2?text=Circuit+Training" alt="People performing circuit training">
                    <div class="p-4">
                        <h3>Circuit Training</h3>
                        <p>Engage in a series of exercises performed one after the other with minimal rest. This method is highly effective for improving both muscular strength and cardiovascular fitness.</p>
                    </div>
                </div>

                <!-- Image Card 11: Rowing -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/B2DFDB/00796B?text=Rowing" alt="Person using a rowing machine">
                    <div class="p-4">
                        <h3>Rowing</h3>
                        <p>Experience a fantastic full-body workout that strengthens your legs, core, and upper body. Rowing is an efficient and low-impact way to burn calories and build endurance.</p>
                    </div>
                </div>

                <!-- Image Card 12: Martial Arts -->
                <div class="image-card p-4 text-center">
                    <img src="https://placehold.co/600x400/D7CCC8/3E2723?text=Martial+Arts" alt="Person practicing martial arts">
                    <div class="p-4">
                        <h3>Martial Arts</h3>
                        <p>Develop discipline, flexibility, and self-defense skills. Martial arts like Karate, Judo, or Taekwondo offer a challenging workout that is also a journey of personal growth.</p>
                    </div>
                </div>
            </div>

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
