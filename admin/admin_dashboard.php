<?php
session_start();
include ('../conn.php'); // Note the directory change if you put the admin files in a subfolder

// --- SECURITY CHECK ---
// Redirect if user is NOT logged in or IS NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php?error=Access_Denied");
    exit();
}

$userID = $_SESSION['user_id'];
$displayName = $_SESSION['user_full_name'] ?? 'Admin User'; 
$message = ''; 

if (isset($_GET['message'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Beast Fitness</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font: Inter -->
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
        .admin-card {
            background-color: #1f2937;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(71, 85, 105, 0.5);
            transition: transform 0.3s ease;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
        }
        .action-button {
            background-color: #ef4444;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            transition: background-color 0.3s ease-in-out, transform 0.3s ease;
        }
        .action-button:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-900 shadow-2xl flex flex-col justify-between p-6 sticky top-0">
            <div>
                <h1 class="text-3xl font-extrabold text-red-500 mb-8">Admin Panel</h1>
               <nav class="space-y-3">
                     <a href="admin_dashboard.php" class="admin-nav-item block bg-gray-800 text-white font-semibold">
                        Dashboard
                    </a>
                    <a href="manage_users.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
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
                <a href="../home.php?action=logout" class="action-button w-full text-center block">
                    Admin Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="mb-10 flex justify-between items-center">
                <h2 class="text-4xl font-bold text-red-500">System Overview</h2>
                <span class="text-lg text-gray-400">Welcome, <?= $displayName ?>!</span>
            </header>

            <?php if ($message): ?>
                <div class="bg-green-600 text-white p-3 rounded-lg mb-6 text-center">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1: Total Users -->
                <div class="admin-card p-6 text-center">
                    <div class="text-indigo-400 mb-3">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-4m-7-2h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zm0 0l-2 2m2-2l2 2m-2-2v-2"></path></svg>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-1">
                        <?php 
                            $countStmt = $conn->query("SELECT COUNT(tbl_user_id) FROM tbl_user");
                            echo $countStmt->fetchColumn();
                        ?>
                    </h3>
                    <p class="text-gray-400">Total Registered Users</p>
                </div>
                
                <!-- Card 2: Total Trainers -->
                <div class="admin-card p-6 text-center">
                    <div class="text-yellow-400 mb-3">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-1">
                        <?php 
                            $countStmt = $conn->query("SELECT COUNT(trainer_id) FROM tbl_trainer WHERE is_active = TRUE");
                            echo $countStmt->fetchColumn();
                        ?>
                    </h3>
                    <p class="text-gray-400">Active Trainers</p>
                </div>

                <!-- Card 3: Pending Bookings -->
                <div class="admin-card p-6 text-center">
                    <div class="text-red-400 mb-3">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M9 16h.01M16 11h.01M16 16h.01M19 8H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V10a2 2 0 00-2-2z"></path></svg>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-1">
                        <?php 
                            $countStmt = $conn->query("SELECT COUNT(booking_id) FROM tbl_booking WHERE status = 'Pending'");
                            echo $countStmt->fetchColumn();
                        ?>
                    </h3>
                    <p class="text-gray-400">Pending Bookings</p>
                </div>
            </div>

            <!-- Management Access -->
            <div class="mt-12">
                <h3 class="text-3xl font-bold text-white mb-6 border-b border-gray-700 pb-3">Quick Management</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="manage_users.php" class="admin-card p-4 text-center bg-gray-800 hover:bg-gray-700">
                        <p class="text-xl font-semibold text-red-400">Manage Users</p>
                        <p class="text-sm text-gray-400">View, edit, or delete user accounts.</p>
                    </a>
                    <a href="manage_trainers.php" class="admin-card p-4 text-center bg-gray-800 hover:bg-gray-700">
                        <p class="text-xl font-semibold text-red-400">Manage Trainers</p>
                        <p class="text-sm text-gray-400">Add, update, or deactivate trainers.</p>
                    </a>
                    <a href="manage_subscriptions.php" class="admin-card p-4 text-center bg-gray-800 hover:bg-gray-700">
                        <p class="text-xl font-semibold text-red-400">Manage Subscriptions</p>
                        <p class="text-sm text-gray-400">Update pricing and plan details.</p>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>