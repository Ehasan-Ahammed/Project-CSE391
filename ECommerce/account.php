<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get user orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Handle profile update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email exists for another user
        $email_check_query = "SELECT * FROM users WHERE email = '$email' AND id != $user_id";
        $email_check_result = mysqli_query($conn, $email_check_query);
        
        if (mysqli_num_rows($email_check_result) > 0) {
            $error = 'Email address is already in use by another account';
        } else {
            // Update profile
            $update_query = "UPDATE users SET 
                            first_name = '$first_name',
                            last_name = '$last_name',
                            email = '$email',
                            phone = '$phone',
                            address = '$address',
                            city = '$city',
                            state = '$state',
                            postal_code = '$postal_code',
                            country = '$country'
                            WHERE id = $user_id";
            
            if (mysqli_query($conn, $update_query)) {
                $success = 'Profile updated successfully';
                
                // Update session variables
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_email'] = $email;
                
                // Refresh user data
                $user_result = mysqli_query($conn, $user_query);
                $user = mysqli_fetch_assoc($user_result);
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields';
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = 'Current password is incorrect';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } else {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success = 'Password changed successfully';
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">My Account</h1>
        
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
        
        <div class="flex flex-col lg:flex-row">
            <!-- Account Navigation -->
            <div class="w-full lg:w-1/4 lg:pr-8 mb-8 lg:mb-0">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-xl font-semibold"><?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?></span>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h2>
                                <p class="text-sm text-gray-600"><?php echo $user['email']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <nav>
                        <a href="#dashboard" class="block px-6 py-3 border-b hover:bg-gray-50 active">Dashboard</a>
                        <a href="#orders" class="block px-6 py-3 border-b hover:bg-gray-50">Orders</a>
                        <a href="#profile" class="block px-6 py-3 border-b hover:bg-gray-50">Profile</a>
                        <a href="#password" class="block px-6 py-3 border-b hover:bg-gray-50">Change Password</a>
                        <a href="wishlist.php" class="block px-6 py-3 border-b hover:bg-gray-50">Wishlist</a>
                        <a href="logout.php" class="block px-6 py-3 hover:bg-gray-50 text-red-600">Logout</a>
                    </nav>
                </div>
            </div>
            
            <!-- Account Content -->
            <div class="w-full lg:w-3/4">
                <!-- Dashboard -->
                <div id="dashboard" class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Dashboard</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold mb-2"><?php echo mysqli_num_rows($orders_result); ?></div>
                            <div class="text-gray-600">Total Orders</div>
                        </div>
                        
                        <div class="border rounded-lg p-4 text-center">
                            <?php
                            $wishlist_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
                            $wishlist_result = mysqli_query($conn, $wishlist_query);
                            $wishlist_count = mysqli_fetch_assoc($wishlist_result)['count'];
                            ?>
                            <div class="text-3xl font-bold mb-2"><?php echo $wishlist_count; ?></div>
                            <div class="text-gray-600">Wishlist Items</div>
                        </div>
                        
                        <div class="border rounded-lg p-4 text-center">
                            <?php
                            $reviews_query = "SELECT COUNT(*) as count FROM reviews WHERE user_id = $user_id";
                            $reviews_result = mysqli_query($conn, $reviews_query);
                            $reviews_count = mysqli_fetch_assoc($reviews_result)['count'];
                            ?>
                            <div class="text-3xl font-bold mb-2"><?php echo $reviews_count; ?></div>
                            <div class="text-gray-600">Reviews</div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="font-semibold mb-3">Recent Orders</h3>
                        
                        <?php
                        mysqli_data_seek($orders_result, 0);
                        $recent_orders = [];
                        $count = 0;
                        
                        while ($order = mysqli_fetch_assoc($orders_result)) {
                            $recent_orders[] = $order;
                            $count++;
                            if ($count >= 3) break;
                        }
                        
                        if (count($recent_orders) > 0):
                        ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">#<?php echo $order['id']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php
                                                        switch ($order['status']) {
                                                            case 'pending':
                                                                echo 'bg-yellow-100 text-yellow-800';
                                                                break;
                                                            case 'processing':
                                                                echo 'bg-blue-100 text-blue-800';
                                                                break;
                                                            case 'shipped':
                                                                echo 'bg-purple-100 text-purple-800';
                                                                break;
                                                            case 'delivered':
                                                                echo 'bg-green-100 text-green-800';
                                                                break;
                                                            case 'cancelled':
                                                                echo 'bg-red-100 text-red-800';
                                                                break;
                                                            default:
                                                                echo 'bg-gray-100 text-gray-800';
                                                        }
                                                        ?>
                                                    ">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="#orders" class="text-blue-600 hover:underline">View All Orders</a>
                            </div>
                        <?php else: ?>
                            <p>You haven't placed any orders yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Orders -->
                <div id="orders" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
                    <h2 class="text-xl font-semibold mb-4">My Orders</h2>
                    
                    <?php
                    mysqli_data_seek($orders_result, 0);
                    if (mysqli_num_rows($orders_result) > 0):
                    ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">#<?php echo $order['id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php
                                                    switch ($order['status']) {
                                                        case 'pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'processing':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'shipped':
                                                            echo 'bg-purple-100 text-purple-800';
                                                            break;
                                                        case 'delivered':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'cancelled':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>
                                                ">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php
                                                    switch ($order['payment_status']) {
                                                        case 'paid':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'failed':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>
                                                ">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>You haven't placed any orders yet.</p>
                        <div class="mt-4">
                            <a href="shop.php" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Profile -->
                <div id="profile" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
                    <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
                    
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" class="w-full border rounded-md px-4 py-2">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo $user['address']; ?>" class="w-full border rounded-md px-4 py-2">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" id="city" name="city" value="<?php echo $user['city']; ?>" class="w-full border rounded-md px-4 py-2">
                            </div>
                            
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                                <input type="text" id="state" name="state" value="<?php echo $user['state']; ?>" class="w-full border rounded-md px-4 py-2">
                            </div>
                            
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" value="<?php echo $user['postal_code']; ?>" class="w-full border rounded-md px-4 py-2">
                            </div>
                            
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <select id="country" name="country" class="w-full border rounded-md px-4 py-2">
                                    <option value="">Select Country</option>
                                    <option value="US" <?php echo $user['country'] === 'US' ? 'selected' : ''; ?>>United States</option>
                                    <option value="CA" <?php echo $user['country'] === 'CA' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="UK" <?php echo $user['country'] === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="AU" <?php echo $user['country'] === 'AU' ? 'selected' : ''; ?>>Australia</option>
                                    <!-- Add more countries as needed -->
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition">Update Profile</button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div id="password" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
                    <h2 class="text-xl font-semibold mb-4">Change Password</h2>
                    
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="mb-4">
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="w-full border rounded-md px-4 py-2" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="w-full border rounded-md px-4 py-2" required>
                            <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                        </div>
                        
                        <div class="mb-6">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full border rounded-md px-4 py-2" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const navLinks = document.querySelectorAll('nav a');
    const contentSections = document.querySelectorAll('#dashboard, #orders, #profile, #password');
    
    navLinks.forEach(link => {
        if (!link.getAttribute('href').startsWith('#')) return;
        
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            
            // Hide all content sections
            contentSections.forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show the target section
            document.getElementById(targetId).classList.remove('hidden');
            
            // Update active state in navigation
            navLinks.forEach(navLink => {
                navLink.classList.remove('active', 'bg-gray-100', 'font-semibold');
            });
            
            this.classList.add('active', 'bg-gray-100', 'font-semibold');
        });
    });
    
    // Check if URL has a hash and navigate to that tab
    if (window.location.hash) {
        const targetLink = document.querySelector(`nav a[href="${window.location.hash}"]`);
        if (targetLink) {
            targetLink.click();
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?> 