<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// Process order status update
$message = '';
if (isset($_POST['update_status'])) {
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

// Get order details
$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE o.id = $order_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: orders.php');
    exit;
}

$order = mysqli_fetch_assoc($result);

// Get order items
$items_query = "SELECT oi.*, p.name, p.image, p.sku 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

// Check if shipping_addresses table exists
$shipping_address = null;
$billing_address = null;

$table_check_query = "SHOW TABLES LIKE 'shipping_addresses'";
$table_check_result = mysqli_query($conn, $table_check_query);

if ($table_check_result && mysqli_num_rows($table_check_result) > 0) {
    // Get shipping address from shipping_addresses table
    $shipping_query = "SELECT * FROM shipping_addresses WHERE order_id = $order_id";
    $shipping_result = mysqli_query($conn, $shipping_query);
    
    if ($shipping_result && mysqli_num_rows($shipping_result) > 0) {
        $shipping_address = mysqli_fetch_assoc($shipping_result);
    }

    // Get billing address from billing_addresses table if it exists
    $table_check_query = "SHOW TABLES LIKE 'billing_addresses'";
    $table_check_result = mysqli_query($conn, $table_check_query);
    
    if ($table_check_result && mysqli_num_rows($table_check_result) > 0) {
        $billing_query = "SELECT * FROM billing_addresses WHERE order_id = $order_id";
        $billing_result = mysqli_query($conn, $billing_query);
        
        if ($billing_result && mysqli_num_rows($billing_result) > 0) {
            $billing_address = mysqli_fetch_assoc($billing_result);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Artizo Admin</title>
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
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
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
                <div>
                    <h1 class="text-2xl font-bold">Order #<?php echo $order_id; ?></h1>
                    <p class="text-gray-600">Placed on <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                </div>
                <div>
                    <a href="orders.php" class="text-blue-600 hover:text-blue-800"><i class="fas fa-arrow-left mr-1"></i> Back to Orders</a>
                </div>
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
            
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">                <!-- Order Status -->                <div class="bg-white rounded-lg shadow-sm p-6 lg:col-span-3">                    <div class="flex justify-between items-center">                        <div>                            <h2 class="text-lg font-semibold">Order Status</h2>                            <div class="mt-2">                                <span class="status-badge                                     <?php                                    switch ($order['status']) {                                        case 'pending':                                            echo 'bg-yellow-100 text-yellow-800';                                            break;                                        case 'processing':                                            echo 'bg-blue-100 text-blue-800';                                            break;                                        case 'shipped':                                            echo 'bg-purple-100 text-purple-800';                                            break;                                        case 'delivered':                                            echo 'bg-green-100 text-green-800';                                            break;                                        case 'cancelled':                                            echo 'bg-red-100 text-red-800';                                            break;                                        default:                                            echo 'bg-gray-100 text-gray-800';                                    }                                    ?>                                ">                                    <?php echo ucfirst($order['status']); ?>                                </span>                                                                <span class="status-badge ml-2                                    <?php                                    switch ($order['payment_status']) {                                        case 'paid':                                            echo 'bg-green-100 text-green-800';                                            break;                                        case 'pending':                                            echo 'bg-yellow-100 text-yellow-800';                                            break;                                        case 'failed':                                            echo 'bg-red-100 text-red-800';                                            break;                                        default:                                            echo 'bg-gray-100 text-gray-800';                                    }                                    ?>                                ">                                    Payment: <?php echo ucfirst($order['payment_status']); ?>                                </span>                            </div>                        </div>                        <div>                            <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" onclick="openStatusModal()">                                Update Status                            </button>                        </div>                    </div>                </div>                                <!-- Customer Information -->                <div class="bg-white rounded-lg shadow-sm p-6">                    <h2 class="text-lg font-semibold mb-4">Customer Information</h2>                    <?php if ($order['user_id']): ?>                        <p><strong>Name:</strong> <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>                        <p><strong>Email:</strong> <?php echo $order['email']; ?></p>                        <p><strong>Phone:</strong> <?php echo $order['phone'] ?? 'N/A'; ?></p>                        <p><strong>Customer ID:</strong> <?php echo $order['user_id']; ?></p>                        <p><strong>Customer Type:</strong> Registered</p>                    <?php else: ?>                        <p><strong>Customer Type:</strong> Guest Checkout</p>                        <p><strong>Name:</strong> <?php echo $order['shipping_first_name'] . ' ' . $order['shipping_last_name']; ?></p>                        <p><strong>Email:</strong> <?php echo $order['email']; ?></p>                    <?php endif; ?>                </div>                                <!-- Shipping Address -->                <div class="bg-white rounded-lg shadow-sm p-6">                    <h2 class="text-lg font-semibold mb-4">Shipping Address</h2>                    <?php if ($shipping_address): ?>                        <address class="not-italic">                            <p class="font-medium"><?php echo $shipping_address['first_name'] . ' ' . $shipping_address['last_name']; ?></p>                            <p><?php echo $shipping_address['street']; ?></p>                            <?php if (!empty($shipping_address['street2'])): ?>                                <p><?php echo $shipping_address['street2']; ?></p>                            <?php endif; ?>                            <p><?php echo $shipping_address['city'] . ', ' . $shipping_address['state'] . ' ' . $shipping_address['zip_code']; ?></p>                            <p><?php echo $shipping_address['country']; ?></p>                            <?php if (!empty($shipping_address['phone'])): ?>                                <p class="mt-2">Phone: <?php echo $shipping_address['phone']; ?></p>                            <?php endif; ?>                        </address>                    <?php elseif (!empty($order['shipping_first_name'])): ?>                        <address class="not-italic">                            <p class="font-medium"><?php echo $order['shipping_first_name'] . ' ' . $order['shipping_last_name']; ?></p>                            <p><?php echo $order['shipping_address']; ?></p>                            <?php if (!empty($order['shipping_address2'])): ?>                                <p><?php echo $order['shipping_address2']; ?></p>                            <?php endif; ?>                            <p><?php echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zip']; ?></p>                            <p><?php echo $order['shipping_country']; ?></p>                            <?php if (!empty($order['shipping_phone'])): ?>                                <p class="mt-2">Phone: <?php echo $order['shipping_phone']; ?></p>                            <?php endif; ?>                        </address>                    <?php else: ?>                        <p class="text-gray-600 italic">No shipping address information available</p>                    <?php endif; ?>                </div>                                <!-- Billing Address -->                <div class="bg-white rounded-lg shadow-sm p-6">                    <h2 class="text-lg font-semibold mb-4">Billing Address</h2>                    <?php if ($billing_address): ?>                        <address class="not-italic">                            <p class="font-medium"><?php echo $billing_address['first_name'] . ' ' . $billing_address['last_name']; ?></p>                            <p><?php echo $billing_address['street']; ?></p>                            <?php if (!empty($billing_address['street2'])): ?>                                <p><?php echo $billing_address['street2']; ?></p>                            <?php endif; ?>                            <p><?php echo $billing_address['city'] . ', ' . $billing_address['state'] . ' ' . $billing_address['zip_code']; ?></p>                            <p><?php echo $billing_address['country']; ?></p>                            <?php if (!empty($billing_address['phone'])): ?>                                <p class="mt-2">Phone: <?php echo $billing_address['phone']; ?></p>                            <?php endif; ?>                        </address>                    <?php elseif (!empty($order['billing_first_name'])): ?>                        <address class="not-italic">                            <p class="font-medium"><?php echo $order['billing_first_name'] . ' ' . $order['billing_last_name']; ?></p>                            <p><?php echo $order['billing_address']; ?></p>                            <?php if (!empty($order['billing_address2'])): ?>                                <p><?php echo $order['billing_address2']; ?></p>                            <?php endif; ?>                            <p><?php echo $order['billing_city'] . ', ' . $order['billing_state'] . ' ' . $order['billing_zip']; ?></p>                            <p><?php echo $order['billing_country']; ?></p>                            <?php if (!empty($order['billing_phone'])): ?>                                <p class="mt-2">Phone: <?php echo $order['billing_phone']; ?></p>                            <?php endif; ?>                        </address>                    <?php else: ?>                        <p class="text-gray-600 italic">No billing address information available</p>                    <?php endif; ?>                </div>
            </div>
            
            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h2 class="text-lg font-semibold mb-4">Order Items</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $subtotal = 0;
                            while ($item = mysqli_fetch_assoc($items_result)): 
                                $item_total = $item['price'] * $item['quantity'];
                                $subtotal += $item_total;
                            ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 object-cover rounded" src="../assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo $item['name']; ?></div>
                                                <?php if (!empty($item['attributes'])): ?>
                                                    <div class="text-sm text-gray-500">
                                                        <?php 
                                                        $attributes = json_decode($item['attributes'], true);
                                                        if ($attributes) {
                                                            foreach ($attributes as $key => $value) {
                                                                echo ucfirst($key) . ': ' . $value . '<br>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['sku']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['quantity']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($item_total, 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium">Subtotal:</td>
                                <td class="px-6 py-3 text-sm">$<?php echo number_format($subtotal, 2); ?></td>
                            </tr>
                            <?php if ($order['discount_amount'] > 0): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium">Discount:</td>
                                <td class="px-6 py-3 text-sm text-red-600">-$<?php echo number_format($order['discount_amount'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium">Shipping:</td>
                                <td class="px-6 py-3 text-sm">$<?php echo number_format($order['shipping_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium">Tax:</td>
                                <td class="px-6 py-3 text-sm">$<?php echo number_format($order['tax_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right font-bold">Total:</td>
                                <td class="px-6 py-3 font-bold">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Order Notes -->
            <?php if (!empty($order['notes'])): ?>
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h2 class="text-lg font-semibold mb-4">Order Notes</h2>
                <p><?php echo nl2br($order['notes']); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Admin Notes Form -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Admin Notes</h2>
                <form action="update-admin-notes.php" method="post">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <div class="mb-4">
                        <textarea name="admin_notes" rows="4" class="w-full border rounded-md px-3 py-2"><?php echo $order['admin_notes'] ?? ''; ?></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save Notes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-xl font-bold mb-4">Update Order Status</h2>
            <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $order_id; ?>" method="post">
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full border rounded-md px-3 py-2">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
        function openStatusModal() {
            document.getElementById('statusModal').classList.remove('hidden');
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
    </script>
</body>
</html> 