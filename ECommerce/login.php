<?php
session_start();
include 'config/db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check if user exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: account.php');
                }
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'No account found with that email';
        }
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['reg_email']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if email already exists
        $check_query = "SELECT * FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Email address is already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insert_query = "INSERT INTO users (first_name, last_name, email, password) 
                            VALUES ('$first_name', '$last_name', '$email', '$hashed_password')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col md:flex-row">
                <!-- Login Form -->
                <div class="w-full md:w-1/2 md:pr-4 mb-8 md:mb-0">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-2xl font-bold mb-6">Login</h2>
                        
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div class="mb-6">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" id="password" name="password" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div class="flex items-center justify-between mb-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="remember" class="form-checkbox">
                                    <span class="ml-2">Remember me</span>
                                </label>
                                
                                <a href="forgot-password.php" class="text-blue-600 hover:underline">Forgot password?</a>
                            </div>
                            
                            <button type="submit" name="login" class="w-full bg-black text-white py-2 rounded-md hover:bg-gray-800 transition">Login</button>
                        </form>
                        
                        <div class="mt-6">
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-300"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                                </div>
                            </div>
                            
                            <div class="mt-6 grid grid-cols-2 gap-3">
                                <a href="#" class="flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fab fa-google text-red-600 mr-2"></i>
                                    Google
                                </a>
                                <a href="#" class="flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fab fa-facebook-f text-blue-600 mr-2"></i>
                                    Facebook
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-6 text-center text-sm">
                            <p class="text-gray-600">Are you an administrator? <a href="admin/login.php" class="text-blue-600 hover:underline font-medium">Login to Admin Panel</a></p>
                        </div>
                    </div>
                </div>
                
                <!-- Registration Form -->
                <div class="w-full md:w-1/2 md:pl-4">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-2xl font-bold mb-6">Register</h2>
                        
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="w-full border rounded-md px-4 py-2" required>
                                </div>
                                
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="w-full border rounded-md px-4 py-2" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="reg_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="reg_email" name="reg_email" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="reg_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" id="reg_password" name="reg_password" class="w-full border rounded-md px-4 py-2" required>
                                <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                            </div>
                            
                            <div class="mb-6">
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div class="mb-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="agree_terms" class="form-checkbox" required>
                                    <span class="ml-2 text-sm">I agree to the <a href="terms.php" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="privacy-policy.php" class="text-blue-600 hover:underline">Privacy Policy</a></span>
                                </label>
                            </div>
                            
                            <button type="submit" name="register" class="w-full bg-black text-white py-2 rounded-md hover:bg-gray-800 transition">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 