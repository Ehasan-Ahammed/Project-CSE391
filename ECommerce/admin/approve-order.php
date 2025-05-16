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
        header('Location: index.php');
        exit;
    }
    
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    
    // Update order status to processing
    $update_query = "UPDATE orders SET status = 'processing' WHERE id = $order_id";
    
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
            $subject = "Your Artizo Order #$order_id Has Been Approved";
            
            $message_body = "Dear {$customer['first_name']} {$customer['last_name']},\n\n";
            $message_body .= "Great news! Your order #$order_id has been approved and is now being processed.\n\n";
            $message_body .= "We are preparing your items for shipment and will notify you once they're on the way.\n\n";
            $message_body .= "Order Total: $" . number_format($customer['total_amount'], 2) . "\n\n";
            $message_body .= "You can track your order status by logging into your account at our website.\n\n";
            $message_body .= "Thank you for shopping with Artizo!\n\n";
            $message_body .= "Best regards,\nThe Artizo Team";
            
            $headers = "From: noreply@artizo.com";
            
            // Send email
            if (mail($to, $subject, $message_body, $headers)) {
                $_SESSION['success'] = "Order #$order_id has been approved and notification email sent.";
            } else {
                $_SESSION['success'] = "Order #$order_id has been approved but email notification failed.";
            }
        } else {
            $_SESSION['success'] = "Order #$order_id has been approved.";
        }
    } else {
        $_SESSION['error'] = "Error approving order: " . mysqli_error($conn);
    }
    
    // Redirect back to dashboard
    header('Location: index.php');
    exit;
} else {
    // If accessed directly without POST data
    header('Location: index.php');
    exit;
}
?> 