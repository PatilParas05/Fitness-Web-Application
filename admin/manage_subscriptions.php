<?php
session_start();
include ('../conn.php'); 

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php?error=Access_Denied");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = $_SESSION['user_full_name'] ?? 'Admin User';
$message = '';
$message_class = '';
$plans = [];

// --- ACTION HANDLERS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    try {
        if ($_POST['action'] == 'add_plan') {
            $name = trim($_POST['plan_name']);
            $description = trim($_POST['description']);
            $price = trim($_POST['price']);
            $duration = trim($_POST['duration_days']);
            
            if (empty($name) || !is_numeric($price) || !is_numeric($duration) || $duration <= 0) {
                $message = "All fields must be valid. Price must be numeric and Duration must be positive.";
                $message_class = "bg-red-500";
            } else {
                $stmt = $conn->prepare("INSERT INTO `tbl_subscription` (`plan_name`, `description`, `price`, `duration_days`) VALUES (:name, :desc, :price, :duration)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':desc', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':duration', $duration);
                $stmt->execute();
                $message = "Subscription plan added successfully!";
                $message_class = "bg-green-500";
            }
        }
        // Note: Delete action is omitted for core plans, but could be added here.
    } catch (PDOException $e) {
        // Specifically catch the duplicate key error if plan_name is re-used
        if ($e->getCode() == 23000) { 
            $message = "Error: A plan with that name already exists. Please use a unique name.";
        } else {
            error_log("Admin Subscription action error: " . $e->getMessage());
            $message = "Database error during action.";
        }
        $message_class = "bg-red-500";
    }
}

// --- FETCH PLANS ---
try {
    $planStmt = $conn->prepare("SELECT * FROM `tbl_subscription` ORDER BY `price` ASC");
    $planStmt->execute();
    $plans = $planStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching subscription list: " . $e->getMessage());
    $message = "Database error fetching plan list.";
    $message_class = "bg-red-500";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Subscriptions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0a0a0a; 
            min-height: 100vh;
            color: #e2e8f0;
        }
        .admin-nav-item {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .admin-nav-item:hover {
            background-color: #374151;
            color: #ef4444;
        }
        .table-row-hover:hover {
            background-color: #1f2937;
        }
        .form-input {
            background-color: #374151;
            border: 1px solid #4b5563;
            color: #e2e8f0;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            outline: none;
            transition: all 0.3s ease;
            width: 100%;
        }
        .form-input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
        }
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-900 shadow-2xl flex flex-col justify-between p-6 sticky top-0">
            <div>
                <h1 class="text-3xl font-extrabold text-red-500 mb-8">Admin Panel</h1>
                <nav class="space-y-3">
                    <a href="admin_dashboard.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
                        Dashboard
                    </a>
                    <a href="manage_users.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
                        Manage Users
                    </a>
                    <a href="manage_trainers.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
                        Manage Trainers
                    </a>
                    <a href="manage_subscriptions.php" class="admin-nav-item block bg-gray-800 text-red-400 font-semibold">
                        Manage Subscriptions
                    </a>
                     <a href="view_contacts.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
                         View Contacts
                     </a>
                </nav>
            </div>
            <div>
                <a href="../home.php" class="block text-gray-400 hover:text-red-400 transition duration-200 mb-2">← Back to User Dashboard</a>
                <a href="../home.php?action=logout" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-full text-center block transition duration-200">
                    Admin Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="mb-10">
                <h2 class="text-4xl font-bold text-red-500">Manage Subscriptions</h2>
                <p class="text-gray-400 mt-2">View and manage the core subscription plans offered to users.</p>
            </header>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-6 text-center">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- Add New Plan Form -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-xl mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 border-b border-gray-700 pb-2">Add New Subscription Plan</h3>
                <form method="POST" action="manage_subscriptions.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <input type="hidden" name="action" value="add_plan">
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Plan Name</label>
                        <input type="text" name="plan_name" class="form-input" required placeholder="Elite Monthly">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Price ($)</label>
                        <input type="number" name="price" class="form-input" required step="0.01" min="0" placeholder="19.99">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Duration (Days)</label>
                        <input type="number" name="duration_days" class="form-input" required min="1" placeholder="30">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <input type="text" name="description" class="form-input" required placeholder="Full feature access for 30 days.">
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="btn-action bg-red-600 hover:bg-red-700 text-white w-full md:w-auto px-6">Add Plan</button>
                    </div>
                </form>
            </div>


            <!-- Subscription List Table -->
            <div class="bg-gray-800 rounded-lg shadow-xl overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-2/12">Plan Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-5/12">Description</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">Price</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if (empty($plans)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-400">No subscription plans found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($plans as $plan): ?>
                                <tr class="bg-gray-800 table-row-hover">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?= htmlspecialchars($plan['subscription_id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-400"><?= htmlspecialchars($plan['plan_name']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-300"><?= htmlspecialchars($plan['description']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center text-green-400">$<?= number_format($plan['price'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-300"><?= htmlspecialchars($plan['duration_days']) ?> Days</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>