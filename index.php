<?php
session_start();
// Redirect to dashboard if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

// Get messages
$message = '';
$error = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message']));
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars(str_replace('_', ' ', $_GET['error']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beast Fitness - Unleash Your Power</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #e2e8f0;
            background-image: url("Beast.png");
            background-size: 1500px;
            background-repeat: no-repeat;
        }
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('Beast.jpg');
            background-size: cover;
            background-position: center;
            min-height: 80vh; 
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 64px;
        }
        .card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            background-color: #1f2937;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(71, 85, 105, 0.5);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
        }
        .auth-button {
            background-color: #ef4444;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            transition: background-color 0.3s ease-in-out, transform 0.3s ease;
            margin-left: 0.75rem;
        }
        .auth-button:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }
        .hero-button {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            background-color: #ef4444;
            color: white;
            font-weight: 700;
            padding: 1rem 2.5rem;
            border-radius: 9999px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .hero-button:hover {
            background-color: #dc2626;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body class="bg-gray-900">

    <!-- Navigation Bar -->
    <header class="bg-gray-900 bg-opacity-90 backdrop-filter backdrop-blur-lg fixed w-full z-50 p-4 shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-red-500">Beast Fitness</h1>
            
            <nav class="flex items-center space-x-2 sm:space-x-4">
                <a href="login.php" class="auth-button bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full shadow-lg text-sm sm:text-base">
                    Login
                </a>
                <a href="signup.php" class="auth-button bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full shadow-lg text-sm sm:text-base">
                    Sign Up
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white p-4">
        <div class="max-w-4xl mx-auto py-24">
            <h2 class="text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tight mb-4">
                Beast Fitness
            </h2>
            <p class="text-xl md:text-2xl lg:text-3xl font-light mb-10 max-w-3xl mx-auto">
                Your ultimate companion for tracking workouts and achieving your fitness goals. Get ready to unleash your inner beast!
            </p>
            <a href="signup.php" class="hero-button">
                Start Your Journey
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-900">
        <div class="container mx-auto px-6">
            <h3 class="text-4xl lg:text-5xl font-bold text-center mb-12 text-red-500">Key Features</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                
                <div class="card p-8 text-center shadow-2xl">
                    <div class="text-blue-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m-6 0v-4a2 2 0 012-2h2a2 2 0 012 2v4m-6 0h6"></path></svg>
                    </div>
                    <h4 class="text-2xl font-semibold mb-2 text-white">Track Workouts</h4>
                    <p class="text-gray-400">Log every set, rep, and exercise with ease. Monitor your progress over time with detailed charts and graphs.</p>
                </div>
                
                <div class="card p-8 text-center shadow-2xl">
                    <div class="text-green-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.484 9.243 5.378 8 5.766 6.757 6.155 5.518 6.945 4.505 8.16C3.492 9.375 2.658 10.932 2 12.75m10-6.497m0-4.253a4.252 4.252 0 014.253 4.253m-4.253-4.253v13m0-13a4.252 4.252 0 01-4.253 4.253m4.253-4.253h6.5m-6.5 0c-1.295 0-2.433-.762-3.238-1.875M12 6.253H5.5m6.5 0a4.252 4.252 0 00-4.253 4.253m4.253-4.253v13m0-13a4.252 4.252 0 004.253 4.253m-4.253-4.253h6.5m-6.5 0c1.295 0 2.433-.762 3.238-1.875M12 6.253H5.5"></path></svg>
                    </div>
                    <h4 class="text-2xl font-semibold mb-2 text-white">View Progress</h4>
                    <p class="text-gray-400">Visualize your fitness journey with intuitive dashboards. See your gains, track your habits, and stay on course to your goals.</p>
                </div>

                <div class="card p-8 text-center shadow-2xl">
                    <div class="text-yellow-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h4 class="text-2xl font-semibold mb-2 text-white">Workout Gallery</h4>
                    <p class="text-gray-400">Find new inspiration from a curated gallery of images and workouts to keep your routine fresh and exciting.</p>
                </div>
                
                <div class="card p-8 text-center shadow-2xl">
                    <div class="text-red-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-4m-7-3V7a2 2 0 012-2h7a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2zM9 16h6M9 12h6M9 8h6"></path></svg>
                    </div>
                    <h4 class="text-2xl font-semibold mb-2 text-white">Book a Trainer</h4>
                    <p class="text-gray-400">Schedule one-on-one sessions with certified personal trainers specializing in your fitness goals.</p>
                </div>
                
                <div class="card p-8 text-center shadow-2xl">
                    <div class="text-purple-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8h.01M12 19a9 9 0 100-18 9 9 0 000 18z"></path></svg>
                    </div>
                    <h4 class="text-2xl font-semibold mb-2 text-white">Premium Access</h4>
                    <p class="text-gray-400">Unlock diet plans, advanced progress analytics, and exclusive content with our subscription tiers.</p>
                </div>
                
                <div class="card p-8 text-center shadow-2xl">
                    <div class="text-indigo-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5V3m0 2h7m-7 0v2m7-2v2m-7 0h7m-7 0H9m7 0h2m-2 0v2m-2 0V7m0 0h2m-2 0H9"></path></svg>
                    </div>
                    <h4 class="text-2xl font-semibold mb-2 text-white">Fitness Goals</h4>
                    <p class="text-gray-400">Set and track your personal fitness targets, from weight loss to strength gains, all in one place.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="py-20 bg-gray-800">
        <div class="container mx-auto px-6 text-center">
            <h3 class="text-4xl lg:text-5xl font-bold text-red-500 mb-12">About Beast Fitness: Our Mission</h3>
            <div class="max-w-4xl mx-auto text-left">
                <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                    Beast Fitness was forged from a simple idea: that everyone has **untapped power** waiting to be unleashed. We are not just a logging tool; we are your digital spotter, your relentless motivator, and the engine driving your most ambitious fitness goals. Our platform is engineered for those who are serious about progress, transforming consistency into tangible results.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                    <div class="p-6 bg-gray-900 rounded-lg shadow-2xl border-b-4 border-red-600">
                        <h4 class="text-2xl font-semibold mb-3 text-red-400">Dedication</h4>
                        <p class="text-gray-400">We believe consistency beats intensity. Our platform is built to make dedication easier, tracking every single set, rep, and milestone of your journey.</p>
                    </div>
                    <div class="p-6 bg-gray-900 rounded-lg shadow-2xl border-b-4 border-red-600">
                        <h4 class="text-2xl font-semibold mb-3 text-red-400">Innovation</h4>
                        <p class="text-gray-400">We constantly evolve our features, from advanced analytics to personalized coaching integration, keeping you ahead of the curve in fitness technology.</p>
                    </div>
                    <div class="p-6 bg-gray-900 rounded-lg shadow-2xl border-b-4 border-red-600">
                        <h4 class="text-2xl font-semibold mb-3 text-red-400">Community</h4>
                        <p class="text-gray-400">Fitness is a team sport. We strive to connect users with certified trainers and a supportive community of fellow beasts ready to crush their goals.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="py-20 bg-gray-900">
        <div class="container mx-auto px-6 text-center">
            <h3 class="text-4xl lg:text-5xl font-bold text-white mb-12">Get in Touch: Contact Us</h3>
            
            <!-- Success/Error Messages -->
            <?php if ($message): ?>
                <div class="max-w-4xl mx-auto mb-6 bg-green-600 text-white p-4 rounded-lg">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="max-w-4xl mx-auto mb-6 bg-red-600 text-white p-4 rounded-lg">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 text-left">
                <!-- Contact Form -->
                <div class="p-8 bg-gray-800 rounded-xl shadow-2xl">
                    <h4 class="text-3xl font-bold text-red-400 mb-6">Send Us a Message</h4>
                    <form action="submit_contact.php" method="POST" class="space-y-4">
                        <input 
                            type="text" 
                            name="name" 
                            placeholder="Your Name" 
                            required
                            class="w-full p-3 rounded-lg bg-gray-700 text-white placeholder-gray-400 border border-gray-600 focus:border-red-500 focus:ring-red-500 transition duration-200">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Your Email" 
                            required
                            class="w-full p-3 rounded-lg bg-gray-700 text-white placeholder-gray-400 border border-gray-600 focus:border-red-500 focus:ring-red-500 transition duration-200">
                        <textarea 
                            name="message" 
                            placeholder="Your Message" 
                            rows="4" 
                            required
                            class="w-full p-3 rounded-lg bg-gray-700 text-white placeholder-gray-400 border border-gray-600 focus:border-red-500 focus:ring-red-500 transition duration-200"></textarea>
                        <button type="submit" class="hero-button w-full mt-4">
                            Submit Inquiry
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="p-8 bg-gray-800 rounded-xl shadow-2xl space-y-8 flex flex-col justify-center">
                    <h4 class="text-3xl font-bold text-red-400 mb-4">Or Connect Directly</h4>
                    
                    <div class="flex items-center space-x-4">
                        <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-2 4v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7"></path></svg>
                        <div>
                            <p class="text-lg font-medium text-white">Email Support</p>
                            <a href="mailto:support@beastfitness.com" class="text-gray-400 hover:text-red-400 transition">support@beastfitness.com</a>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.5l1.5 4.5 4-4.5h3.5a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 12h4m-4 4h4"></path></svg>
                        <div>
                            <p class="text-lg font-medium text-white">Trainer Line</p>
                            <p class="text-gray-400">1-800-BEAST-MODE</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-20 bg-gray-800">
        <div class="container mx-auto text-center px-6">
            <h3 class="text-4xl lg:text-5xl font-bold text-white mb-6">Ready to Unleash Your Inner Beast?</h3>
            <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-2xl mx-auto">
                Join thousands of users who are transforming their fitness journey with the ultimate workout companion.
            </p>
            <a href="signup.php" class="hero-button">
                Get Started Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Beast Fitness App. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>