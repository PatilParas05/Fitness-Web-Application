<?php
session_start(); // Always start the session at the very beginning

// Include your database connection file
// Corrected: Assuming 'conn.php' is in the same directory as 'login.php'
include ('./conn.php'); 

// If the user is already logged in, redirect them. Check if they are an admin.
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        header("Location: admin/admin_dashboard.php");
    } else {
        header("Location: home.php"); // Redirect to home.php
    }
    exit();
}

$message = ''; // Variable to store feedback messages for the user

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username']); // User might enter username, email, or full name
    $password_input = trim($_POST['password']);

    try {
        // Updated: Select 'is_admin' status along with login details
        $stmt = $conn->prepare("SELECT tbl_user_id, password, full_name, is_admin FROM tbl_user WHERE email = :input OR full_name = :input OR username = :input");
        $stmt->bindParam(':input', $username_or_email); 
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify if a user was found and if the password is correct
        if ($user && password_verify($password_input, $user['password'])) {
            // Login successful! Set session variables
            $_SESSION['user_id'] = $user['tbl_user_id'];
            $_SESSION['user_full_name'] = $user['full_name']; 
            
            // Set admin status in session
            $_SESSION['is_admin'] = ($user['is_admin'] == 1); 

            // Redirect based on role
            if ($_SESSION['is_admin'] === true) {
                 header("Location: admin/admin_dashboard.php");
            } else {
                 header("Location: home.php");
            }
            exit();
        } else {
            // Login failed: user not found or incorrect password
            $message = "Invalid username or password."; 
        }
    } catch (PDOException $e) {
        $message = "Database error during login: " . htmlspecialchars($e->getMessage());
        error_log("Login PDO Error: " . $e->getMessage()); 
    }
}

// Display messages passed via GET (e.g., from successful registration)
if (isset($_GET['message'])) {
    $message = htmlspecialchars(urldecode($_GET['message']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness Login</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen p-4">
    <div class="bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md border border-gray-700">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-red-500 mb-2">Beast Fitness</h1>
            <p class="text-gray-300 text-lg">Unleash Your Inner Beast!</p>
        </div>

        <?php if ($message): ?>
            <div class="<?= strpos($message, 'successful') !== false ? 'bg-green-600' : 'bg-red-600' ?> text-white p-3 rounded-lg mb-4 text-center text-sm">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-gray-300 text-sm font-medium mb-2">Username or Email</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                    placeholder="Enter your username or email"
                    required
                >
            </div>
            <div>
                <label for="password" class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                    placeholder="Enter your password"
                    required
                >
            </div>
            <button
                type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-75 shadow-lg"
            >
                Login
            </button>
        </form>

        <p class="text-center text-gray-400 text-sm mt-6">
            Don't have an account? <a href="signup.php" class="text-red-400 hover:underline">Sign Up</a>
        </p>
        <p class="text-center text-gray-400 text-sm mt-6">
            Go to <a href="index.php" class="text-red-400 hover:underline">Home</a>
        </p>
    </div>
</body>
</html>