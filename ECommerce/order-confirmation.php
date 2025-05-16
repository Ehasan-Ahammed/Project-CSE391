<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Check if order ID exists in session
if (!isset($_SESSION['last_order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['last_order_id'];

// Get order details
$order_query = "SELECT o.*, u.first_name, u.last_name, u.email 
               FROM orders o 
               LEFT JOIN users u ON o.user_id = u.id 
               WHERE o.id = $order_id";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header('Location: index.php');
    exit;
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, p.name, p.image 
               FROM order_items oi 
               LEFT JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

// Calculate subtotal
$subtotal = 0;
$items = [];
while ($item = mysqli_fetch_assoc($items_result)) {
    $subtotal += $item['price'] * $item['quantity'];
    $items[] = $item;
}

// Calculate shipping (simplified)$shipping = 80; // Default to Inside Dhaka (80 BDT)

// Clear the order ID from session to prevent refreshing
unset($_SESSION['last_order_id']);
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-8 text-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold mb-2">Thank You for Your Order!</h1>
                <p class="text-gray-600 mb-4">Your order has been placed successfully.</p>
                <p class="font-medium">Order #<?php echo $order_id; ?></p>
                <p class="text-sm text-gray-600 mt-2">A confirmation email has been sent to <?php echo $order['email']; ?></p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Order Details</h2>
                
                <div class="border-b pb-4 mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Order Number:</span>
                        <span><?php echo $order_id; ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Date:</span>
                        <span><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Payment Method:</span>
                        <span><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Payment Status:</span>
                        <span class="<?php echo $order['payment_status'] === 'paid' ? 'text-green-600' : 'text-yellow-600'; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                </div>
                
                <h3 class="font-semibold mb-3">Items Ordered</h3>
                <div class="border-b pb-4 mb-4">
                    <?php foreach ($items as $item): ?>
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center">
                                <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="w-12 h-12 object-cover rounded">
                                <div class="ml-3">
                                    <div class="font-medium"><?php echo $item['name']; ?></div>
                                    <?php if (!empty($item['attributes'])): ?>
                                        <div class="text-xs text-gray-600"><?php echo $item['attributes']; ?></div>
                                    <?php endif; ?>
                                    <div class="text-sm">Qty: <?php echo $item['quantity']; ?></div>
                                </div>
                            </div>
                                                        <div class="text-right">                                <div>৳<?php echo number_format($item['price'], 2); ?></div>                                <div class="font-medium">৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                                <div class="flex justify-between mb-2">                    <span>Subtotal</span>                    <span>৳<?php echo number_format($subtotal, 2); ?></span>                </div>                <div class="flex justify-between mb-4">                    <span>Shipping</span>                    <span>৳<?php echo number_format($shipping, 2); ?></span>                </div>                <div class="flex justify-between font-semibold text-lg">                    <span>Total</span>                    <span>৳<?php echo number_format($order['total_amount'], 2); ?></span>                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Shipping Information</h2>
                
                <div class="mb-4">
                    <div class="font-medium"><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></div>
                    <div><?php echo $order['shipping_address']; ?></div>
                    <div><?php echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_postal_code']; ?></div>
                    <div><?php echo $order['shipping_country']; ?></div>
                    <div>Phone: <?php echo $order['shipping_phone']; ?></div>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-2">Estimated Delivery</h3>
                    <?php
                    $delivery_date = strtotime('+3 days', strtotime($order['created_at']));
                    $delivery_date_end = strtotime('+7 days', strtotime($order['created_at']));
                    ?>
                    <p><?php echo date('F j', $delivery_date); ?> - <?php echo date('F j, Y', $delivery_date_end); ?></p>
                </div>
            </div>
            
            <div class="flex justify-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="orders.php" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-800 transition">View All Orders</a>
                <?php endif; ?>
                <a href="index.php" class="border border-black px-6 py-3 rounded-md hover:bg-gray-100 transition">Continue Shopping</a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 