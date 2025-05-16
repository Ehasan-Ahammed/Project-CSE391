<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Process order status update
$message = '';
if (isset($_POST['update_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Update order status
    $update_query = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
    
    if (mysqli_query($conn, $update_query)) {
        // Get customer email for notification
        $email_query = "SELECT u.email, u.first_name, u.last_name, o.total_amount 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.id = $order_id";
        $email_result = mysqli_query($conn, $email_query);
        
        if (mysqli_num_rows($email_result) > 0) {
            $customer = mysqli_fetch_assoc($email_result);
            
            // Send email notification
            $to = $customer['email'];
            $subject = "Your Artizo Order #$order_id Status Update";
            
            $message_body = "Dear {$customer['first_name']} {$customer['last_name']},\n\n";
            $message_body .= "Your order #$order_id status has been updated to: " . ucfirst($new_status) . "\n\n";
            
            if ($new_status == 'processing') {
                $message_body .= "We are now processing your order and will update you when it ships.\n\n";
            } elseif ($new_status == 'shipped') {
                $message_body .= "Your order has been shipped! You should receive it within 3-7 business days.\n\n";
            } elseif ($new_status == 'delivered') {
                $message_body .= "Your order has been delivered. We hope you enjoy your purchase!\n\n";
            } elseif ($new_status == 'cancelled') {
                $message_body .= "Your order has been cancelled. If you did not request this cancellation, please contact our customer service.\n\n";
            }
            
            $message_body .= "Order Total: $" . number_format($customer['total_amount'], 2) . "\n\n";
            $message_body .= "Thank you for shopping with Artizo!\n\n";
            $message_body .= "Best regards,\nThe Artizo Team";
            
            $headers = "From: noreply@artizo.com";
            
            // Send email
            if (mail($to, $subject, $message_body, $headers)) {
                $message = "Order status updated to " . ucfirst($new_status) . " and notification email sent.";
            } else {
                $message = "Order status updated to " . ucfirst($new_status) . " but email notification failed.";
            }
        } else {
            $message = "Order status updated to " . ucfirst($new_status) . ".";
        }
    } else {
        $message = "Error updating order status: " . mysqli_error($conn);
    }
}

// Get orders with filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = $status_filter ? "WHERE o.status = '$status_filter'" : "";

$query = "SELECT o.*, u.first_name, u.last_name, u.email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          $where_clause
          ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Artizo Admin</title>
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
                <a href="orders.php" class="active"><i class="fas fa-shopping-cart mr-2"></i> Orders</a>
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
                <h1 class="text-2xl font-bold">Orders Management</h1>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
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
            
            <!-- Filter Options -->
            <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                <h2 class="text-lg font-semibold mb-3">Filter Orders</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="orders.php" class="px-4 py-2 rounded-md <?php echo empty($status_filter) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">All</a>
                    <a href="orders.php?status=pending" class="px-4 py-2 rounded-md <?php echo $status_filter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Pending</a>
                    <a href="orders.php?status=processing" class="px-4 py-2 rounded-md <?php echo $status_filter === 'processing' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Processing</a>
                    <a href="orders.php?status=shipped" class="px-4 py-2 rounded-md <?php echo $status_filter === 'shipped' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Shipped</a>
                    <a href="orders.php?status=delivered" class="px-4 py-2 rounded-md <?php echo $status_filter === 'delivered' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Delivered</a>
                    <a href="orders.php?status=cancelled" class="px-4 py-2 rounded-md <?php echo $status_filter === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Cancelled</a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">#<?php echo $order['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            if ($order['user_id']) {
                                                echo $order['first_name'] . ' ' . $order['last_name'];
                                                echo '<br><span class="text-xs text-gray-500">' . $order['email'] . '</span>';
                                            } else {
                                                echo 'Guest';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($order['total_amount'], 2); ?></td>
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
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                                <button type="button" class="text-green-600 hover:text-green-900" onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">Update Status</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-xl font-bold mb-4">Update Order Status</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="order_id" id="modal_order_id">
                
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="modal_status" class="w-full border rounded-md px-3 py-2">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border rounded-md">Cancel</button>
                    <button type="submit" name="update_status" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function openStatusModal(orderId, currentStatus) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('modal_status').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
    </script>
</body>
</html> 