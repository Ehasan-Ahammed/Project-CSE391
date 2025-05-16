<?php
session_start();
include '../config/db.php';

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check if admin exists
        $query = "SELECT * FROM admins WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            
            // Using plain text password comparison
            if ($password === $admin['password']) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['admin_email'] = $admin['email'];
                
                // Update last login time
                $update_query = "UPDATE admins SET last_login = NOW() WHERE id = {$admin['id']}";
                mysqli_query($conn, $update_query);
                
                // Redirect to admin dashboard
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'No admin account found with that username';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Artizo</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="bg-blue-600 text-white py-2 px-4 rounded-md inline-flex items-center mb-4">
                    <i class="fas fa-user-shield text-xl mr-2"></i>
                    <span class="font-bold">ADMIN AREA</span>
                </div>
                <h1 class="text-3xl font-bold">Artizo Admin</h1>
                <p class="text-gray-600 mt-2">Login to access the admin dashboard</p>
            </div>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            This area is restricted to authorized administrators only. If you are a customer, please use the <a href="../login.php" class="font-medium underline">customer login page</a>.
                        </p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                    <input type="text" id="username" name="username" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center">
                    <i class="fas fa-lock mr-2"></i>
                    <span>Login to Admin Panel</span>
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="../index.php" class="text-blue-600 hover:underline mr-4"><i class="fas fa-home mr-1"></i> Back to Website</a>
                <a href="../login.php" class="text-blue-600 hover:underline"><i class="fas fa-user mr-1"></i> Customer Login</a>
            </div>
        </div>
    </div>
</body>
</html> 