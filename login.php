<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('user-dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            redirect('user-dashboard.php');
        }
    }

    $error = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub - User Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="login-bg min-h-screen flex items-center justify-center bg-gradient-to-r from-indigo-500 to-purple-600 px-4">
    <div class="w-full max-w-md space-y-8 p-8 sm:p-10 bg-white rounded-lg shadow-lg" data-aos="zoom-in">
        <div class="text-center">
            <i data-feather="calendar" class="mx-auto h-12 w-12 text-indigo-600"></i>
            <h2 class="mt-4 text-2xl sm:text-3xl font-extrabold text-gray-900">User Login</h2>
            <p class="mt-2 text-sm sm:text-base text-gray-600">Sign in to manage your event registrations</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <form class="mt-6 space-y-6" action="login.php" method="POST">
            <div class="space-y-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-feather="mail" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input id="email-address" name="email" type="email" autocomplete="email" required class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Email address">
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-feather="lock" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Password">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-0">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 text-sm text-gray-900">Remember me</label>
                </div>
                <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i data-feather="log-in" class="h-5 w-5 mr-2"></i> Sign in
            </button>
            <p class="text-center text-sm text-gray-600">Don't have an account? 
                <a href="register.php" class="font-medium text-indigo-600 hover:text-indigo-500">Register here</a>
            </p>
        </form>
    </div>
    <script>AOS.init(); feather.replace();</script>
</body>
</html>
