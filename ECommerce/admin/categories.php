<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if categories table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'categories'");
$categories_table_exists = mysqli_num_rows($table_check) > 0;

if (!$categories_table_exists) {
    // Create categories table
    $create_table = "CREATE TABLE categories (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        parent_id INT(11) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY (parent_id)
    )";
    
    if (mysqli_query($conn, $create_table)) {
        $categories_table_exists = true;
        
        // Add sample categories
        $sample_categories = [
            ['name' => 'Men', 'parent_id' => 'NULL'],
            ['name' => 'Women', 'parent_id' => 'NULL'],
            ['name' => 'Kids', 'parent_id' => 'NULL'],
            ['name' => 'Accessories', 'parent_id' => 'NULL']
        ];
        
        foreach ($sample_categories as $category) {
            $name = mysqli_real_escape_string($conn, $category['name']);
            $parent_id = $category['parent_id'];
            
            mysqli_query($conn, "INSERT INTO categories (name, parent_id) VALUES ('$name', $parent_id)");
        }
        
        $success = "Categories table created successfully with sample categories.";
    } else {
        $error = "Error creating categories table: " . mysqli_error($conn);
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $categories_table_exists) {
    // Add new category
    if (isset($_POST['add_category'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
        
        if (!empty($name)) {
            $sql = "INSERT INTO categories (name, parent_id) VALUES ('$name', $parent_id)";
            if (mysqli_query($conn, $sql)) {
                $success = "Category added successfully!";
            } else {
                $error = "Error adding category: " . mysqli_error($conn);
            }
        } else {
            $error = "Category name is required";
        }
    }
    
    // Update category
    if (isset($_POST['update_category'])) {
        $id = (int)$_POST['id'];
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
        
        if (!empty($name)) {
            $sql = "UPDATE categories SET name = '$name', parent_id = $parent_id WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $success = "Category updated successfully!";
            } else {
                $error = "Error updating category: " . mysqli_error($conn);
            }
        } else {
            $error = "Category name is required";
        }
    }
    
    // Delete category
    if (isset($_POST['delete_category'])) {
        $id = (int)$_POST['id'];
        
        // First, update child categories to have no parent
        $sql = "UPDATE categories SET parent_id = NULL WHERE parent_id = $id";
        mysqli_query($conn, $sql);
        
        // Then delete the category
        $sql = "DELETE FROM categories WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $success = "Category deleted successfully!";
        } else {
            $error = "Error deleting category: " . mysqli_error($conn);
        }
    }
}

// Get all categories
$categories = [];
$parent_categories = [];

if ($categories_table_exists) {
    $sql = "SELECT c.*, p.name as parent_name 
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            ORDER BY c.parent_id IS NULL DESC, p.name, c.name";
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    // Get parent categories for dropdown
    $sql = "SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name";
    $parent_result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($parent_result)) {
        $parent_categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Admin Panel</title>
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
                <a href="categories.php" class="flex items-center bg-blue-800">
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
                <a href="settings.php" class="flex items-center">
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
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold">Category Management</h1>
                <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus mr-2"></i>Add Category
                </button>
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
            
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $category['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (empty($category['parent_id'])): ?>
                                            <span class="font-medium"><?php echo $category['name']; ?></span>
                                        <?php else: ?>
                                            <?php echo $category['name']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $category['parent_name'] ?: '-'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3" 
                                                onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>', <?php echo $category['parent_id'] ?: 'null'; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900" 
                                                onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No categories found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category (Optional)</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (Top Level Category)</option>
                                <?php foreach ($parent_categories as $parent): ?>
                                    <option value="<?php echo $parent['id']; ?>"><?php echo $parent['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_parent_id" class="form-label">Parent Category (Optional)</label>
                            <select class="form-select" id="edit_parent_id" name="parent_id">
                                <option value="">None (Top Level Category)</option>
                                <?php foreach ($parent_categories as $parent): ?>
                                    <option value="<?php echo $parent['id']; ?>"><?php echo $parent['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="hidden" id="delete_id" name="id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete the category "<span id="delete_name"></span>"?</p>
                        <p class="text-danger">Note: Child categories will be converted to top-level categories.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_category" class="btn btn-danger">Delete Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(id, name, parentId) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            
            const parentSelect = document.getElementById('edit_parent_id');
            if (parentId) {
                parentSelect.value = parentId;
            } else {
                parentSelect.value = '';
            }
            
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
        
        function deleteCategory(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
        }
    </script>
</body>
</html> 