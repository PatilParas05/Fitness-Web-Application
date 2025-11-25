<?php
session_start();
include ('../conn.php');

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php?error=Access_Denied");
    exit();
}

$displayName = $_SESSION['user_full_name'] ?? 'Admin User';
$message = '';

// Handle status update
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $contactId = intval($_GET['id']);
    $newStatus = $_GET['status'];
    
    if (in_array($newStatus, ['New', 'Read', 'Resolved'])) {
        try {
            $stmt = $conn->prepare("UPDATE tbl_contact SET status = :status WHERE contact_id = :id");
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id', $contactId);
            $stmt->execute();
            $message = "Status updated successfully!";
        } catch (PDOException $e) {
            $message = "Error updating status.";
        }
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $contactId = intval($_GET['id']);
    try {
        $stmt = $conn->prepare("DELETE FROM tbl_contact WHERE contact_id = :id");
        $stmt->bindParam(':id', $contactId);
        $stmt->execute();
        $message = "Contact message deleted successfully!";
    } catch (PDOException $e) {
        $message = "Error deleting message.";
    }
}

// Fetch all contacts
try {
    $stmt = $conn->query("SELECT * FROM tbl_contact ORDER BY created_at DESC");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $contacts = [];
    $message = "Error loading contacts.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact Messages - Admin</title>
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
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-new { background-color: #3b82f6; color: white; }
        .status-read { background-color: #eab308; color: white; }
        .status-resolved { background-color: #22c55e; color: white; }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
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
                    <a href="manage_subscriptions.php" class="admin-nav-item block text-gray-300 hover:text-red-400">
                        Manage Subscriptions
                    </a>
                    <a href="view_contacts.php" class="admin-nav-item block bg-gray-800 text-white font-semibold">
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

        <!-- Main Content -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="mb-10 flex justify-between items-center">
                <h2 class="text-4xl font-bold text-red-500">Contact Messages</h2>
                <span class="text-lg text-gray-400">Welcome, <?= $displayName ?>!</span>
            </header>

            <?php if ($message): ?>
                <div class="bg-green-600 text-white p-3 rounded-lg mb-6 text-center">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
                    <h3 class="text-2xl font-bold text-blue-400">
                        <?= count(array_filter($contacts, fn($c) => $c['status'] === 'New')) ?>
                    </h3>
                    <p class="text-gray-400">New Messages</p>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
                    <h3 class="text-2xl font-bold text-yellow-400">
                        <?= count(array_filter($contacts, fn($c) => $c['status'] === 'Read')) ?>
                    </h3>
                    <p class="text-gray-400">Read Messages</p>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
                    <h3 class="text-2xl font-bold text-green-400">
                        <?= count(array_filter($contacts, fn($c) => $c['status'] === 'Resolved')) ?>
                    </h3>
                    <p class="text-gray-400">Resolved</p>
                </div>
            </div>

            <!-- Contact Messages Table -->
            <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">ID</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Name</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Email</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Message</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Date</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <?php if (empty($contacts)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                        No contact messages yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contacts as $contact): ?>
                                    <tr class="hover:bg-gray-700 transition">
                                        <td class="px-6 py-4 text-white"><?= $contact['contact_id'] ?></td>
                                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($contact['name']) ?></td>
                                        <td class="px-6 py-4 text-gray-300">
                                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="text-blue-400 hover:underline">
                                                <?= htmlspecialchars($contact['email']) ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-gray-300 max-w-xs truncate" title="<?= htmlspecialchars($contact['message']) ?>">
                                            <?= htmlspecialchars(substr($contact['message'], 0, 50)) . (strlen($contact['message']) > 50 ? '...' : '') ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="status-badge status-<?= strtolower($contact['status']) ?>">
                                                <?= htmlspecialchars($contact['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-300">
                                            <?= date('M d, Y', strtotime($contact['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <!-- View Details Button -->
                                                <button onclick="showDetails(<?= $contact['contact_id'] ?>)" class="text-blue-400 hover:text-blue-300 text-sm">
                                                    View
                                                </button>
                                                
                                                <!-- Status Dropdown -->
                                                <select onchange="updateStatus(<?= $contact['contact_id'] ?>, this.value)" class="text-xs bg-gray-700 text-white rounded px-2 py-1 border border-gray-600">
                                                    <option value="">Change Status</option>
                                                    <option value="New" <?= $contact['status'] === 'New' ? 'disabled' : '' ?>>New</option>
                                                    <option value="Read" <?= $contact['status'] === 'Read' ? 'disabled' : '' ?>>Read</option>
                                                    <option value="Resolved" <?= $contact['status'] === 'Resolved' ? 'disabled' : '' ?>>Resolved</option>
                                                </select>
                                                
                                                <!-- Delete Button -->
                                                <a href="?action=delete&id=<?= $contact['contact_id'] ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this message?')"
                                                   class="text-red-400 hover:text-red-300 text-sm">
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal for Full Message -->
    <div id="messageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" onclick="closeModal()">
        <div class="bg-gray-800 rounded-lg p-8 max-w-2xl w-full mx-4" onclick="event.stopPropagation()">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-2xl font-bold text-red-500">Message Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white text-2xl">&times;</button>
            </div>
            <div id="modalContent" class="text-white space-y-4">
                <!-- Content will be inserted here -->
            </div>
        </div>
    </div>

    <script>
        const contacts = <?= json_encode($contacts) ?>;
        
        function showDetails(contactId) {
            const contact = contacts.find(c => c.contact_id == contactId);
            if (!contact) return;
            
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `
                <div class="space-y-3">
                    <p><strong class="text-gray-400">From:</strong> ${escapeHtml(contact.name)}</p>
                    <p><strong class="text-gray-400">Email:</strong> <a href="mailto:${escapeHtml(contact.email)}" class="text-blue-400">${escapeHtml(contact.email)}</a></p>
                    <p><strong class="text-gray-400">Date:</strong> ${new Date(contact.created_at).toLocaleString()}</p>
                    <p><strong class="text-gray-400">Status:</strong> <span class="status-badge status-${contact.status.toLowerCase()}">${contact.status}</span></p>
                    <div class="pt-4 border-t border-gray-700">
                        <strong class="text-gray-400">Message:</strong>
                        <p class="mt-2 text-gray-300 whitespace-pre-wrap">${escapeHtml(contact.message)}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('messageModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden');
        }
        
        function updateStatus(contactId, newStatus) {
            if (newStatus) {
                window.location.href = `?action=update_status&id=${contactId}&status=${newStatus}`;
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>