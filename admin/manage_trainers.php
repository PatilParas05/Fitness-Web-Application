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
$trainers = [];

// --- ACTION HANDLERS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    try {
        if ($_POST['action'] == 'add_trainer') {
            $name = trim($_POST['full_name']);
            $specialization = trim($_POST['specialization']);
            $email = trim($_POST['contact_email']);
            $rate = trim($_POST['hourly_rate']);
            
            if (empty($name) || empty($specialization) || empty($email) || !is_numeric($rate)) {
                $message = "All trainer fields must be valid.";
                $message_class = "bg-red-500";
            } else {
                $stmt = $conn->prepare("INSERT INTO `tbl_trainer` (`full_name`, `specialization`, `contact_email`, `hourly_rate`, `is_active`) VALUES (:name, :spec, :email, :rate, 1)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':spec', $specialization);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':rate', $rate);
                $stmt->execute();
                $message = "Trainer added successfully!";
                $message_class = "bg-green-500";
            }
        } elseif ($_POST['action'] == 'delete_trainer') {
            $trainerID = $_POST['trainer_id'];
            $stmt = $conn->prepare("DELETE FROM `tbl_trainer` WHERE `trainer_id` = :id");
            $stmt->bindParam(':id', $trainerID);
            $stmt->execute();
            $message = "Trainer deleted successfully!";
            $message_class = "bg-green-500";
        } elseif ($_POST['action'] == 'toggle_active') {
            $trainerID = $_POST['trainer_id'];
            // Toggle active status (1 -> 0, or 0 -> 1)
            $stmt = $conn->prepare("UPDATE `tbl_trainer` SET `is_active` = 1 - `is_active` WHERE `trainer_id` = :id");
            $stmt->bindParam(':id', $trainerID);
            $stmt->execute();
            $message = "Trainer status updated!";
            $message_class = "bg-green-500";
        }
    } catch (PDOException $e) {
        error_log("Admin Trainer action error: " . $e->getMessage());
        $message = "Database error during action: " . htmlspecialchars($e->getMessage());
        $message_class = "bg-red-500";
    }
}

// --- FETCH TRAINERS ---
try {
    $trainerStmt = $conn->prepare("SELECT `trainer_id`, `full_name`, `specialization`, `contact_email`, `hourly_rate`, `is_active` FROM `tbl_trainer` ORDER BY `trainer_id` ASC");
    $trainerStmt->execute();
    $trainers = $trainerStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching trainer list: " . $e->getMessage());
    $message = "Database error fetching trainer list.";
    $message_class = "bg-red-500";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Trainers</title>
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
                    <a href="manage_trainers.php" class="admin-nav-item block bg-gray-800 text-red-400 font-semibold">
                        Manage Trainers
                    </a>
                    <a href="manage_subscriptions.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
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
                <h2 class="text-4xl font-bold text-red-500">Manage Trainers</h2>
                <p class="text-gray-400 mt-2">Add, remove, and manage the availability of professional trainers.</p>
            </header>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-6 text-center">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- Add New Trainer Form -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-xl mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 border-b border-gray-700 pb-2">Add New Trainer</h3>
                <form method="POST" action="manage_trainers.php" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <input type="hidden" name="action" value="add_trainer">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Full Name</label>
                        <input type="text" name="full_name" class="form-input" required placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Specialization</label>
                        <input type="text" name="specialization" class="form-input" required placeholder="HIIT, Weight Loss">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input type="email" name="contact_email" class="form-input" required placeholder="trainer@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Hourly Rate ($)</label>
                        <input type="number" name="hourly_rate" class="form-input" required step="0.01" min="0" placeholder="50.00">
                    </div>
                    <button type="submit" class="btn-action bg-red-600 hover:bg-red-700 text-white w-full">Add Trainer</button>
                </form>
            </div>


            <!-- Trainer List Table -->
            <div class="bg-gray-800 rounded-lg shadow-xl overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-2/12">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-2/12">Specialization</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">Rate</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-3/12">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if (empty($trainers)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-400">No trainers are currently listed.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trainers as $trainer): ?>
                                <tr class="bg-gray-800 table-row-hover">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?= htmlspecialchars($trainer['trainer_id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?= htmlspecialchars($trainer['full_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?= htmlspecialchars($trainer['specialization']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-400">$<?= number_format($trainer['hourly_rate'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $trainer['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $trainer['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                        
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to toggle the status of <?= htmlspecialchars($trainer['full_name']) ?>?');">
                                            <input type="hidden" name="action" value="toggle_active">
                                            <input type="hidden" name="trainer_id" value="<?= $trainer['trainer_id'] ?>">
                                            <button type="submit" class="btn-action text-xs <?= $trainer['is_active'] ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white' ?>">
                                                <?= $trainer['is_active'] ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>

                                        <form method="POST" class="inline" onsubmit="return confirm('WARNING: Delete <?= htmlspecialchars($trainer['full_name']) ?> permanently? This will affect existing bookings.');">
                                            <input type="hidden" name="action" value="delete_trainer">
                                            <input type="hidden" name="trainer_id" value="<?= $trainer['trainer_id'] ?>">
                                            <button type="submit" class="btn-action text-xs bg-gray-500 hover:bg-gray-600 text-white">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
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