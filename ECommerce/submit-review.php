<?php
session_start();
include 'config/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit a review.";
    header('Location: login.php');
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validate form data
if (!isset($_POST['product_id']) || !isset($_POST['rating']) || !isset($_POST['comment'])) {
    $_SESSION['error'] = "Please fill in all review fields.";
    
    // Redirect back to product page if we have product_id
    if (isset($_POST['product_id'])) {
        // Get product slug from ID for redirection
        $product_id = (int)$_POST['product_id'];
        $query = "SELECT slug FROM products WHERE id = $product_id";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $product = mysqli_fetch_assoc($result);
            header("Location: product.php?slug=" . $product['slug'] . "#reviews");
            exit;
        }
    }
    
    header('Location: shop.php');
    exit;
}

// Get and sanitize form data
$product_id = (int)$_POST['product_id'];
$user_id = (int)$_SESSION['user_id'];
$rating = (int)$_POST['rating'];
$comment = mysqli_real_escape_string($conn, $_POST['comment']);

// Validate rating range
if ($rating < 1 || $rating > 5) {
    $_SESSION['error'] = "Rating must be between 1 and 5.";
    
    // Get product slug from ID for redirection
    $query = "SELECT slug FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        header("Location: product.php?slug=" . $product['slug'] . "#reviews");
        exit;
    }
    
    header('Location: shop.php');
    exit;
}

// Check if product exists
$product_query = "SELECT id, name, slug FROM products WHERE id = $product_id";
$product_result = mysqli_query($conn, $product_query);

if (!$product_result || mysqli_num_rows($product_result) == 0) {
    $_SESSION['error'] = "Invalid product selected.";
    header('Location: shop.php');
    exit;
}

$product = mysqli_fetch_assoc($product_result);

// Check if user already submitted a review for this product
$existing_review_query = "SELECT id FROM reviews WHERE user_id = $user_id AND product_id = $product_id";
$existing_review_result = mysqli_query($conn, $existing_review_query);

if ($existing_review_result && mysqli_num_rows($existing_review_result) > 0) {
    $_SESSION['error'] = "You have already submitted a review for this product.";
    header("Location: product.php?slug=" . $product['slug'] . "#reviews");
    exit;
}

// Insert review with pending status
$status = 'pending'; // Default status for new reviews - admin will approve
$insert_query = "INSERT INTO reviews (product_id, user_id, rating, comment, status, created_at) 
                VALUES ($product_id, $user_id, $rating, '$comment', '$status', NOW())";

if (mysqli_query($conn, $insert_query)) {
    $_SESSION['success'] = "Thank you! Your review has been submitted and is pending approval.";
} else {
    $_SESSION['error'] = "Error submitting review: " . mysqli_error($conn);
}

// Redirect back to product page
header("Location: product.php?slug=" . $product['slug'] . "#reviews");
exit;
?> 