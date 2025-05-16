<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if settings table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
$settings_table_exists = mysqli_num_rows($table_check) > 0;

if (!$settings_table_exists) {
    // Create settings table
    $create_table = "CREATE TABLE settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(255) NOT NULL,
        setting_value TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (setting_key)
    )";
    
    if (mysqli_query($conn, $create_table)) {
        $settings_table_exists = true;
    } else {
        $error = "Error creating settings table: " . mysqli_error($conn);
    }
}

// Initialize settings if they don't exist
if ($settings_table_exists) {
    $settings_check = "SELECT COUNT(*) as count FROM settings";
    $result = mysqli_query($conn, $settings_check);
    $row = mysqli_fetch_assoc($result);

    if ($row['count'] == 0) {
        // Insert default settings
        $default_settings = [
            'store_name' => 'Artizo',
            'store_email' => 'info@artizo.com.bd',
            'store_phone' => '+880 1700 000000',
            'store_address' => 'A R Tanni Fashion, Shewrapara, Mirpur, Dhaka-1216, Bangladesh',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
            'tax_enabled' => '0',
            'tax_rate' => '0',
            'shipping_inside_dhaka' => '80',
            'shipping_outside_dhaka' => '150',
            'shipping_express' => '300',
            'working_hours' => 'Saturday - Thursday: 10AM - 8PM, Friday: 3PM - 8PM',
            'maintenance_mode' => '0'
        ];
        
        foreach ($default_settings as $key => $value) {
            mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        // Store information
        $store_name = mysqli_real_escape_string($conn, $_POST['store_name']);
        $store_email = mysqli_real_escape_string($conn, $_POST['store_email']);
        $store_phone = mysqli_real_escape_string($conn, $_POST['store_phone']);
        $store_address = mysqli_real_escape_string($conn, $_POST['store_address']);
        
        // Currency settings
        $currency = mysqli_real_escape_string($conn, $_POST['currency']);
        $currency_symbol = mysqli_real_escape_string($conn, $_POST['currency_symbol']);
        
        // Tax settings
        $tax_enabled = isset($_POST['tax_enabled']) ? '1' : '0';
        $tax_rate = mysqli_real_escape_string($conn, $_POST['tax_rate']);
        
        // Shipping settings
        $shipping_inside_dhaka = mysqli_real_escape_string($conn, $_POST['shipping_inside_dhaka']);
        $shipping_outside_dhaka = mysqli_real_escape_string($conn, $_POST['shipping_outside_dhaka']);
        $shipping_express = mysqli_real_escape_string($conn, $_POST['shipping_express']);
        
        // Other settings
        $working_hours = mysqli_real_escape_string($conn, $_POST['working_hours']);
        $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
        
        // Update settings in database
        $settings = [
            'store_name' => $store_name,
            'store_email' => $store_email,
            'store_phone' => $store_phone,
            'store_address' => $store_address,
            'currency' => $currency,
            'currency_symbol' => $currency_symbol,
            'tax_enabled' => $tax_enabled,
            'tax_rate' => $tax_rate,
            'shipping_inside_dhaka' => $shipping_inside_dhaka,
            'shipping_outside_dhaka' => $shipping_outside_dhaka,
            'shipping_express' => $shipping_express,
            'working_hours' => $working_hours,
            'maintenance_mode' => $maintenance_mode
        ];
        
        foreach ($settings as $key => $value) {
            mysqli_query($conn, "UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
        }
        
        $success = "Settings updated successfully!";
    }
}

// Get current settings
$settings = [];
if ($settings_table_exists) {
    $settings_query = "SELECT * FROM settings";
    $settings_result = mysqli_query($conn, $settings_query);
    
    while ($row = mysqli_fetch_assoc($settings_result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
// Use default settings if table doesn't exist
else {
    $settings = [
        'store_name' => 'Artizo',
        'store_email' => 'info@artizo.com.bd',
        'store_phone' => '+880 1700 000000',
        'store_address' => 'A R Tanni Fashion, Shewrapara, Mirpur, Dhaka-1216, Bangladesh',
        'currency' => 'BDT',
        'currency_symbol' => '৳',
        'tax_enabled' => '0',
        'tax_rate' => '0',
        'shipping_inside_dhaka' => '80',
        'shipping_outside_dhaka' => '150',
        'shipping_express' => '300',
        'working_hours' => 'Saturday - Thursday: 10AM - 8PM, Friday: 3PM - 8PM',
        'maintenance_mode' => '0'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .admin-content {
            margin-left: 16rem;
            padding: 1.5rem;
        }
        .admin-sidebar {
            background-color: #343a40;
            color: white;
            padding-top: 1rem;
            overflow-y: auto;
        }
        .admin-sidebar a {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1.25rem;
            display: block;
            text-decoration: none;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="admin-sidebar w-64 fixed h-full">
            <div class="p-4 text-center">
                <h1 class="text-2xl font-bold mb-1">Artizo</h1>
                <p class="text-xs opacity-70">Admin Panel</p>
            </div>
            <nav class="mt-4">
                <a href="index.php" class="flex items-center">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span>Dashboard</span>
                </a>
                <a href="products.php" class="flex items-center">
                    <i class="fas fa-box w-6"></i>
                    <span>Products</span>
                </a>
                <a href="categories.php" class="flex items-center">
                    <i class="fas fa-folder w-6"></i>
                    <span>Categories</span>
                </a>
                <a href="orders.php" class="flex items-center">
                    <i class="fas fa-shopping-cart w-6"></i>
                    <span>Orders</span>
                </a>
                <a href="customers.php" class="flex items-center">
                    <i class="fas fa-users w-6"></i>
                    <span>Customers</span>
                </a>
                <a href="coupons.php" class="flex items-center">
                    <i class="fas fa-tag w-6"></i>
                    <span>Coupons</span>
                </a>
                <a href="reviews.php" class="flex items-center">
                    <i class="fas fa-star w-6"></i>
                    <span>Reviews</span>
                </a>
                <a href="settings.php" class="flex items-center bg-blue-800">
                    <i class="fas fa-cog w-6"></i>
                    <span>Settings</span>
                </a>
                <div class="mt-8 border-t border-gray-700 pt-4">
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt mr-2"></i> View Site</a>
                    <a href="logout.php" class="flex items-center text-red-300 hover:text-red-100">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-content flex-1">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Store Settings</h1>
                <p class="text-gray-600">Configure your store settings</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <!-- Store Information -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 border-b pb-2">Store Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-3">
                                <label for="store_name" class="form-label">Store Name</label>
                                <input type="text" class="form-control" id="store_name" name="store_name" value="<?php echo $settings['store_name'] ?? 'Artizo'; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="store_email" class="form-label">Store Email</label>
                                <input type="email" class="form-control" id="store_email" name="store_email" value="<?php echo $settings['store_email'] ?? 'info@artizo.com.bd'; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="store_phone" class="form-label">Store Phone</label>
                                <input type="text" class="form-control" id="store_phone" name="store_phone" value="<?php echo $settings['store_phone'] ?? '+880 1700 000000'; ?>" required>
                            </div>
                            
                            <div class="mb-3 md:col-span-2">
                                <label for="store_address" class="form-label">Store Address</label>
                                <textarea class="form-control" id="store_address" name="store_address" rows="2" required><?php echo $settings['store_address'] ?? 'House #10, Road #12, Banani, Dhaka 1213, Bangladesh'; ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Currency Settings -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 border-b pb-2">Currency Settings</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="BDT" <?php echo ($settings['currency'] ?? '') === 'BDT' ? 'selected' : ''; ?>>BDT - Bangladeshi Taka</option>
                                    <option value="USD" <?php echo ($settings['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                    <option value="EUR" <?php echo ($settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                    <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?php echo $settings['currency_symbol'] ?? '৳'; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tax Settings -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 border-b pb-2">Tax Settings</h2>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="tax_enabled" name="tax_enabled" <?php echo ($settings['tax_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="tax_enabled">Enable Tax Calculation</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" step="0.01" min="0" value="<?php echo $settings['tax_rate'] ?? '0'; ?>">
                        </div>
                    </div>
                    
                    <!-- Shipping Settings -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 border-b pb-2">Shipping Settings</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="mb-3">
                                <label for="shipping_inside_dhaka" class="form-label">Inside Dhaka City (<?php echo $settings['currency_symbol'] ?? '৳'; ?>)</label>
                                <input type="number" class="form-control" id="shipping_inside_dhaka" name="shipping_inside_dhaka" value="<?php echo $settings['shipping_inside_dhaka'] ?? '80'; ?>" min="0" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="shipping_outside_dhaka" class="form-label">Outside Dhaka District (<?php echo $settings['currency_symbol'] ?? '৳'; ?>)</label>
                                <input type="number" class="form-control" id="shipping_outside_dhaka" name="shipping_outside_dhaka" value="<?php echo $settings['shipping_outside_dhaka'] ?? '150'; ?>" min="0" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="shipping_express" class="form-label">Express Nationwide (<?php echo $settings['currency_symbol'] ?? '৳'; ?>)</label>
                                <input type="number" class="form-control" id="shipping_express" name="shipping_express" value="<?php echo $settings['shipping_express'] ?? '300'; ?>" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other Settings -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 border-b pb-2">Other Settings</h2>
                        
                        <div class="mb-3">
                            <label for="working_hours" class="form-label">Working Hours</label>
                            <input type="text" class="form-control" id="working_hours" name="working_hours" value="<?php echo $settings['working_hours'] ?? 'Saturday - Thursday: 10AM - 8PM, Friday: 3PM - 8PM'; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_mode">Enable Maintenance Mode</label>
                            </div>
                            <small class="text-muted">When enabled, only admins can access the site.</small>
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 