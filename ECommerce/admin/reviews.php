<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle review status update (approve/reject)
if (isset($_POST['update_review'])) {
    $review_id = intval($_POST['review_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE reviews SET status = '$status' WHERE id = $review_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Review status updated successfully";
    } else {
        $_SESSION['error'] = "Error updating review status: " . mysqli_error($conn);
    }
    
    header('Location: reviews.php');
    exit;
}

// Handle review deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $review_id = intval($_GET['delete']);
    
    $delete_query = "DELETE FROM reviews WHERE id = $review_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Review deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting review: " . mysqli_error($conn);
    }
    
    header('Location: reviews.php');
    exit;
}

// Get reviews with filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = $status_filter ? "WHERE r.status = '$status_filter'" : "";

$query = "SELECT r.*, p.name as product_name, p.slug as product_slug, u.first_name, u.last_name, u.email 
          FROM reviews r 
          LEFT JOIN products p ON r.product_id = p.id 
          LEFT JOIN users u ON r.user_id = u.id 
          $where_clause
          ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Artizo Admin</title>
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
        .star-rating {
            color: #FFD700;
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
                <a href="orders.php"><i class="fas fa-shopping-cart mr-2"></i> Orders</a>
                <a href="customers.php"><i class="fas fa-users mr-2"></i> Customers</a>
                <a href="reviews.php" class="active"><i class="fas fa-star mr-2"></i> Reviews</a>
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
                <h1 class="text-2xl font-bold">Reviews Management</h1>
            </div>
            
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
                <h2 class="text-lg font-semibold mb-3">Filter Reviews</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="reviews.php" class="px-4 py-2 rounded-md <?php echo empty($status_filter) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">All</a>
                    <a href="reviews.php?status=pending" class="px-4 py-2 rounded-md <?php echo $status_filter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Pending</a>
                    <a href="reviews.php?status=approved" class="px-4 py-2 rounded-md <?php echo $status_filter === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Approved</a>
                    <a href="reviews.php?status=rejected" class="px-4 py-2 rounded-md <?php echo $status_filter === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">Rejected</a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($review = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $review['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="../product.php?slug=<?php echo $review['product_slug']; ?>" class="text-blue-600 hover:text-blue-900" target="_blank">
                                                <?php echo $review['product_name']; ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            if ($review['user_id']) {
                                                echo $review['first_name'] . ' ' . $review['last_name'];
                                                echo '<br><span class="text-xs text-gray-500">' . $review['email'] . '</span>';
                                            } else {
                                                echo $review['name'];
                                                echo '<br><span class="text-xs text-gray-500">' . $review['email'] . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="star-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $review['rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs truncate"><?php echo $review['comment']; ?></div>
                                            <button type="button" class="text-xs text-blue-600 hover:text-blue-900 mt-1" onclick="showReviewModal(<?php echo $review['id']; ?>, '<?php echo addslashes($review['comment']); ?>')">
                                                Read More
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                switch ($review['status']) {
                                                    case 'approved':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'pending':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'rejected':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>
                                            ">
                                                <?php echo ucfirst($review['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="post" class="inline-block mr-1">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" name="update_review" class="text-green-600 hover:text-green-900" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="post" class="inline-block mr-1">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_review" class="text-red-600 hover:text-red-900" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            <a href="reviews.php?delete=<?php echo $review['id']; ?>" class="text-red-600 hover:text-red-900" title="Delete" onclick="return confirm('Are you sure you want to delete this review?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center">No reviews found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Review Modal -->
    <div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Review Details</h3>
                <button type="button" onclick="closeReviewModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <p id="reviewText" class="whitespace-pre-line"></p>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeReviewModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Review modal functions
        function showReviewModal(id, reviewText) {
            document.getElementById('reviewText').textContent = reviewText;
            document.getElementById('reviewModal').classList.remove('hidden');
        }
        
        function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
        }
    </script>
</body>
</html> 