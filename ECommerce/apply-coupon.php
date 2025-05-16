<?php
session_start();
include 'config/db.php';

// Redirect to cart if not accessed properly
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['coupon_code'])) {
    header('Location: cart.php');
    exit;
}

$coupon_code = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['coupon_code'])));

// Check if coupon code is empty
if (empty($coupon_code)) {
    $_SESSION['error'] = "Please enter a coupon code.";
    header('Location: cart.php');
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    $_SESSION['error'] = "Your cart is empty. Add products before applying a coupon.";
    header('Location: cart.php');
    exit;
}

// Calculate cart subtotal for minimum order validation
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Validate coupon
$query = "SELECT * FROM coupons WHERE code = '$coupon_code' AND status = 'active'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "Invalid or inactive coupon code.";
    header('Location: cart.php');
    exit;
}

$coupon = mysqli_fetch_assoc($result);

// Check if coupon is expired
$today = date('Y-m-d');
if (!empty($coupon['start_date']) && $today < $coupon['start_date']) {
    $_SESSION['error'] = "This coupon is not valid yet.";
    header('Location: cart.php');
    exit;
}

if (!empty($coupon['end_date']) && $today > $coupon['end_date']) {
    $_SESSION['error'] = "This coupon has expired.";
    header('Location: cart.php');
    exit;
}

// Check usage limit
if (!empty($coupon['usage_limit']) && $coupon['used_count'] >= $coupon['usage_limit']) {
    $_SESSION['error'] = "This coupon has reached its usage limit.";
    header('Location: cart.php');
    exit;
}

// Check minimum order value
if ($subtotal < $coupon['min_order_value']) {
    $_SESSION['error'] = "This coupon requires a minimum order of ৳" . number_format($coupon['min_order_value'], 2) . ".";
    header('Location: cart.php');
    exit;
}

// Store coupon in session
$_SESSION['coupon'] = [
    'id' => $coupon['id'],
    'code' => $coupon['code'],
    'type' => $coupon['type'],
    'value' => $coupon['value'],
    'max_discount_value' => $coupon['max_discount_value']
];

// Calculate discount
if ($coupon['type'] === 'percentage') {
    $discount = $subtotal * ($coupon['value'] / 100);
    
    // Apply maximum discount if set
    if (!empty($coupon['max_discount_value']) && $discount > $coupon['max_discount_value']) {
        $discount = $coupon['max_discount_value'];
    }
} else {
    // Fixed amount discount
    $discount = $coupon['value'];
    
    // Ensure discount doesn't exceed cart total
    if ($discount > $subtotal) {
        $discount = $subtotal;
    }
}

$_SESSION['coupon']['discount_amount'] = $discount;

$_SESSION['success'] = "Coupon applied successfully! You saved ৳" . number_format($discount, 2) . ".";
header('Location: cart.php');
exit;
?> 