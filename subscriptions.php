<?php
session_start();
include './conn.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=subscriptions.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';
$message_class = '';
$plans = [];

try {
    // Fetch user details
    $userStmt = $conn->prepare("SELECT `full_name` FROM `tbl_user` WHERE `tbl_user_id` = :user_id");
    $userStmt->bindParam(':user_id', $userID);
    $userStmt->execute();
    $displayName = $userStmt->fetchColumn() ?: 'User';
    
    // Fetch subscription plans from the new table
    $planStmt = $conn->prepare("SELECT * FROM `tbl_subscription` ORDER BY `price` ASC");
    $planStmt->execute();
    
    if ($planStmt->rowCount() > 0) {
        $plans = $planStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Fallback: Insert dummy data if the table is empty (helpful for initial setup)
        $conn->exec("INSERT INTO `tbl_subscription` (`plan_name`, `description`, `price`, `duration_days`) VALUES 
            ('Basic Access', 'Access essential workout logs and guides for 30 days.', 9.99, 30),
            ('Pro Member', 'Full access to all diet plans, premium guides, and advanced tracking for 90 days.', 29.99, 90),
            ('Beast Annual', 'Ultimate yearly access, including exclusive pre-release content and priority support for 365 days.', 99.99, 365)
        ");
        $planStmt->execute();
        $plans = $planStmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log("Error fetching subscription data in subscriptions.php: " . $e->getMessage());
    $message = "Error fetching subscription plans.";
    $message_class = "bg-red-500";
}

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
    <title>Beast Fitness - Subscriptions</title>
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
        .plan-card {
            background-color: #1f2937;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            border: 1px solid #4a5568;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .plan-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }
        .plan-card.highlight {
            border-color: #ef4444;
            background: linear-gradient(to bottom right, #1f2937, #374151);
        }
        .plan-price {
            font-size: 3rem;
            font-weight: 800;
            color: #ef4444;
            line-height: 1;
        }
        .action-button {
            padding: 0.9rem 2rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .feature-list li {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: #cbd5e0;
        }
        .feature-list svg {
            color: #10b981;
            margin-right: 0.5rem;
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
            <h1 class="text-5xl lg:text-6xl font-extrabold text-red-500 mb-4 text-center tracking-tight">
                <span class="block mb-2">Go Pro</span>
            </h1>
            <p class="text-3xl lg:text-4xl text-white font-semibold mb-12 text-center">Unlock Your Full Potential with Premium Plans</p>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-8 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (!empty($plans)): ?>
                    <?php foreach ($plans as $index => $plan): ?>
                        <div class="plan-card <?= ($index == 1) ? 'highlight border-4' : '' ?> flex flex-col">
                            <h3 class="text-3xl font-bold text-white mb-2 text-center"><?= htmlspecialchars($plan['plan_name']) ?></h3>
                            <p class="text-gray-400 text-center mb-4"><?= htmlspecialchars($plan['duration_days']) ?> Days Access</p>
                            
                            <div class="text-center mb-6">
                                <span class="plan-price">$<?= number_format($plan['price'], 2) ?></span>
                            </div>

                            <p class="text-gray-300 text-center mb-6 flex-grow"><?= htmlspecialchars($plan['description']) ?></p>

                            <ul class="feature-list mb-8 text-sm">
                                <li>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Workout Logging & Goals
                                </li>
                                <li>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Fitness Guides Access
                                </li>
                                <?php if ($plan['duration_days'] >= 90): // Features for Pro and Annual ?>
                                <li>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Exclusive Diet Plans
                                </li>
                                <li>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Advanced Progress Charts
                                </li>
                                <?php endif; ?>
                                <?php if ($plan['duration_days'] >= 365): // Features for Annual only ?>
                                <li>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Priority Support & Content
                                </li>
                                <?php endif; ?>
                            </ul>

                            <a href="payment_page.php?subscription_id=<?= $plan['subscription_id'] ?>" class="action-button bg-red-600 hover:bg-red-700 text-white text-center shadow-lg mt-auto">
                                Get Started
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-1 md:col-span-3 text-center">
                        <p class="text-gray-400 text-lg">No subscription plans found. Please contact support.</p>
                    </div>
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
</body>
</html>