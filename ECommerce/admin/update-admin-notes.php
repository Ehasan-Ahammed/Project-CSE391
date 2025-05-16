<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
        $_SESSION['error'] = "Order ID is required";
        header('Location: orders.php');
        exit;
    }
    
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes'] ?? '');
    
    // Update admin notes
    $query = "UPDATE orders SET admin_notes = '$admin_notes' WHERE id = $order_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Admin notes updated successfully";
    } else {
        $_SESSION['error'] = "Error updating admin notes: " . mysqli_error($conn);
    }
    
    // Redirect back to order detail page
    header("Location: order-detail.php?id=$order_id");
    exit;
} else {
    // If accessed directly without POST data
    header('Location: orders.php');
    exit;
}
?> 