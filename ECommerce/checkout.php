<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header('Location: cart.php');
    exit;
}

// Get user data if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user_query = "SELECT * FROM users WHERE id = {$_SESSION['user_id']}";
    $user_result = mysqli_query($conn, $user_query);
    if (mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);
    }
}

// Calculate cart totals
$subtotal = 0;
$item_count = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}

// Default shipping rate (will be updated based on user selection)
$shipping = ($subtotal > 0) ? 80 : 0; // Default to Inside Dhaka (80 BDT)

// Calculate total (no tax)
$total = $subtotal + $shipping;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $errors = [];
    
    // Required fields
    $required_fields = [
        'first_name', 'last_name', 'email', 'phone',
        'address', 'city', 'state', 'postal_code', 'country',
        'payment_method'
    ];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Email validation
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($errors)) {
        // Process the order
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $payment_method = $_POST['payment_method'];
        $payment_status = ($payment_method === 'cod') ? 'pending' : 'paid';
        
        // Create order in database
        $order_sql = "INSERT INTO orders (
            user_id, total_amount, status, shipping_address, shipping_city, 
            shipping_state, shipping_postal_code, shipping_country, shipping_phone,
            payment_method, payment_status
        ) VALUES (
            " . ($user_id ? $user_id : 'NULL') . ", 
            $total, 
            'pending', 
            '" . mysqli_real_escape_string($conn, $_POST['address']) . "', 
            '" . mysqli_real_escape_string($conn, $_POST['city']) . "', 
            '" . mysqli_real_escape_string($conn, $_POST['state']) . "', 
            '" . mysqli_real_escape_string($conn, $_POST['postal_code']) . "', 
            '" . mysqli_real_escape_string($conn, $_POST['country']) . "', 
            '" . mysqli_real_escape_string($conn, $_POST['phone']) . "', 
            '" . mysqli_real_escape_string($conn, $payment_method) . "', 
            '$payment_status'
        )";
        
        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);
            
            // Add order items
            foreach ($_SESSION['cart'] as $item) {
                $attributes = '';
                if (!empty($item['size']) || !empty($item['color'])) {
                    $attr_array = [];
                    if (!empty($item['size'])) $attr_array[] = 'Size: ' . $item['size'];
                    if (!empty($item['color'])) $attr_array[] = 'Color: ' . $item['color'];
                    $attributes = implode(', ', $attr_array);
                }
                
                $item_sql = "INSERT INTO order_items (
                    order_id, product_id, quantity, price, attributes
                ) VALUES (
                    $order_id, 
                    {$item['id']}, 
                    {$item['quantity']}, 
                    {$item['price']}, 
                    '" . mysqli_real_escape_string($conn, $attributes) . "'
                )";
                
                mysqli_query($conn, $item_sql);
                
                // Update product quantity (reduce stock)
                $update_stock_sql = "UPDATE products SET quantity = quantity - {$item['quantity']} WHERE id = {$item['id']}";
                mysqli_query($conn, $update_stock_sql);
            }
            
            // Clear the cart
            $_SESSION['cart'] = [];
            
            // Store order ID in session for confirmation page
            $_SESSION['last_order_id'] = $order_id;
            
            // Redirect to order confirmation page
            header('Location: order-confirmation.php');
            exit;
        } else {
            $errors[] = 'Error processing your order. Please try again.';
        }
    }
}
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-col lg:flex-row">
            <!-- Checkout Form -->
            <div class="w-full lg:w-2/3 lg:pr-8">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <!-- Billing Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-semibold mb-4">Billing Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo isset($user) ? $user['first_name'] : (isset($_POST['first_name']) ? $_POST['first_name'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo isset($user) ? $user['last_name'] : (isset($_POST['last_name']) ? $_POST['last_name'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($user) ? $user['email'] : (isset($_POST['email']) ? $_POST['email'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo isset($user) ? $user['phone'] : (isset($_POST['phone']) ? $_POST['phone'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                                <input type="text" id="address" name="address" value="<?php echo isset($user) ? $user['address'] : (isset($_POST['address']) ? $_POST['address'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                                <input type="text" id="city" name="city" value="<?php echo isset($user) ? $user['city'] : (isset($_POST['city']) ? $_POST['city'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State/Province *</label>
                                <input type="text" id="state" name="state" value="<?php echo isset($user) ? $user['state'] : (isset($_POST['state']) ? $_POST['state'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Postal Code *</label>
                                <input type="text" id="postal_code" name="postal_code" value="<?php echo isset($user) ? $user['postal_code'] : (isset($_POST['postal_code']) ? $_POST['postal_code'] : ''); ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country *</label>
                                                                <select id="country" name="country" class="w-full border rounded-md px-4 py-2" required>                                    <option value="">Select Country</option>                                    <option value="BD" <?php echo (isset($user) && $user['country'] === 'BD') || (isset($_POST['country']) && $_POST['country'] === 'BD') ? 'selected' : ''; ?> selected>Bangladesh</option>                                    <option value="US" <?php echo (isset($user) && $user['country'] === 'US') || (isset($_POST['country']) && $_POST['country'] === 'US') ? 'selected' : ''; ?>>United States</option>                                    <option value="IN" <?php echo (isset($user) && $user['country'] === 'IN') || (isset($_POST['country']) && $_POST['country'] === 'IN') ? 'selected' : ''; ?>>India</option>                                    <option value="PK" <?php echo (isset($user) && $user['country'] === 'PK') || (isset($_POST['country']) && $_POST['country'] === 'PK') ? 'selected' : ''; ?>>Pakistan</option>                                    <option value="NP" <?php echo (isset($user) && $user['country'] === 'NP') || (isset($_POST['country']) && $_POST['country'] === 'NP') ? 'selected' : ''; ?>>Nepal</option>                                    <!-- Add more countries as needed -->                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="create_account" class="form-checkbox" <?php echo isset($_POST['create_account']) ? 'checked' : ''; ?>>
                                <span class="ml-2">Create an account for future purchases</span>
                            </label>
                        </div>
                    </div>
                    
                                        <!-- Shipping Method -->                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">                        <h2 class="text-lg font-semibold mb-4">Shipping Method</h2>                                                <div class="space-y-4">                            <label class="flex items-center p-4 border rounded-md cursor-pointer">                                <input type="radio" name="shipping_method" value="inside_dhaka" class="form-radio shipping-option" data-cost="80" checked>                                <div class="ml-3">                                    <div class="text-sm font-medium text-gray-900">Inside Dhaka City</div>                                    <div class="text-sm text-gray-500">2-3 business days</div>                                </div>                                <div class="ml-auto font-medium">৳80.00</div>                            </label>                                                        <label class="flex items-center p-4 border rounded-md cursor-pointer">                                <input type="radio" name="shipping_method" value="outside_dhaka" class="form-radio shipping-option" data-cost="150">                                <div class="ml-3">                                    <div class="text-sm font-medium text-gray-900">Outside Dhaka District</div>                                    <div class="text-sm text-gray-500">3-5 business days</div>                                </div>                                <div class="ml-auto font-medium">৳150.00</div>                            </label>                                                        <label class="flex items-center p-4 border rounded-md cursor-pointer">                                <input type="radio" name="shipping_method" value="express" class="form-radio shipping-option" data-cost="300">                                <div class="ml-3">                                    <div class="text-sm font-medium text-gray-900">Express Shipping (Nationwide)</div>                                    <div class="text-sm text-gray-500">1-2 business days</div>                                </div>                                <div class="ml-auto font-medium">৳300.00</div>                            </label>                        </div>                    </div>
                    
                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-semibold mb-4">Payment Method</h2>
                        
                        <div class="space-y-4">
                            <label class="flex items-center p-4 border rounded-md cursor-pointer">
                                <input type="radio" name="payment_method" value="credit_card" class="form-radio" checked>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Credit Card</div>
                                    <div class="text-sm text-gray-500">Visa, Mastercard, American Express</div>
                                </div>
                                <div class="ml-auto">
                                    <i class="fab fa-cc-visa text-blue-700 text-2xl mr-1"></i>
                                    <i class="fab fa-cc-mastercard text-red-600 text-2xl mr-1"></i>
                                    <i class="fab fa-cc-amex text-blue-500 text-2xl"></i>
                                </div>
                            </label>
                            
                            <div id="credit-card-form" class="p-4 border rounded-md ml-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label for="card_number" class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" class="w-full border rounded-md px-4 py-2">
                                    </div>
                                    
                                    <div>
                                        <label for="card_name" class="block text-sm font-medium text-gray-700 mb-1">Name on Card</label>
                                        <input type="text" id="card_name" name="card_name" class="w-full border rounded-md px-4 py-2">
                                    </div>
                                    
                                    <div class="flex space-x-4">
                                        <div class="w-1/2">
                                            <label for="card_expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                                            <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" class="w-full border rounded-md px-4 py-2">
                                        </div>
                                        
                                        <div class="w-1/2">
                                            <label for="card_cvv" class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                                            <input type="text" id="card_cvv" name="card_cvv" placeholder="123" class="w-full border rounded-md px-4 py-2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <label class="flex items-center p-4 border rounded-md cursor-pointer">
                                <input type="radio" name="payment_method" value="paypal" class="form-radio">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">PayPal</div>
                                    <div class="text-sm text-gray-500">Pay with your PayPal account</div>
                                </div>
                                <div class="ml-auto">
                                    <i class="fab fa-paypal text-blue-800 text-2xl"></i>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border rounded-md cursor-pointer">
                                <input type="radio" name="payment_method" value="cod" class="form-radio">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Cash on Delivery</div>
                                    <div class="text-sm text-gray-500">Pay when you receive your order</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Order Notes -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-semibold mb-4">Order Notes</h2>
                        
                        <div>
                            <label for="order_notes" class="block text-sm font-medium text-gray-700 mb-1">Special instructions for delivery</label>
                            <textarea id="order_notes" name="order_notes" rows="3" class="w-full border rounded-md px-4 py-2"><?php echo isset($_POST['order_notes']) ? $_POST['order_notes'] : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-black text-white py-3 rounded-md hover:bg-gray-800 transition">Place Order</button>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="w-full lg:w-1/3 mt-8 lg:mt-0">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                    <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                    
                    <div class="border-b pb-4 mb-4">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="flex justify-between mb-2">
                                <div class="flex items-start">
                                    <span class="text-sm"><?php echo $item['name']; ?></span>
                                    <span class="text-gray-500 text-xs ml-1">x<?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="text-sm">৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                                        <div class="border-b pb-4 mb-4">                        <div class="flex justify-between mb-2">                                                        <span>Subtotal</span>                                                        <span>৳<?php echo number_format($subtotal, 2); ?></span>                                                </div>                                                <div class="flex justify-between">                                                        <span>Shipping</span>                                                        <span class="shipping-cost">৳<?php echo number_format($shipping, 2); ?></span>                                                </div>                    </div>
                    
                                        <div class="flex justify-between mb-4">                        <span class="font-semibold">Total</span>                        <span class="font-semibold order-total">৳<?php echo number_format($total, 2); ?></span>                    </div>
                    
                    <div class="text-sm text-gray-600">
                        <p>By placing your order, you agree to our <a href="terms.php" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="privacy-policy.php" class="text-blue-600 hover:underline">Privacy Policy</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>document.addEventListener('DOMContentLoaded', function() {    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');    const creditCardForm = document.getElementById('credit-card-form');        paymentMethodRadios.forEach(radio => {        radio.addEventListener('change', function() {            if (this.value === 'credit_card') {                creditCardForm.style.display = 'block';            } else {                creditCardForm.style.display = 'none';            }        });    });        // Shipping cost update functionality    const shippingOptions = document.querySelectorAll('.shipping-option');    const shippingCostElement = document.querySelector('.shipping-cost');    const totalElement = document.querySelector('.order-total');    const subtotalValue = <?php echo $subtotal; ?>;        // Function to update order totals    function updateOrderTotals() {        // Get selected shipping cost        const selectedShipping = document.querySelector('.shipping-option:checked');        const shippingCost = selectedShipping ? parseFloat(selectedShipping.dataset.cost) : 80;                // Calculate total (no tax)        const total = (subtotalValue + shippingCost).toFixed(2);                // Update display        if (shippingCostElement) {            shippingCostElement.textContent = '৳' + shippingCost.toFixed(2);        }                if (totalElement) {            totalElement.textContent = '৳' + total;        }    }        // Add event listeners to shipping options    shippingOptions.forEach(option => {        option.addEventListener('change', updateOrderTotals);    });        // Initialize with default values    updateOrderTotals();});
</script>

<?php include 'includes/footer.php'; ?> 