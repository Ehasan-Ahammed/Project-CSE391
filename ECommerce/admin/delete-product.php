<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get product details to delete associated images
$query = "SELECT image FROM products WHERE id = $product_id";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
    
    // Delete main product image if it exists
    if (!empty($product['image'])) {
        $image_path = '../assets/images/products/' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete additional product images
    $images_query = "SELECT image FROM product_images WHERE product_id = $product_id";
    $images_result = mysqli_query($conn, $images_query);
    
    if ($images_result && mysqli_num_rows($images_result) > 0) {
        while ($image = mysqli_fetch_assoc($images_result)) {
            $image_path = '../assets/images/products/' . $image['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
    
    // Delete product attributes
    mysqli_query($conn, "DELETE FROM product_attributes WHERE product_id = $product_id");
    
    // Delete product images from database
    mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $product_id");
    
    // Delete the product
    $delete_query = "DELETE FROM products WHERE id = $product_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success_message'] = "Product deleted successfully";
    } else {
        $_SESSION['error_message'] = "Error deleting product: " . mysqli_error($conn);
    }
} else {
    $_SESSION['error_message'] = "Product not found";
}

// Redirect back to products page
header('Location: products.php');
exit;
?> 