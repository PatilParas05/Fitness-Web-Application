<?php
session_start();
include ('../conn.php'); 

// --- SECURITY CHECK ---
// Redirect if user is NOT logged in or IS NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php?error=Access_Denied");
    exit();
}

$currentAdminID = $_SESSION['user_id'];
$displayName = $_SESSION['user_full_name'] ?? 'Admin User';
$message = '';
$message_class = '';
$users = [];

// --- ACTION HANDLERS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $targetUserID = $_POST['user_id'];

    // Prevent Admin from modifying their own account status via the table
    if ($targetUserID == $currentAdminID) {
        $message = "Error: You cannot modify your own administrative status here.";
        $message_class = "bg-red-500";
    } else {
        try {
            if ($_POST['action'] == 'toggle_admin') {
                // Toggle admin status (1 -> 0, or 0 -> 1)
                $stmt = $conn->prepare("UPDATE `tbl_user` SET `is_admin` = 1 - `is_admin` WHERE `tbl_user_id` = :id");
                $stmt->bindParam(':id', $targetUserID);
                $stmt->execute();
                $message = "User administrative status updated!";
                $message_class = "bg-green-500";
            } elseif ($_POST['action'] == 'delete_user') {
                // Delete user account
                $stmt = $conn->prepare("DELETE FROM `tbl_user` WHERE `tbl_user_id` = :id");
                $stmt->bindParam(':id', $targetUserID);
                $stmt->execute();
                $message = "User account deleted successfully!";
                $message_class = "bg-green-500";
            }
        } catch (PDOException $e) {
            error_log("Admin User action error: " . $e->getMessage());
            $message = "Database error during action: " . htmlspecialchars($e->getMessage());
            $message_class = "bg-red-500";
        }
    }
}

// --- FETCH USERS ---
try {
    $userStmt = $conn->prepare("SELECT `tbl_user_id`, `full_name`, `email`, `username`, `is_admin`, `created_at` FROM `tbl_user` ORDER BY `tbl_user_id` ASC");
    $userStmt->execute();
    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching user list: " . $e->getMessage());
    $message = "Database error fetching user list.";
    $message_class = "bg-red-500";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
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
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .disabled-action {
            opacity: 0.5;
            cursor: not-allowed;
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
                    <a href="manage_users.php" class="admin-nav-item block bg-gray-800 text-red-400 font-semibold">
                        Manage Users
                    </a>
                    <a href="manage_trainers.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
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
                <h2 class="text-4xl font-bold text-red-500">Manage Users</h2>
                <p class="text-gray-400 mt-2">View all registered users and manage their account roles and access.</p>
            </header>

            <?php if ($message): ?>
                <div class="<?= $message_class ?> text-white p-3 rounded-lg mb-6 text-center">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- User List Table -->
            <div class="bg-gray-800 rounded-lg shadow-xl overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-2/12">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-2/12">Email/Username</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-1/12">Role</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-2/12">Joined</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-3/12">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-400">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <?php $isCurrentAdmin = ($user['tbl_user_id'] == $currentAdminID); ?>
                                <tr class="bg-gray-800 table-row-hover <?= $isCurrentAdmin ? 'border-2 border-red-500' : '' ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?= htmlspecialchars($user['tbl_user_id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        <?= htmlspecialchars($user['email']) ?><br>
                                        <span class="text-gray-500 text-xs">@<?= htmlspecialchars($user['username']) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $user['is_admin'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= $user['is_admin'] ? 'Admin' : 'Standard' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 text-center">
                                        <?= htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                        
                                        <!-- Toggle Admin Button -->
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to change the admin status for <?= htmlspecialchars($user['full_name']) ?>?');">
                                            <input type="hidden" name="action" value="toggle_admin">
                                            <input type="hidden" name="user_id" value="<?= $user['tbl_user_id'] ?>">
                                            <button type="submit" 
                                                    class="btn-action text-xs <?= $user['is_admin'] ? 'bg-yellow-600 hover:bg-yellow-700 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white' ?> <?= $isCurrentAdmin ? 'disabled-action' : '' ?>"
                                                    <?= $isCurrentAdmin ? 'disabled' : '' ?>
                                            >
                                                <?= $user['is_admin'] ? 'Demote' : 'Promote' ?>
                                            </button>
                                        </form>

                                        <!-- Delete User Button -->
                                        <form method="POST" class="inline" onsubmit="return confirm('WARNING: Delete <?= htmlspecialchars($user['full_name']) ?> permanently? This action is irreversible.');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['tbl_user_id'] ?>">
                                            <button type="submit" 
                                                    class="btn-action text-xs bg-gray-500 hover:bg-gray-600 text-white <?= $isCurrentAdmin ? 'disabled-action' : '' ?>"
                                                    <?= $isCurrentAdmin ? 'disabled' : '' ?>
                                            >
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