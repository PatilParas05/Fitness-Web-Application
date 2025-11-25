<?php
session_start();
include './conn.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_to=trainers.php");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = '';
$message = '';
$message_class = '';
$trainers = [];

try {
    // Fetch user details
    $userStmt = $conn->prepare("SELECT `full_name` FROM `tbl_user` WHERE `tbl_user_id` = :user_id");
    $userStmt->bindParam(':user_id', $userID);
    $userStmt->execute();
    if ($userStmt->rowCount() > 0) {
        $row = $userStmt->fetch(PDO::FETCH_ASSOC);
        $displayName = htmlspecialchars($row['full_name']);
    } else {
        session_destroy();
        header("Location: login.php?error=invalid_session");
        exit();
    }

    // Fetch active trainers
    $trainerStmt = $conn->prepare("SELECT `trainer_id`, `full_name`, `specialization`, `hourly_rate` FROM `tbl_trainer` WHERE `is_active` = 1 ORDER BY `full_name` ASC");
    $trainerStmt->execute();
    $trainers = $trainerStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching data in trainers.php: " . $e->getMessage());
    $message = "Error fetching trainer data.";
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
    <title>Beast Fitness - Book a Trainer</title>
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
        .trainer-card {
            background-color: #1f2937;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid #4a5568;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .trainer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
        }
        .trainer-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #3b82f6;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
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
                <span class="block mb-2">Find Your Coach</span>
                <span class="block text-3xl lg:text-4xl text-white font-semibold">Book a Personal Trainer</span>
            </h1>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-4 text-center" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($trainers)): ?>
                    <?php foreach ($trainers as $trainer): ?>
                        <div class="trainer-card text-center flex flex-col items-stretch">
                            <!-- Placeholder Avatar -->
                            <div class="trainer-avatar">
                                <?= strtoupper(substr($trainer['full_name'], 0, 2)) ?>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-white mb-2"><?= htmlspecialchars($trainer['full_name']) ?></h3>
                            <p class="text-red-400 font-semibold mb-4"><?= htmlspecialchars($trainer['specialization']) ?></p>
                            
                            <div class="text-gray-400 text-sm mb-6 flex-grow">
                                <p>Hourly Rate: <span class="text-white font-bold">$<?= number_format($trainer['hourly_rate'], 2) ?></span></p>
                                <p>Availability: <span class="text-green-400">Contact for Schedule</span></p>
                            </div>

                            <a href="book_session.php?trainer_id=<?= $trainer['trainer_id'] ?>" class="action-button bg-green-600 hover:bg-green-700 text-white mt-auto">
                                Book Session
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center">
                        <p class="text-gray-400 text-lg">No trainers are currently active. Please check back later!</p>
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

    <script>
        // No specific JS needed for this page other than the default navigation toggling (if implemented)
    </script>
</body>
</html>