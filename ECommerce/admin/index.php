<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admins WHERE id = $admin_id";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Get statistics
$products_query = "SELECT COUNT(*) as count FROM products";
$products_result = mysqli_query($conn, $products_query);
$products_count = mysqli_fetch_assoc($products_result)['count'];

$orders_query = "SELECT COUNT(*) as count FROM orders";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

$users_query = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
$users_result = mysqli_query($conn, $users_query);
$users_count = mysqli_fetch_assoc($users_result)['count'];

$revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'";
$revenue_result = mysqli_query($conn, $revenue_query);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;

// Get pending orders
$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
$pending_orders_count = mysqli_fetch_assoc($pending_orders_result)['count'];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.first_name, u.last_name, u.email 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);

// Get popular products
$popular_products_query = "SELECT p.*, COUNT(oi.id) as order_count 
                          FROM products p 
                          JOIN order_items oi ON p.id = oi.product_id 
                          GROUP BY p.id 
                          ORDER BY order_count DESC 
                          LIMIT 5";
$popular_products_result = mysqli_query($conn, $popular_products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Artizo</title>
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
                <a href="index.php" class="active"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                <a href="products.php"><i class="fas fa-box mr-2"></i> Products</a>
                <a href="categories.php"><i class="fas fa-tags mr-2"></i> Categories</a>
                <a href="orders.php"><i class="fas fa-shopping-cart mr-2"></i> Orders</a>
                <a href="customers.php"><i class="fas fa-users mr-2"></i> Customers</a>
                <a href="reviews.php"><i class="fas fa-star mr-2"></i> Reviews</a>
                <a href="coupons.php"><i class="fas fa-ticket-alt mr-2"></i> Coupons</a>
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
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <div class="text-sm text-gray-600">
                    Welcome, <?php echo $admin['first_name'] . ' ' . $admin['last_name']; ?>!
                </div>
            </div>
            
            <!-- Success/Error Messages -->
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
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-gray-500 text-sm">Orders</h3>
                            <p class="text-2xl font-bold"><?php echo $orders_count; ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="orders.php" class="text-blue-600 text-sm hover:underline">View all orders</a>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                        <div>
                                                        <h3 class="text-gray-500 text-sm">Revenue</h3>                            <p class="text-2xl font-bold">৳<?php echo number_format($total_revenue, 2); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="orders.php" class="text-green-600 text-sm hover:underline">View details</a>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-purple-100 p-3 mr-4">
                            <i class="fas fa-box text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="text-gray-500 text-sm">Products</h3>
                            <p class="text-2xl font-bold"><?php echo $products_count; ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="products.php" class="text-purple-600 text-sm hover:underline">Manage products</a>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-yellow-100 p-3 mr-4">
                            <i class="fas fa-users text-yellow-600"></i>
                        </div>
                        <div>
                            <h3 class="text-gray-500 text-sm">Customers</h3>
                            <p class="text-2xl font-bold"><?php echo $users_count; ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="customers.php" class="text-yellow-600 text-sm hover:underline">View customers</a>
                    </div>
                </div>
            </div>
            
            <!-- Pending Orders Alert -->
            <?php if ($pending_orders_count > 0): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            You have <strong><?php echo $pending_orders_count; ?></strong> pending orders that require your attention.
                            <a href="orders.php?status=pending" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                View pending orders
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">Recent Orders</h2>
                        <a href="orders.php" class="text-blue-600 text-sm hover:underline">View all</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (mysqli_num_rows($recent_orders_result) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($order['user_id']): ?>
                                                    <div class="text-sm text-gray-900"><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></div>
                                                <?php else: ?>
                                                    <div class="text-sm text-gray-900">Guest</div>
                                                <?php endif; ?>
                                            </td>
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
                                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">                                                ৳<?php echo number_format($order['total_amount'], 2); ?>                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <a href="#" class="text-green-600 hover:text-green-900" onclick="approveOrder(<?php echo $order['id']; ?>)">Approve</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Popular Products -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">Popular Products</h2>
                        <a href="products.php" class="text-blue-600 text-sm hover:underline">View all</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (mysqli_num_rows($popular_products_result) > 0): ?>
                                    <?php while ($product = mysqli_fetch_assoc($popular_products_result)): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover" src="../assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                                    </div>
                                                                                                        <div class="ml-4">                                                        <div class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?></div>                                                        <div class="text-sm text-gray-500"><?php echo isset($product['sku']) ? $product['sku'] : 'No SKU'; ?></div>                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                                                <?php if ($product['sale_price']): ?>                                                    <span class="line-through text-gray-400">৳<?php echo number_format($product['price'], 2); ?></span>                                                    <span class="text-green-600">৳<?php echo number_format($product['sale_price'], 2); ?></span>                                                <?php else: ?>                                                    ৳<?php echo number_format($product['price'], 2); ?>                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $product['order_count']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php if ($product['quantity'] > 10): ?>
                                                    <span class="text-green-600"><?php echo $product['quantity']; ?></span>
                                                <?php elseif ($product['quantity'] > 0): ?>
                                                    <span class="text-yellow-600"><?php echo $product['quantity']; ?> (Low)</span>
                                                <?php else: ?>
                                                    <span class="text-red-600">Out of stock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No products found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Approval Modal -->
    <div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-xl font-bold mb-4">Approve Order</h2>
            <p class="mb-4">Are you sure you want to approve this order? This will change the status to "processing" and send an email notification to the customer.</p>
            <form id="approvalForm" action="approve-order.php" method="post">
                <input type="hidden" name="order_id" id="approval_order_id">
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeApprovalModal()" class="px-4 py-2 border rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Approve Order</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function approveOrder(orderId) {
            document.getElementById('approval_order_id').value = orderId;
            document.getElementById('approvalModal').classList.remove('hidden');
        }
        
        function closeApprovalModal() {
            document.getElementById('approvalModal').classList.add('hidden');
        }
    </script>
</body>
</html> 