<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = mysqli_real_escape_string($conn, strtolower(str_replace(' ', '-', $name)));
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $quantity = intval($_POST['quantity']);
    $category_id = intval($_POST['category_id']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validate required fields
    if (empty($name) || empty($description) || $price <= 0 || $quantity < 0 || empty($category_id)) {
        $error = "Please fill in all required fields";
    } else {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Check if extension is allowed
            if (in_array(strtolower($file_ext), $allowed)) {
                // Generate unique filename
                $new_filename = uniqid() . '.' . $file_ext;
                $upload_dir = '../assets/images/products/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                    $image = $new_filename;
                } else {
                    $error = "Failed to upload image";
                }
            } else {
                $error = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            }
        }
        
        if (empty($error)) {
            // Check if slug already exists
            $check_slug_query = "SELECT id FROM products WHERE slug = '$slug'";
            $check_slug_result = mysqli_query($conn, $check_slug_query);
            
            if (mysqli_num_rows($check_slug_result) > 0) {
                // Append a number to make the slug unique
                $slug .= '-' . uniqid();
            }
            
            // Insert product into database
            $sql = "INSERT INTO products (name, slug, description, price, sale_price, quantity, image, category_id, featured, status) 
                    VALUES ('$name', '$slug', '$description', $price, " . ($sale_price ? $sale_price : "NULL") . ", $quantity, '$image', $category_id, $featured, '$status')";
            
            if (mysqli_query($conn, $sql)) {
                $product_id = mysqli_insert_id($conn);
                
                // Handle additional images
                if (isset($_FILES['additional_images'])) {
                    $file_count = count($_FILES['additional_images']['name']);
                    
                    for ($i = 0; $i < $file_count; $i++) {
                        if ($_FILES['additional_images']['error'][$i] == 0) {
                            $filename = $_FILES['additional_images']['name'][$i];
                            $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
                            
                            if (in_array(strtolower($file_ext), $allowed)) {
                                $new_filename = uniqid() . '.' . $file_ext;
                                
                                if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$i], $upload_dir . $new_filename)) {
                                    $insert_image_sql = "INSERT INTO product_images (product_id, image, sort_order) 
                                                        VALUES ($product_id, '$new_filename', $i)";
                                    mysqli_query($conn, $insert_image_sql);
                                }
                            }
                        }
                    }
                }
                
                // Handle product attributes
                if (isset($_POST['attribute_names']) && is_array($_POST['attribute_names'])) {
                    $attr_count = count($_POST['attribute_names']);
                    
                    for ($i = 0; $i < $attr_count; $i++) {
                        if (!empty($_POST['attribute_names'][$i]) && !empty($_POST['attribute_values'][$i])) {
                            $attr_name = mysqli_real_escape_string($conn, $_POST['attribute_names'][$i]);
                            $attr_value = mysqli_real_escape_string($conn, $_POST['attribute_values'][$i]);
                            $attr_price = !empty($_POST['attribute_prices'][$i]) ? floatval($_POST['attribute_prices'][$i]) : 0;
                            $attr_quantity = !empty($_POST['attribute_quantities'][$i]) ? intval($_POST['attribute_quantities'][$i]) : 0;
                            
                            $insert_attr_sql = "INSERT INTO product_attributes (product_id, attribute_name, attribute_value, price_adjustment, quantity) 
                                                VALUES ($product_id, '$attr_name', '$attr_value', $attr_price, $attr_quantity)";
                            mysqli_query($conn, $insert_attr_sql);
                        }
                    }
                }
                
                $message = "Product added successfully";
                // Redirect to products page
                header("Location: products.php");
                exit;
            } else {
                $error = "Error adding product: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Artizo Admin</title>
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
                <a href="products.php" class="active"><i class="fas fa-box mr-2"></i> Products</a>
                <a href="categories.php"><i class="fas fa-tags mr-2"></i> Categories</a>
                <a href="orders.php"><i class="fas fa-shopping-cart mr-2"></i> Orders</a>
                <a href="customers.php"><i class="fas fa-users mr-2"></i> Customers</a>
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
                <h1 class="text-2xl font-bold">Add New Product</h1>
                <a href="products.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                            
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-600">*</span></label>
                                <input type="text" id="name" name="name" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-600">*</span></label>
                                <textarea id="description" name="description" rows="5" class="w-full border rounded-md px-4 py-2" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-600">*</span></label>
                                <select id="category_id" name="category_id" class="w-full border rounded-md px-4 py-2" required>
                                    <option value="">Select Category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price <span class="text-red-600">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2">$</span>
                                        <input type="number" id="price" name="price" step="0.01" min="0" class="w-full border rounded-md pl-8 pr-4 py-2" required>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2">$</span>
                                        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" class="w-full border rounded-md pl-8 pr-4 py-2">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-600">*</span></label>
                                <input type="number" id="quantity" name="quantity" min="0" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" value="active" class="form-radio" checked>
                                        <span class="ml-2">Active</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" value="inactive" class="form-radio">
                                        <span class="ml-2">Inactive</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="featured" value="1" class="form-checkbox">
                                    <span class="ml-2">Featured Product</span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <h2 class="text-lg font-semibold mb-4">Images</h2>
                            
                            <div class="mb-4">
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Main Image</label>
                                <input type="file" id="image" name="image" class="w-full border rounded-md px-4 py-2" accept="image/*">
                                <p class="text-xs text-gray-500 mt-1">Recommended size: 800x800px. Max file size: 2MB</p>
                            </div>
                            
                            <div class="mb-4">
                                <label for="additional_images" class="block text-sm font-medium text-gray-700 mb-1">Additional Images</label>
                                <input type="file" id="additional_images" name="additional_images[]" class="w-full border rounded-md px-4 py-2" accept="image/*" multiple>
                                <p class="text-xs text-gray-500 mt-1">You can select multiple images</p>
                            </div>
                            
                            <h2 class="text-lg font-semibold mb-4 mt-8">Product Attributes</h2>
                            
                            <div id="attributes-container">
                                <div class="attribute-row grid grid-cols-4 gap-2 mb-3">
                                    <div>
                                        <input type="text" name="attribute_names[]" placeholder="Name (e.g. Color)" class="w-full border rounded-md px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <input type="text" name="attribute_values[]" placeholder="Value (e.g. Red)" class="w-full border rounded-md px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <input type="number" name="attribute_prices[]" placeholder="Price Adj." step="0.01" class="w-full border rounded-md px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <input type="number" name="attribute_quantities[]" placeholder="Qty" min="0" class="w-full border rounded-md px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" id="add-attribute" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm mt-2">
                                <i class="fas fa-plus mr-1"></i> Add Attribute
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-8 border-t pt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add attribute row
        document.getElementById('add-attribute').addEventListener('click', function() {
            const container = document.getElementById('attributes-container');
            const newRow = document.createElement('div');
            newRow.className = 'attribute-row grid grid-cols-4 gap-2 mb-3';
            newRow.innerHTML = `
                <div>
                    <input type="text" name="attribute_names[]" placeholder="Name (e.g. Color)" class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <input type="text" name="attribute_values[]" placeholder="Value (e.g. Red)" class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <input type="number" name="attribute_prices[]" placeholder="Price Adj." step="0.01" class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div class="flex">
                    <input type="number" name="attribute_quantities[]" placeholder="Qty" min="0" class="w-full border rounded-md px-3 py-2 text-sm">
                    <button type="button" class="remove-attribute ml-1 text-red-500 px-2" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            
            // Add remove event listener
            newRow.querySelector('.remove-attribute').addEventListener('click', function() {
                container.removeChild(newRow);
            });
        });
    </script>
</body>
</html> 