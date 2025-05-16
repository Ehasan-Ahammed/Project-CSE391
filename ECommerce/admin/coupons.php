<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle coupon deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $coupon_id = intval($_GET['delete']);
    
    $delete_query = "DELETE FROM coupons WHERE id = $coupon_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Coupon deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting coupon: " . mysqli_error($conn);
    }
    
    header('Location: coupons.php');
    exit;
}

// Handle coupon form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $code = mysqli_real_escape_string($conn, strtoupper($_POST['code']));
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $value = floatval($_POST['value']);
    $min_order_value = !empty($_POST['min_order_value']) ? floatval($_POST['min_order_value']) : 0;
    $max_discount_value = !empty($_POST['max_discount_value']) ? floatval($_POST['max_discount_value']) : 'NULL';
    $start_date = !empty($_POST['start_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['start_date']) . "'" : 'NULL';
    $end_date = !empty($_POST['end_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['end_date']) . "'" : 'NULL';
    $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : 'NULL';
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if (isset($_POST['coupon_id']) && !empty($_POST['coupon_id'])) {
        // Update existing coupon
        $coupon_id = intval($_POST['coupon_id']);
        
        $update_query = "UPDATE coupons SET 
                        code = '$code', 
                        type = '$type', 
                        value = $value, 
                        min_order_value = $min_order_value, 
                        max_discount_value = " . (is_numeric($max_discount_value) ? $max_discount_value : 'NULL') . ", 
                        start_date = $start_date, 
                        end_date = $end_date, 
                        usage_limit = " . (is_numeric($usage_limit) ? $usage_limit : 'NULL') . ", 
                        status = '$status' 
                        WHERE id = $coupon_id";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = "Coupon updated successfully";
        } else {
            $_SESSION['error'] = "Error updating coupon: " . mysqli_error($conn);
        }
    } else {
        // Insert new coupon
        $insert_query = "INSERT INTO coupons (code, type, value, min_order_value, max_discount_value, start_date, end_date, usage_limit, status) 
                        VALUES ('$code', '$type', $value, $min_order_value, " . (is_numeric($max_discount_value) ? $max_discount_value : 'NULL') . ", $start_date, $end_date, " . (is_numeric($usage_limit) ? $usage_limit : 'NULL') . ", '$status')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = "Coupon created successfully";
        } else {
            $_SESSION['error'] = "Error creating coupon: " . mysqli_error($conn) . " Query: " . $insert_query;
        }
    }
    
    header('Location: coupons.php');
    exit;
}

// Get coupon data for editing
$edit_coupon = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $coupon_id = intval($_GET['edit']);
    
    $edit_query = "SELECT * FROM coupons WHERE id = $coupon_id";
    $edit_result = mysqli_query($conn, $edit_query);
    
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_coupon = mysqli_fetch_assoc($edit_result);
    }
}

// Get all coupons
$query = "SELECT * FROM coupons ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupon Management - Artizo Admin</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar {
            background-color: #343a40;
            color: #fff;
            min-height: 100vh;
        }
        .admin-sidebar a {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 15px;
            display: block;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="flex">
        <!-- Sidebar -->
        <div class="admin-sidebar w-64 fixed h-full">
            <div class="p-4 border-b border-gray-700">
                <h1 class="text-xl font-bold">Artizo Admin</h1>
            </div>
            <nav class="mt-4">
                <a href="index.php"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                <a href="products.php"><i class="fas fa-box mr-2"></i> Products</a>
                <a href="categories.php"><i class="fas fa-tags mr-2"></i> Categories</a>
                <a href="orders.php"><i class="fas fa-shopping-cart mr-2"></i> Orders</a>
                <a href="customers.php"><i class="fas fa-users mr-2"></i> Customers</a>
                <a href="reviews.php"><i class="fas fa-star mr-2"></i> Reviews</a>
                <a href="coupons.php" class="active"><i class="fas fa-ticket-alt mr-2"></i> Coupons</a>
                <a href="settings.php"><i class="fas fa-cog mr-2"></i> Settings</a>
                <div class="mt-8 border-t border-gray-700 pt-4">
                    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt mr-2"></i> View Site</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="ml-64 flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold">Coupon Management</h1>
                <?php if (!$edit_coupon): ?>
                    <button onclick="toggleCouponForm()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i> Add New Coupon
                    </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Coupon Form -->
            <div id="couponForm" class="bg-white p-6 rounded-lg shadow-sm mb-8 <?php echo $edit_coupon ? 'block' : 'hidden'; ?>">
                <h2 class="text-lg font-semibold mb-4"><?php echo $edit_coupon ? 'Edit' : 'Add New'; ?> Coupon</h2>
                <form action="coupons.php" method="post">
                    <?php if ($edit_coupon): ?>
                        <input type="hidden" name="coupon_id" value="<?php echo $edit_coupon['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="code" class="block mb-2 text-sm font-medium">Coupon Code*</label>
                            <input type="text" id="code" name="code" class="w-full border rounded-md px-3 py-2" value="<?php echo $edit_coupon ? $edit_coupon['code'] : ''; ?>" required>
                            <p class="text-sm text-gray-500 mt-1">Coupon code will be automatically converted to uppercase.</p>
                        </div>
                        
                        <div>
                            <label for="type" class="block mb-2 text-sm font-medium">Discount Type*</label>
                            <select id="type" name="type" class="w-full border rounded-md px-3 py-2" required>
                                <option value="percentage" <?php echo ($edit_coupon && $edit_coupon['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage</option>
                                <option value="fixed" <?php echo ($edit_coupon && $edit_coupon['type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="value" class="block mb-2 text-sm font-medium">Discount Value*</label>
                            <input type="number" id="value" name="value" step="0.01" min="0" class="w-full border rounded-md px-3 py-2" value="<?php echo $edit_coupon ? $edit_coupon['value'] : ''; ?>" required>
                            <p class="text-sm text-gray-500 mt-1" id="valueHelper">For percentage, enter a value between 1-100.</p>
                        </div>
                        
                        <div>
                            <label for="min_order_value" class="block mb-2 text-sm font-medium">Minimum Order Value</label>
                            <input type="number" id="min_order_value" name="min_order_value" step="0.01" min="0" class="w-full border rounded-md px-3 py-2" value="<?php echo $edit_coupon ? $edit_coupon['min_order_value'] : '0'; ?>">
                        </div>
                        
                        <div>
                            <label for="max_discount_value" class="block mb-2 text-sm font-medium">Maximum Discount Amount</label>
                            <input type="number" id="max_discount_value" name="max_discount_value" step="0.01" min="0" class="w-full border rounded-md px-3 py-2" value="<?php echo $edit_coupon && $edit_coupon['max_discount_value'] ? $edit_coupon['max_discount_value'] : ''; ?>">
                            <p class="text-sm text-gray-500 mt-1">Maximum discount amount (only applies to percentage discounts).</p>
                        </div>
                        
                        <div>
                            <label for="usage_limit" class="block mb-2 text-sm font-medium">Usage Limit</label>
                            <input type="number" id="usage_limit" name="usage_limit" min="1" class="w-full border rounded-md px-3 py-2" value="<?php echo $edit_coupon && $edit_coupon['usage_limit'] ? $edit_coupon['usage_limit'] : ''; ?>">
                            <p class="text-sm text-gray-500 mt-1">Leave empty for unlimited use.</p>
                        </div>
                        
                        <div>
                            <label for="start_date" class="block mb-2 text-sm font-medium">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="w-full border rounded-md px-3 py-2" value="<?php echo $edit_coupon && $edit_coupon['start_date'] ? $edit_coupon['start_date'] : ''; ?>">
                            <p class="text-sm text-gray-500 mt-1">Leave empty to start immediately.</p>
                        </div>
                        
                        <div>
                            <label for="end_date" class="block mb-2 text-sm font-medium">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="w-full border rounded-md px-3 py-2" value="<?php echo $edit_coupon && $edit_coupon['end_date'] ? $edit_coupon['end_date'] : ''; ?>">
                            <p class="text-sm text-gray-500 mt-1">Leave empty for no expiration.</p>
                        </div>
                        
                        <div>
                            <label for="status" class="block mb-2 text-sm font-medium">Status*</label>
                            <select id="status" name="status" class="w-full border rounded-md px-3 py-2" required>
                                <option value="active" <?php echo ($edit_coupon && $edit_coupon['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($edit_coupon && $edit_coupon['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            <?php echo $edit_coupon ? 'Update Coupon' : 'Create Coupon'; ?>
                        </button>
                        <?php if ($edit_coupon): ?>
                            <a href="coupons.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Cancel</a>
                        <?php else: ?>
                            <button type="button" onclick="toggleCouponForm()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Cancel</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Coupons List -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($coupon = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium"><?php echo $coupon['code']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        if ($coupon['type'] === 'percentage') {
                                            echo $coupon['value'] . '%';
                                            if (!empty($coupon['max_discount_value'])) {
                                                echo ' (max $' . number_format($coupon['max_discount_value'], 2) . ')';
                                            }
                                        } else {
                                            echo '$' . number_format($coupon['value'], 2);
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo !empty($coupon['min_order_value']) ? '$' . number_format($coupon['min_order_value'], 2) : '-'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $start = !empty($coupon['start_date']) ? date('M d, Y', strtotime($coupon['start_date'])) : 'Always';
                                        $end = !empty($coupon['end_date']) ? date('M d, Y', strtotime($coupon['end_date'])) : 'No expiry';
                                        
                                        echo $start . ' - ' . $end;
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        if (!empty($coupon['usage_limit'])) {
                                            echo $coupon['used_count'] . '/' . $coupon['usage_limit'];
                                        } else {
                                            echo $coupon['used_count'] . '/∞';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $coupon['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($coupon['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="coupons.php?edit=<?php echo $coupon['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="coupons.php?delete=<?php echo $coupon['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this coupon?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No coupons found. Create your first coupon!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleCouponForm() {
            const form = document.getElementById('couponForm');
            form.classList.toggle('hidden');
        }
        
        // Update value helper text based on discount type
        document.getElementById('type').addEventListener('change', function() {
            const valueHelper = document.getElementById('valueHelper');
            if (this.value === 'percentage') {
                valueHelper.textContent = 'For percentage, enter a value between 1-100.';
            } else {
                valueHelper.textContent = 'Enter the fixed discount amount.';
            }
        });
    </script>
</body>
</html> 