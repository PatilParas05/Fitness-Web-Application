<?php
session_start(); // Always start the session at the very beginning

// Include your database connection file
// Corrected: Assuming 'conn.php' is in the same directory as 'signup.php'
include ('./conn.php'); 

// If the user is already logged in, redirect them to the dashboard (home.php)
if (isset($_SESSION['user_id'])) {
    header("Location: home.php"); // Redirect to your main dashboard page
    exit();
}

$message = ''; // Variable to store feedback messages for the user

// Process signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username_input = trim($_POST['username']); // New: Get username from form
    $password_input = trim($_POST['password']);
    $confirm_password_input = trim($_POST['confirm_password']);

    // --- CORRECTION: Stricter validation against empty strings ('') ---
    if (empty($fullname) || $fullname === '') {
        $message = "Full Name is required.";
    } elseif (empty($email) || $email === '') {
        $message = "Email Address is required.";
    } elseif (empty($username_input) || $username_input === '') {
        $message = "Username is required.";
    } elseif (empty($password_input) || $password_input === '') {
        $message = "Password is required.";
    } elseif (empty($confirm_password_input) || $confirm_password_input === '') {
        $message = "Confirm Password is required.";
    } 
    // --- END CORRECTION ---
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password_input !== $confirm_password_input) {
        $message = "Passwords do not match.";
    } elseif (strlen($password_input) < 6) { // Example: minimum password length
        $message = "Password must be at least 6 characters long.";
    } else {
        // Attempt to split full name into first and last name
        $nameParts = explode(' ', $fullname, 2); // Split by first space, limit to 2 parts
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : ''; // If no space, last name is empty

        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

        try {
            // Check if email OR username already exists
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM tbl_user WHERE email = :email OR username = :username");
            $checkStmt->bindParam(':email', $email);
            $checkStmt->bindParam(':username', $username_input); // New: Check for duplicate username
            $checkStmt->execute();
            if ($checkStmt->fetchColumn() > 0) {
                $message = "This email or username is already registered. Please login or use different credentials.";
            } else {
                // Insert new user into the database
                // Updated: Include 'username' in the INSERT statement
                $stmt = $conn->prepare("INSERT INTO tbl_user (full_name, first_name, last_name, email, username, password) VALUES (:full_name, :first_name, :last_name, :email, :username, :password)");
                $stmt->bindParam(':full_name', $fullname);
                $stmt->bindParam(':first_name', $firstName);
                $stmt->bindParam(':last_name', $lastName);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':username', $username_input); // New: Bind username
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();

                // Registration successful! Redirect to login page with a success message
                header("Location: login.php?message=" . urlencode("Registration successful! Please log in."));
                exit();
            }
        } catch (PDOException $e) {
            $message = "Database error during registration: " . htmlspecialchars($e->getMessage());
            error_log("Signup PDO Error: " . $e->getMessage()); // Log the error for debugging
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness Sign Up</title>
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
            <p class="text-gray-300 text-lg">Join the Pack!</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-red-600 text-white p-3 rounded-lg mb-4 text-center text-sm">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="signup.php" method="POST" class="space-y-6">
            <div>
                <label for="fullname" class="block text-gray-300 text-sm font-medium mb-2">Full Name</label>
                <input
                    type="text"
                    id="fullname"
                    name="fullname"
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                    placeholder="Enter your full name"
                    required
                >
            </div>
            <div>
                <label for="email" class="block text-gray-300 text-sm font-medium mb-2">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                    placeholder="Enter your email address"
                    required
                >
            </div>
            <div>
                <label for="username" class="block text-gray-300 text-sm font-medium mb-2">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                    placeholder="Create a unique username"
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
                    placeholder="Create a password"
                    required
                >
            </div>
            <div>
                <label for="confirm_password" class="block text-gray-300 text-sm font-medium mb-2">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                    placeholder="Confirm your password"
                    required
                >
            </div>
            <button
                type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-75 shadow-lg"
            >
                Sign Up
            </button>
        </form>

        <p class="text-center text-gray-400 text-sm mt-6">
            Already have an account? <a href="login.php" class="text-red-400 hover:underline">Login here</a>
        </p>
    </div>
</body>
</html>