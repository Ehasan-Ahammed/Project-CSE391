<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get registered users/customers
$query = "SELECT u.*, 
            (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
            (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id) as total_spent
          FROM users u
          ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $query);

// Get total customer count
$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = mysqli_query($conn, $count_query);
$total_customers = mysqli_fetch_assoc($count_result)['total'];

// Get active customers (placed at least one order)
$active_query = "SELECT COUNT(DISTINCT user_id) as active FROM orders WHERE user_id IS NOT NULL";
$active_result = mysqli_query($conn, $active_query);
$active_customers = mysqli_fetch_assoc($active_result)['active'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Artizo Admin</title>
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
                <a href="customers.php" class="active"><i class="fas fa-users mr-2"></i> Customers</a>
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
                <h1 class="text-2xl font-bold">Customers Management</h1>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Total Customers</h3>
                            <p class="text-3xl font-bold"><?php echo $total_customers; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4">
                            <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Active Customers</h3>
                            <p class="text-3xl font-bold"><?php echo $active_customers; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-purple-100 p-3 mr-4">
                            <i class="fas fa-percentage text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Conversion Rate</h3>
                            <p class="text-3xl font-bold">
                                <?php 
                                if ($total_customers > 0) {
                                    echo round(($active_customers / $total_customers) * 100) . '%';
                                } else {
                                    echo '0%';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Registered Customers</h2>
                    <div class="relative">
                        <input type="text" id="customerSearch" placeholder="Search customers..." class="border rounded-md px-4 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="customersTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered On</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($customer = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $customer['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $customer['email']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $customer['phone'] ?? 'N/A'; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $customer['order_count']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($customer['total_spent'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button type="button" onclick="showCustomerModal(<?php echo $customer['id']; ?>, '<?php echo addslashes($customer['first_name'] . ' ' . $customer['last_name']); ?>', '<?php echo $customer['email']; ?>')" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center">No customers found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer Modal -->
    <div id="customerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Customer Details</h3>
                <button type="button" onclick="closeCustomerModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="customerDetails" class="mb-4">
                <!-- Customer details will be loaded here -->
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeCustomerModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Customer search functionality
        document.getElementById('customerSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const table = document.getElementById('customersTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const name = rows[i].getElementsByTagName('td')[1]?.textContent.toLowerCase() || '';
                const email = rows[i].getElementsByTagName('td')[2]?.textContent.toLowerCase() || '';
                
                if (name.includes(searchText) || email.includes(searchText)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
        
        // Customer modal functions
        function showCustomerModal(id, name, email) {
            const modal = document.getElementById('customerModal');
            const detailsContainer = document.getElementById('customerDetails');
            
            // Load customer details via AJAX or just display basic info
            detailsContainer.innerHTML = `
                <div class="space-y-3">
                    <p><strong>ID:</strong> ${id}</p>
                    <p><strong>Name:</strong> ${name}</p>
                    <p><strong>Email:</strong> ${email}</p>
                    <div class="mt-4">
                        <h4 class="font-semibold">Recent Orders</h4>
                        <div class="text-center py-3">
                            <a href="orders.php?customer_id=${id}" class="text-blue-600 hover:text-blue-800">
                                View All Orders
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }
        
        function closeCustomerModal() {
            document.getElementById('customerModal').classList.add('hidden');
        }
    </script>
</body>
</html> 