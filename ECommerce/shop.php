<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : '';
$subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : '';

// Get sorting option
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get price range
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000;

// Get size and color filters
$size = isset($_GET['size']) ? $_GET['size'] : '';
$color = isset($_GET['color']) ? $_GET['color'] : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$products_per_page = 12;
$offset = ($page - 1) * $products_per_page;

// Build the query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE p.status = 'active'";

// Add category filter
if (!empty($category)) {
    // Get the category ID
    $cat_query = "SELECT id FROM categories WHERE slug = '$category'";
    $cat_result = mysqli_query($conn, $cat_query);
    
    if (mysqli_num_rows($cat_result) > 0) {
        $cat_row = mysqli_fetch_assoc($cat_result);
        $category_id = $cat_row['id'];
        
        if (!empty($subcategory)) {
            // If subcategory is provided, get products from that subcategory
            $subcat_query = "SELECT id FROM categories WHERE slug = '$subcategory' AND parent_id = $category_id";
            $subcat_result = mysqli_query($conn, $subcat_query);
            
            if (mysqli_num_rows($subcat_result) > 0) {
                $subcat_row = mysqli_fetch_assoc($subcat_result);
                $subcategory_id = $subcat_row['id'];
                $query .= " AND p.category_id = $subcategory_id";
            }
        } else {
            // If only category is provided, get products from that category and its subcategories
            $query .= " AND (p.category_id = $category_id OR p.category_id IN (SELECT id FROM categories WHERE parent_id = $category_id))";
        }
    }
}

// Add price range filter
$query .= " AND p.price BETWEEN $min_price AND $max_price";

// Add size and color filters (assuming these are stored in product_attributes table)
if (!empty($size) || !empty($color)) {
    $query .= " AND p.id IN (SELECT product_id FROM product_attributes WHERE ";
    
    if (!empty($size)) {
        $query .= "(attribute_name = 'size' AND attribute_value = '$size')";
    }
    
    if (!empty($size) && !empty($color)) {
        $query .= " OR ";
    }
    
    if (!empty($color)) {
        $query .= "(attribute_name = 'color' AND attribute_value = '$color')";
    }
    
    $query .= ")";
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'bestselling':
        // This would ideally be based on sales data, but for simplicity:
        $query .= " ORDER BY p.featured DESC, p.id ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

// Count total products for pagination
$count_query = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*) as total", $query);
$count_query = preg_replace('/ORDER BY.*$/', '', $count_query);
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $products_per_page);

// Add limit for pagination
$query .= " LIMIT $offset, $products_per_page";

// Execute the query
$result = mysqli_query($conn, $query);

// Get all categories for the filter sidebar
$categories_query = "SELECT c.*, COUNT(p.id) as product_count 
                    FROM categories c 
                    LEFT JOIN products p ON c.id = p.category_id 
                    WHERE c.parent_id IS NULL 
                    GROUP BY c.id";
$categories_result = mysqli_query($conn, $categories_query);

// Get subcategories if a category is selected
$subcategories_result = null;
if (!empty($category) && isset($category_id)) {
    $subcategories_query = "SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           WHERE c.parent_id = $category_id 
                           GROUP BY c.id";
    $subcategories_result = mysqli_query($conn, $subcategories_query);
}

// Get available sizes and colors
$sizes_query = "SELECT DISTINCT attribute_value FROM product_attributes WHERE attribute_name = 'size' ORDER BY attribute_value";
$sizes_result = mysqli_query($conn, $sizes_query);

$colors_query = "SELECT DISTINCT attribute_value FROM product_attributes WHERE attribute_name = 'color' ORDER BY attribute_value";
$colors_result = mysqli_query($conn, $colors_query);

// Get category name for title
$page_title = "Shop All Products";
if (!empty($category)) {
    $cat_name_query = "SELECT name FROM categories WHERE slug = '$category'";
    $cat_name_result = mysqli_query($conn, $cat_name_query);
    if (mysqli_num_rows($cat_name_result) > 0) {
        $cat_name_row = mysqli_fetch_assoc($cat_name_result);
        $page_title = $cat_name_row['name'];
        
        if (!empty($subcategory)) {
            $subcat_name_query = "SELECT name FROM categories WHERE slug = '$subcategory'";
            $subcat_name_result = mysqli_query($conn, $subcat_name_query);
            if (mysqli_num_rows($subcat_name_result) > 0) {
                $subcat_name_row = mysqli_fetch_assoc($subcat_name_result);
                $page_title .= " - " . $subcat_name_row['name'];
            }
        }
    }
}
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex mb-8 text-sm">
            <ol class="flex items-center space-x-2">
                <li><a href="index.php" class="text-gray-500 hover:text-black">Home</a></li>
                <li><span class="text-gray-500 mx-2">/</span></li>
                <?php if (!empty($category)): ?>
                    <li><a href="shop.php" class="text-gray-500 hover:text-black">Shop</a></li>
                    <li><span class="text-gray-500 mx-2">/</span></li>
                    <li><a href="shop.php?category=<?php echo $category; ?>" class="<?php echo empty($subcategory) ? 'text-black font-medium' : 'text-gray-500 hover:text-black'; ?>"><?php echo ucfirst($category); ?></a></li>
                    <?php if (!empty($subcategory)): ?>
                        <li><span class="text-gray-500 mx-2">/</span></li>
                        <li><span class="text-black font-medium"><?php echo ucfirst(str_replace('-', ' ', $subcategory)); ?></span></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><span class="text-black font-medium">Shop</span></li>
                <?php endif; ?>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row">
            <!-- Filter Sidebar -->
            <div class="w-full md:w-1/4 md:pr-8 mb-8 md:mb-0">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Categories</h3>
                        <ul class="space-y-2">
                            <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                <li>
                                    <a href="shop.php?category=<?php echo $cat['slug']; ?>" class="flex justify-between items-center <?php echo ($category == $cat['slug']) ? 'font-medium text-black' : 'text-gray-600 hover:text-black'; ?>">
                                        <span><?php echo $cat['name']; ?></span>
                                        <span class="text-sm text-gray-500">(<?php echo $cat['product_count']; ?>)</span>
                                    </a>
                                    
                                    <?php if ($category == $cat['slug'] && $subcategories_result && mysqli_num_rows($subcategories_result) > 0): ?>
                                        <ul class="ml-4 mt-2 space-y-1">
                                            <?php mysqli_data_seek($subcategories_result, 0); ?>
                                            <?php while ($subcat = mysqli_fetch_assoc($subcategories_result)): ?>
                                                <li>
                                                    <a href="shop.php?category=<?php echo $category; ?>&subcategory=<?php echo $subcat['slug']; ?>" class="flex justify-between items-center <?php echo ($subcategory == $subcat['slug']) ? 'font-medium text-black' : 'text-gray-600 hover:text-black'; ?>">
                                                        <span><?php echo $subcat['name']; ?></span>
                                                        <span class="text-sm text-gray-500">(<?php echo $subcat['product_count']; ?>)</span>
                                                    </a>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <form action="shop.php" method="get" id="filter-form">
                        <?php if (!empty($category)): ?>
                            <input type="hidden" name="category" value="<?php echo $category; ?>">
                        <?php endif; ?>
                        
                        <?php if (!empty($subcategory)): ?>
                            <input type="hidden" name="subcategory" value="<?php echo $subcategory; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Price Range</h3>
                            <div class="flex items-center">
                                <input type="number" name="min_price" value="<?php echo $min_price; ?>" min="0" max="999" class="w-1/3 border rounded-md px-3 py-2 text-sm" placeholder="Min">
                                <span class="mx-2">-</span>
                                <input type="number" name="max_price" value="<?php echo $max_price; ?>" min="1" max="1000" class="w-1/3 border rounded-md px-3 py-2 text-sm" placeholder="Max">
                                <button type="submit" class="ml-2 bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm hover:bg-gray-300">Go</button>
                            </div>
                        </div>

                        <?php if ($sizes_result && mysqli_num_rows($sizes_result) > 0): ?>
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-4">Size</h3>
                                <div class="grid grid-cols-4 gap-2">
                                    <?php while ($size_row = mysqli_fetch_assoc($sizes_result)): ?>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="size" value="<?php echo $size_row['attribute_value']; ?>" <?php echo ($size == $size_row['attribute_value']) ? 'checked' : ''; ?> class="form-radio">
                                            <span class="ml-2"><?php echo $size_row['attribute_value']; ?></span>
                                        </label>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($colors_result && mysqli_num_rows($colors_result) > 0): ?>
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-4">Color</h3>
                                <div class="grid grid-cols-4 gap-2">
                                    <?php while ($color_row = mysqli_fetch_assoc($colors_result)): ?>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="color" value="<?php echo $color_row['attribute_value']; ?>" <?php echo ($color == $color_row['attribute_value']) ? 'checked' : ''; ?> class="form-radio">
                                            <span class="ml-2"><?php echo ucfirst($color_row['attribute_value']); ?></span>
                                        </label>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between">
                            <button type="submit" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition">Apply Filters</button>
                            <a href="shop.php<?php echo !empty($category) ? '?category=' . $category : ''; ?><?php echo !empty($subcategory) ? '&subcategory=' . $subcategory : ''; ?>" class="text-gray-600 hover:text-black px-4 py-2">Clear Filters</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="w-full md:w-3/4">
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <h1 class="text-2xl font-bold mb-4 sm:mb-0"><?php echo $page_title; ?></h1>
                    
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 mr-2">Sort by:</span>
                        <select name="sort" id="sort-select" class="border rounded-md px-3 py-2 text-sm" onchange="updateSort(this.value)">
                            <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="bestselling" <?php echo ($sort == 'bestselling') ? 'selected' : ''; ?>>Best Selling</option>
                        </select>
                    </div>
                </div>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <?php while ($product = mysqli_fetch_assoc($result)): ?>
                            <div class="product-card bg-white rounded-lg shadow-md overflow-hidden">
                                <a href="product.php?slug=<?php echo $product['slug']; ?>">
                                    <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-64 object-cover">
                                    <?php if ($product['sale_price']): ?>
                                        <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs">SALE</span>
                                    <?php endif; ?>
                                </a>
                                <div class="p-4">
                                    <a href="shop.php?category=<?php echo $category; ?>" class="text-sm text-gray-500 hover:text-black"><?php echo $product['category_name']; ?></a>
                                    <h3 class="text-lg font-semibold mt-1">
                                        <a href="product.php?slug=<?php echo $product['slug']; ?>" class="hover:text-blue-600"><?php echo $product['name']; ?></a>
                                    </h3>
                                    <div class="flex justify-between items-center mt-2">
                                        <div>
                                            <?php if ($product['sale_price']): ?>
                                                <span class="text-xl font-bold">$<?php echo $product['sale_price']; ?></span>
                                                <span class="text-sm text-gray-500 line-through ml-2">$<?php echo $product['price']; ?></span>
                                            <?php else: ?>
                                                <span class="text-xl font-bold">$<?php echo $product['price']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="product.php?slug=<?php echo $product['slug']; ?>" class="text-blue-600 hover:underline">View</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="mt-8 flex justify-center">
                            <nav class="inline-flex rounded-md shadow">
                                <?php if ($page > 1): ?>
                                    <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="px-3 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 rounded-l-md">Previous</a>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="px-3 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">1</a>';
                                    if ($start_page > 2) {
                                        echo '<span class="px-3 py-2 border border-gray-300 bg-white text-gray-700">...</span>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    if ($i == $page) {
                                        echo '<span class="px-3 py-2 border border-gray-300 bg-black text-white">' . $i . '</span>';
                                    } else {
                                        echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $i])) . '" class="px-3 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">' . $i . '</a>';
                                    }
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<span class="px-3 py-2 border border-gray-300 bg-white text-gray-700">...</span>';
                                    }
                                    echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '" class="px-3 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">' . $total_pages . '</a>';
                                }
                                ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="px-3 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 rounded-r-md">Next</a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="bg-white p-8 rounded-lg shadow-sm text-center">
                        <h2 class="text-xl font-semibold mb-4">No products found</h2>
                        <p class="text-gray-600 mb-6">Try adjusting your filters or browse our other categories.</p>
                        <a href="shop.php" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-800 transition">View All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
function updateSort(value) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', value);
    window.location.href = currentUrl.toString();
}
</script>

<?php include 'includes/footer.php'; ?> 