<?php
session_start(); 

include ('./conn.php'); 

// Redirect to login page if user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=fitness_guides.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';
$isPremium = false; // Placeholder for premium status check
$dietPlanItems = [];


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
    
    // Placeholder logic for Premium Access: Assume user is premium if their ID is even.
    // In a real app, you would query tbl_payment or a dedicated user_subscription table for an active plan.
    // NOTE: This logic is for testing. Replace with proper subscription check later.
    if ($userID % 2 == 0) {
        $isPremium = true;
    }


    // --- DIET PLAN LOGIC ---
    if ($isPremium) {
        // Fetch diet plans belonging to the user OR general plans (tbl_user_id IS NULL)
        $dietStmt = $conn->prepare("SELECT * FROM `tbl_diet_plan_item` WHERE `tbl_user_id` = :user_id OR `tbl_user_id` IS NULL ORDER BY `day_of_week` ASC");
        $dietStmt->bindParam(':user_id', $userID);
        $dietStmt->execute();
        
        if ($dietStmt->rowCount() > 0) {
            $dietPlanItems = $dietStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Insert Dummy Diet Plan Items if table is empty
             $conn->exec("INSERT INTO `tbl_diet_plan_item` (`tbl_user_id`, `meal_type`, `description`, `calories`, `day_of_week`) VALUES 
                 (NULL, 'Breakfast', 'Oatmeal with berries and nuts (High fiber)', 350, 'Monday'),
                 (NULL, 'Lunch', 'Grilled chicken salad with light vinaigrette (Lean protein)', 450, 'Monday'),
                 (NULL, 'Dinner', 'Salmon with roasted vegetables (Omega-3 rich)', 550, 'Monday'),
                 (NULL, 'Snack', 'Greek yogurt (Protein boost)', 150, 'Monday')
             ");
             $dietStmt->execute();
             $dietPlanItems = $dietStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }


} catch (PDOException $e) {
    error_log("Error fetching user name in fitness_guides.php: " . $e->getMessage());
    // Only display database error if it affects the guides/diet content
    $message = "Database error: Could not load content.";
    $message_class = "bg-red-500";
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
    $message_class = "bg-red-500";
} elseif (isset($_GET['message'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message']));
    $message_class = "bg-green-500";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Fitness Guides</title>
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
            max-width: 90%;
            width: 1000px;
            margin-left: auto;
            margin-right: auto;
            background-color: #1f2937;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            padding: 3rem 2.5rem;
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
        .guide-section {
            background-color: #1f2937;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            border: 1px solid #4a5568;
            transition: transform 0.3s ease;
        }
        .guide-section:hover {
            transform: translateY(-5px);
        }
        .guide-section h2 {
            color: #ef4444;
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .guide-section h3 {
            color: #e2e8f0;
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .guide-section p, .guide-section ul, .guide-section ol {
            color: #cbd5e0;
            line-height: 1.7;
            margin-bottom: 1rem;
        }
        .guide-section ul, .guide-section ol {
            list-style-type: disc;
            margin-left: 1.5rem;
        }
        .guide-section ol {
            list-style-type: decimal;
        }
        .guide-section li {
            margin-bottom: 0.5rem;
        }
        .highlight {
            color: #fcd34d; 
            font-weight: 600;
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
        .diet-table th, .diet-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #4a5568;
        }
        /* Custom style for the Go Pro banner */
        .pro-banner {
            background: #ef4444; /* red-500 */
            border-radius: 12px;
            padding: 1.5rem 2rem;
            box-shadow: 0 8px 15px rgba(239, 68, 68, 0.3);
            border: 3px solid #fcd34d; /* amber-300 */
        }
        .pro-button {
            background-color: #fcd34d; /* amber-300 */
            color: #1f2937; /* dark text */
            font-weight: 800;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .pro-button:hover {
            background-color: #fbbf24; /* amber-400 */
            transform: scale(1.05);
        }
        .mini-card {
            background-color: #0d0d0d;
            border: 1px solid #4a5568;
            border-radius: 8px;
            padding: 1rem;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .mini-card:hover {
            transform: scale(1.02);
            border-color: #ef4444;
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
                <span class="block mb-2">Knowledge is Power</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">Your Fitness Guides</span>
            </h1>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>
            
            <!-- NEW: Go Pro Banner (Visible only if NOT Premium) -->
            <?php if (!$isPremium): ?>
                <div class="pro-banner flex flex-col md:flex-row justify-between items-center text-white mb-12">
                    <div class="flex items-center mb-4 md:mb-0">
                        <!-- Custom Icon/Symbol (Recreating the look of the reference image) -->
                        <div class="text-3xl mr-4">
                            <span style="color: #fcd34d; font-weight: 900;">&#x2B24;</span>
                        </div>
                        <p class="text-2xl font-extrabold tracking-tight">
                            GO PRO! 
                            <span class="text-lg font-normal block md:inline md:ml-4">
                                Unlock personalized diet plans, advanced analytics, and exclusive content.
                            </span>
                        </p>
                    </div>
                    <a href="subscriptions.php" class="pro-button shadow-xl">
                        View Subscription Plans
                    </a>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 gap-8">
        
                <!-- Personalized Diet Plan Section (Premium Access) -->
                <div class="guide-section">
                    <h2>🍎 My Personalized Diet Plan</h2>
                    <?php if ($isPremium): ?>
                        <p class="text-green-400 font-semibold mb-6">Access Granted! This is your customized daily diet recommendation.</p>

                        <?php if (!empty($dietPlanItems)): ?>
                            <div class="overflow-x-auto">
                                <table class="diet-table min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm">
                                    <thead>
                                        <tr class="bg-gray-700 text-gray-300 uppercase">
                                            <th class="w-1/6">Day</th>
                                            <th class="w-1/6">Meal Type</th>
                                            <th class="w-1/2">Description</th>
                                            <th class="w-1/6">Calories (Est.)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dietPlanItems as $item): ?>
                                            <tr class="hover:bg-gray-700 transition-colors">
                                                <td class="font-medium text-white"><?= htmlspecialchars($item['day_of_week'] ?? 'N/A') ?></td>
                                                <td class="text-red-300 font-medium"><?= htmlspecialchars($item['meal_type']) ?></td>
                                                <td><?= htmlspecialchars($item['description']) ?></td>
                                                <td class="font-semibold"><?= htmlspecialchars(number_format($item['calories'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-sm text-gray-400 italic mt-4">Note: This plan is a template. Always consult a healthcare professional before making major dietary changes.</p>
                        <?php else: ?>
                            <p class="text-gray-400">Your personalized diet plan is being prepared. Please check back later!</p>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Lock message (smaller version inside the feature box) -->
                        <div class="bg-red-900 bg-opacity-30 p-4 rounded-md border border-red-500 text-center">
                            <h3 class="text-red-400 text-xl font-bold mb-3">Premium Feature Locked 🔒</h3>
                            <p class="text-gray-300 mb-4">Upgrade to the **Pro Member** or **Beast Annual** subscription to access personalized diet plans and exclusive content.</p>
                            <a href="subscriptions.php" class="action-button bg-red-600 hover:bg-red-700 text-white shadow-lg inline-block">
                                View Subscription Plans
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- NEW: Section for More Plans (Visible to everyone) -->
                    <h3 class="mt-8 text-2xl font-bold text-white border-t border-gray-700 pt-6">Explore More Plans & Guidance</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        
                        <!-- Card 1: View Premium Plans -->
                        <a href="subscriptions.php" class="mini-card hover:border-yellow-500">
                            <div class="flex items-center space-x-3">
                                <span class="text-yellow-500 text-xl font-bold">&#x2B24;</span>
                                <div>
                                    <p class="text-white font-semibold">Go Pro & Unlock All Diets</p>
                                    <p class="text-xs text-gray-400">Get customized macro-plans and recipes.</p>
                                </div>
                            </div>
                        </a>

                        <!-- Card 2: Book a Nutrition Trainer -->
                        <a href="fitness_guides.php" class="mini-card hover:border-blue-500">
                            <div class="flex items-center space-x-3">
                                <span class="text-blue-500 text-xl font-bold">👤</span>
                                <div>
                                    <p class="text-white font-semibold">Book 1-on-1 Nutrition Coach</p>
                                    <p class="text-xs text-gray-400">Get expert advice tailored to your body.</p>
                                </div>
                            </div>
                        </a>

                        <!-- Card 3: Read Detailed Food Guide -->
                        <a href="fitness_guides.php" class="mini-card hover:border-green-500">
                            <div class="flex items-center space-x-3">
                                <span class="text-green-500 text-xl font-bold">📖</span>
                                <div>
                                    <p class="text-white font-semibold">Detailed Macro Guide</p>
                                    <p class="text-xs text-gray-400">Learn how to calculate your own intake.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
                
                <!-- Existing Guide: Workout Routines -->
                <div class="guide-section">
                    <h2>🏋️ Workout Routines: Build Strength & Stamina</h2>
                    <p>Whether you're aiming for <span class="highlight">weight loss</span>, <span class="highlight">muscle gain</span>, or overall fitness, a structured workout routine is key. Consistency and proper form are paramount.</p>

                    <h3>Full Body Workout (Beginner)</h3>
                    <p>Perform 3 sets of 10-12 repetitions for each exercise, with 60-90 seconds rest between sets.</p>
                    <ol>
                        <li><span class="highlight">Squats:</span> Targets quadriceps, hamstrings, and glutes.</li>
                        <li><span class="highlight">Push-ups:</span> Works chest, shoulders, and triceps. (Modify on knees if needed)</li>
                        <li><span class="highlight">Dumbbell Rows:</span> Strengthens back muscles. (Use light weights or resistance bands)</li>
                        <li><span class="highlight">Plank:</span> Core strength. Hold for 30-60 seconds.</li>
                        <li><span class="highlight">Lunges:</span> Develops leg strength and balance.</li>
                    </ol>

                    <h3>Advanced Strength Training (Example Split)</h3>
                    <p>Focus on 4 sets of 6-8 repetitions for compound movements, 3 sets of 10-12 for isolation, with 90-120 seconds rest.</p>
                    <ul>
                        <li><span class="highlight">Day 1: Chest & Triceps</span> (Bench Press, Incline Dumbbell Press, Cable Flyes, Triceps Pushdowns, Overhead Extension)</li>
                        <li><span class="highlight">Day 2: Back & Biceps</span> (Deadlifts, Pull-ups, Barbell Rows, Lat Pulldowns, Bicep Curls)</li>
                        <li><span class="highlight">Day 3: Legs & Shoulders</span> (Squats, Leg Press, Romanian Deadlifts, Overhead Press, Lateral Raises)</li>
                        <li>Rest or Active Recovery on other days.</li>
                    </ul>
                </div>

                <!-- Existing Guide: Weight Management -->
                <div class="guide-section">
                    <h2>🍎 Weight Management: Lose Fat or Gain Muscle</h2>
                    <p>Achieving your ideal body composition involves a combination of diet, exercise, and lifestyle choices. It's not just about the numbers on the scale.</p>

                    <h3>Weight Loss Strategies</h3>
                    <p>To lose weight, you generally need to be in a <span class="highlight">caloric deficit</span>, meaning you consume fewer calories than you burn.</p>
                    <ul>
                        <li><span class="highlight">Balanced Diet:</span> Focus on whole foods, lean proteins, fruits, vegetables, and healthy fats.</li>
                        <li><span class="highlight">Portion Control:</span> Be mindful of serving sizes.</li>
                        <li><span class="highlight">Hydration:</span> Drink plenty of water throughout the day.</li>
                        <li><span class="highlight">Cardio:</span> Incorporate regular cardiovascular exercises (running, cycling, swimming).</li>
                        <li><span class="highlight">Strength Training:</span> Builds muscle, which boosts metabolism.</li>
                    </ul>

                    <h3>Weight Gain / Muscle Building Strategies</h3>
                    <p>To gain weight, particularly muscle, you need a <span class="highlight">caloric surplus</span> and adequate protein intake.</p>
                    <ul>
                        <li><span class="highlight">Calorie Surplus:</span> Consume more calories than you burn, focusing on nutrient-dense foods.</li>
                        <li><span class="highlight">High Protein Intake:</span> Essential for muscle repair and growth (e.g., chicken, fish, eggs, legumes).</li>
                        <li><span class="highlight">Compound Exercises:</span> Prioritize exercises that work multiple muscle groups (squats, deadlifts, bench press).</li>
                        <li><span class="highlight">Consistent Training:</span> Follow a progressive overload principle to continually challenge muscles.</li>
                        <li><span class="highlight">Adequate Rest:</span> Muscles grow during recovery, so prioritize sleep.</li>
                    </ul>
                </div>

                <!-- Existing Guide: Indian Pranayama -->
                <div class="guide-section">
                    <h2>🧘 Indian Pranayama: Breathwork for Mind & Body</h2>
                    <p>Pranayama is the practice of breath control in yoga. It's a powerful tool for improving physical health, mental clarity, and emotional balance.</p>

                    <h3>Benefits of Pranayama</h3>
                    <ul>
                        <li>Reduces stress and anxiety.</li>
                        <li>Improves lung capacity and respiratory health.</li>
                        <li>Enhances focus and concentration.</li>
                        <li>Boosts energy levels.</li>
                        <li>Calms the nervous system.</li>
                    </ul>

                    <h3>Common Pranayama Techniques</h3>
                    <ol>
                        <li><span class="highlight">Anulom Vilom (Alternate Nostril Breathing):</span>
                            <p>Close right nostril with thumb, inhale through left. Close left with ring finger, exhale through right. Inhale right, exhale left. Repeat.</p>
                        </li>
                        <li><span class="highlight">Kapalbhati (Skull Shining Breath):</span>
                            <p>Forceful exhalations through both nostrils, passive inhalations. Creates warmth and cleanses nasal passages.</p>
                        </li>
                        <li><span class="highlight">Bhramari (Hummixng Bee Breath):</span>
                            <p>Inhale deeply, exhale slowly while making a humming sound, blocking ears with thumbs. Calms the mind.</p>
                        </li>
                        <li><span class="highlight">Ujjayi (Ocean Breath):</span>
                            <p>Constrict the back of the throat slightly during inhalation and exhalation, creating an ocean-like sound. Helps with focus and relaxation.</p>
                        </li>
                    </ol>
                    <p class="text-sm text-gray-400 italic">Always practice Pranayama under the guidance of a certified instructor, especially if you are new to it or have any health conditions.</p>
                </div>
            </div>

            <div class="flex justify-center mt-8">
                <a class="action-button bg-gray-600 hover:bg-gray-700 text-white text-center shadow-lg" href="home.php">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>


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