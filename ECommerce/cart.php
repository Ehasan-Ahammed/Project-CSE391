<?php
session_start();
include 'config/db.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $item_key = isset($_GET['item']) ? $_GET['item'] : '';
    
    if ($action === 'remove' && !empty($item_key) && isset($_SESSION['cart'][$item_key])) {
        // Remove item from cart
        unset($_SESSION['cart'][$item_key]);
    } elseif ($action === 'update' && isset($_POST['quantity'])) {
        // Update quantities
        foreach ($_POST['quantity'] as $key => $qty) {
            if (isset($_SESSION['cart'][$key])) {
                $qty = max(1, (int)$qty); // Ensure quantity is at least 1
                $_SESSION['cart'][$key]['quantity'] = $qty;
            }
        }
    } elseif ($action === 'clear') {
        // Clear cart
        $_SESSION['cart'] = [];
    } elseif ($action === 'remove_coupon') {
        // Remove coupon
        unset($_SESSION['coupon']);
        $_SESSION['success'] = "Coupon has been removed.";
    }
    
    // Redirect to avoid form resubmission
    header('Location: cart.php');
    exit;
}

// Include header after all redirects
include 'includes/header.php';

// Calculate cart totals
$subtotal = 0;
$item_count = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}

// Apply coupon if exists
$discount = 0;
$coupon_code = '';
if (isset($_SESSION['coupon'])) {
    $coupon_code = $_SESSION['coupon']['code'];
    $discount = $_SESSION['coupon']['discount_amount'];
}

// Calculate shipping (simplified)
$shipping = ($subtotal > 0) ? 80 : 0; // Default to Inside Dhaka (80 BDT)
// Calculate total (no tax)
$total = $subtotal - $discount + $shipping;
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
        
        <?php if (count($_SESSION['cart']) > 0): ?>
            <div class="flex flex-col lg:flex-row">
                <!-- Cart Items -->
                <div class="w-full lg:w-2/3 lg:pr-8">
                    <form action="cart.php?action=update" method="post">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($_SESSION['cart'] as $key => $item): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16">
                                                        <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="h-16 w-16 object-cover rounded">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo $item['name']; ?></div>
                                                        <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                                                            <div class="text-sm text-gray-500">
                                                                <?php if (!empty($item['size'])): ?>
                                                                    Size: <?php echo $item['size']; ?>
                                                                <?php endif; ?>
                                                                
                                                                <?php if (!empty($item['color'])): ?>
                                                                    <?php echo !empty($item['size']) ? ' / ' : ''; ?>
                                                                    Color: <?php echo $item['color']; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">৳<?php echo number_format($item['price'], 2); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="quantity-input w-24">
                                                    <button type="button" class="quantity-btn" onclick="decrementQuantity('<?php echo $key; ?>')">-</button>
                                                    <input type="number" name="quantity[<?php echo $key; ?>]" id="quantity-<?php echo $key; ?>" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-field">
                                                    <button type="button" class="quantity-btn" onclick="incrementQuantity('<?php echo $key; ?>')">+</button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="cart.php?action=remove&item=<?php echo $key; ?>" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-6 flex justify-between">
                            <div>
                                <a href="shop.php" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                                </a>
                            </div>
                            <div class="flex space-x-4">
                                <a href="cart.php?action=clear" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition">Clear Cart</a>
                                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-black transition">Update Cart</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Cart Summary -->
                <div class="w-full lg:w-1/3 mt-8 lg:mt-0">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Cart Summary</h2>
                        
                        <div class="border-b pb-4 mb-4">
                            <div class="flex justify-between mb-2">
                                <span>Subtotal (<?php echo $item_count; ?> items)</span>
                                <span>৳<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <?php if ($discount > 0): ?>
                            <div class="flex justify-between mb-2">
                                <span>Discount (<?php echo $coupon_code; ?>)</span>
                                <span class="text-red-600">-৳<?php echo number_format($discount, 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between">
                                <span>Shipping</span>
                                <span>৳<?php echo number_format($shipping, 2); ?></span>
                            </div>
                        </div>
                        <div class="flex justify-between mb-6">
                            <span class="font-semibold">Total</span>
                            <span class="font-semibold">৳<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="block w-full bg-black text-white text-center py-3 rounded-md hover:bg-gray-800 transition">Proceed to Checkout</a>
                        
                        <!-- Coupon Code -->
                        <div class="mt-6 pt-6 border-t">
                            <h3 class="text-sm font-semibold mb-2">Apply Coupon Code</h3>
                            <?php if ($discount > 0): ?>
                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                    <div>
                                        <span class="font-medium"><?php echo $coupon_code; ?></span>
                                        <span class="text-sm text-gray-600 ml-2">applied</span>
                                    </div>
                                    <a href="cart.php?action=remove_coupon" class="text-red-600 hover:text-red-800 text-sm">
                                        <i class="fas fa-times mr-1"></i> Remove
                                    </a>
                                </div>
                            <?php else: ?>
                                <form action="apply-coupon.php" method="post" class="flex">
                                    <input type="text" name="coupon_code" placeholder="Enter coupon code" class="flex-grow border rounded-l-md px-4 py-2 text-sm">
                                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-r-md hover:bg-black transition">Apply</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white p-8 rounded-lg shadow-sm text-center">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="text-2xl font-semibold mb-4">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">Looks like you haven't added any products to your cart yet.</p>
                <a href="shop.php" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-800 transition">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function incrementQuantity(key) {
    const input = document.getElementById('quantity-' + key);
    let value = parseInt(input.value);
    input.value = value + 1;
}

function decrementQuantity(key) {
    const input = document.getElementById('quantity-' + key);
    let value = parseInt(input.value);
    
    if (value > 1) {
        input.value = value - 1;
    }
}
</script>

<?php include 'includes/footer.php'; ?> 