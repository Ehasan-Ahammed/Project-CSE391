<?php
session_start();
include 'config/db.php';

// Get product slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: shop.php');
    exit;
}

// Get product details
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.slug = '$slug' AND p.status = 'active'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: shop.php');
    exit;
}

$product = mysqli_fetch_assoc($result);

// Get product images
$images_query = "SELECT * FROM product_images WHERE product_id = {$product['id']} ORDER BY sort_order";
$images_result = mysqli_query($conn, $images_query);

// Get product attributes
$sizes_query = "SELECT * FROM product_attributes WHERE product_id = {$product['id']} AND attribute_name = 'size' ORDER BY attribute_value";
$sizes_result = mysqli_query($conn, $sizes_query);

$colors_query = "SELECT * FROM product_attributes WHERE product_id = {$product['id']} AND attribute_name = 'color' ORDER BY attribute_value";
$colors_result = mysqli_query($conn, $colors_query);

// Get related products
$related_query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.category_id = {$product['category_id']} 
                 AND p.id != {$product['id']} 
                 AND p.status = 'active' 
                 ORDER BY p.created_at DESC 
                 LIMIT 4";
$related_result = mysqli_query($conn, $related_query);

// Get product reviews
$reviews_query = "SELECT r.*, u.first_name, u.last_name 
                 FROM reviews r 
                 LEFT JOIN users u ON r.user_id = u.id 
                 WHERE r.product_id = {$product['id']} 
                 AND r.status = 'approved' 
                 ORDER BY r.created_at DESC";
$reviews_result = mysqli_query($conn, $reviews_query);

// Calculate average rating
$avg_rating = 0;
$review_count = mysqli_num_rows($reviews_result);
if ($review_count > 0) {
    $rating_sum = 0;
    mysqli_data_seek($reviews_result, 0);
    while ($review = mysqli_fetch_assoc($reviews_result)) {
        $rating_sum += $review['rating'];
    }
    $avg_rating = round($rating_sum / $review_count, 1);
    mysqli_data_seek($reviews_result, 0);
}

// Handle add to cart action
if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $color = isset($_POST['color']) ? $_POST['color'] : '';
    
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Generate a unique cart item key
    $cart_item_key = $product['id'];
    if (!empty($size)) {
        $cart_item_key .= '_' . $size;
    }
    if (!empty($color)) {
        $cart_item_key .= '_' . $color;
    }
    
    // Check if product already in cart
    if (isset($_SESSION['cart'][$cart_item_key])) {
        // Update quantity
        $_SESSION['cart'][$cart_item_key]['quantity'] += $quantity;
    } else {
        // Add new item
        $_SESSION['cart'][$cart_item_key] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['sale_price'] ? $product['sale_price'] : $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'size' => $size,
            'color' => $color
        ];
    }
    
    // Redirect to cart page
    header('Location: cart.php');
    exit;
}

// Include header after all header redirects are done
include 'includes/header.php';
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex mb-8 text-sm">
            <ol class="flex items-center space-x-2">
                <li><a href="index.php" class="text-gray-500 hover:text-black">Home</a></li>
                <li><span class="text-gray-500 mx-2">/</span></li>
                <li><a href="shop.php" class="text-gray-500 hover:text-black">Shop</a></li>
                <li><span class="text-gray-500 mx-2">/</span></li>
                <li><a href="shop.php?category=<?php echo $product['category_slug']; ?>" class="text-gray-500 hover:text-black"><?php echo $product['category_name']; ?></a></li>
                <li><span class="text-gray-500 mx-2">/</span></li>
                <li><span class="text-black font-medium"><?php echo $product['name']; ?></span></li>
            </ol>
        </nav>

        <!-- Product Details -->
        <div class="flex flex-col lg:flex-row">
            <!-- Product Images -->
            <div class="w-full lg:w-1/2 mb-8 lg:mb-0 lg:pr-8">
                <div class="product-gallery">
                    <div class="mb-4">
                        <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-auto rounded-lg shadow-md product-main-image">
                    </div>
                    
                    <?php if (mysqli_num_rows($images_result) > 0): ?>
                        <div class="grid grid-cols-4 gap-4">
                            <div class="product-gallery-thumb active" data-image="assets/images/products/<?php echo $product['image']; ?>">
                                <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-24 object-cover rounded-md cursor-pointer">
                            </div>
                            
                            <?php while ($image = mysqli_fetch_assoc($images_result)): ?>
                                <div class="product-gallery-thumb" data-image="assets/images/products/<?php echo $image['image']; ?>">
                                    <img src="assets/images/products/<?php echo $image['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-24 object-cover rounded-md cursor-pointer">
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="w-full lg:w-1/2">
                <h1 class="text-3xl font-bold mb-2"><?php echo $product['name']; ?></h1>
                
                <div class="flex items-center mb-4">
                    <div class="flex items-center rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo ($i <= $avg_rating) ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="ml-2 text-gray-600"><?php echo $review_count; ?> reviews</span>
                </div>
                
                <div class="mb-6">
                    <?php if ($product['sale_price']): ?>
                        <span class="text-3xl font-bold">$<?php echo $product['sale_price']; ?></span>
                        <span class="text-lg text-gray-500 line-through ml-2">$<?php echo $product['price']; ?></span>
                        <?php
                        $discount_percentage = round(($product['price'] - $product['sale_price']) / $product['price'] * 100);
                        ?>
                        <span class="ml-2 bg-red-500 text-white px-2 py-1 rounded text-xs">SAVE <?php echo $discount_percentage; ?>%</span>
                    <?php else: ?>
                        <span class="text-3xl font-bold">$<?php echo $product['price']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-700"><?php echo $product['description']; ?></p>
                </div>
                
                <form action="<?php echo $_SERVER['PHP_SELF'] . '?slug=' . $slug; ?>" method="post">
                    <?php if (mysqli_num_rows($sizes_result) > 0): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Size</h3>
                            <div class="flex flex-wrap">
                                <?php while ($size = mysqli_fetch_assoc($sizes_result)): ?>
                                    <div class="size-option mr-2 mb-2" data-size="<?php echo $size['attribute_value']; ?>">
                                        <?php echo $size['attribute_value']; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <input type="hidden" name="size" id="selected-size">
                        </div>
                    <?php endif; ?>
                    
                    <?php if (mysqli_num_rows($colors_result) > 0): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Color</h3>
                            <div class="flex flex-wrap">
                                <?php while ($color = mysqli_fetch_assoc($colors_result)): ?>
                                    <div class="color-option mr-3 mb-2" data-color="<?php echo $color['attribute_value']; ?>" style="background-color: <?php echo $color['attribute_value']; ?>"></div>
                                <?php endwhile; ?>
                            </div>
                            <input type="hidden" name="color" id="selected-color">
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Quantity</h3>
                        <div class="quantity-input w-32">
                            <button type="button" class="quantity-btn" onclick="decrementQuantity()">-</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="quantity-field">
                            <button type="button" class="quantity-btn" onclick="incrementQuantity()">+</button>
                        </div>
                        <p class="text-sm text-gray-600 mt-2"><?php echo $product['quantity']; ?> items available</p>
                    </div>
                    
                    <div class="flex flex-wrap mb-8">
                        <button type="submit" name="add_to_cart" class="bg-black text-white px-8 py-3 rounded-md hover:bg-gray-800 transition mr-4 mb-2 sm:mb-0">Add to Cart</button>
                        <button type="button" onclick="addToWishlist(<?php echo $product['id']; ?>)" class="border border-gray-300 px-8 py-3 rounded-md hover:bg-gray-100 transition flex items-center">
                            <i class="far fa-heart mr-2"></i> Add to Wishlist
                        </button>
                    </div>
                </form>
                
                <div class="border-t pt-6">
                    <div class="mb-4">
                        <span class="font-semibold">SKU:</span> <?php echo $product['id']; ?>
                    </div>
                    <div class="mb-4">
                        <span class="font-semibold">Category:</span> <a href="shop.php?category=<?php echo $product['category_slug']; ?>" class="text-blue-600 hover:underline"><?php echo $product['category_name']; ?></a>
                    </div>
                    <div class="flex space-x-4">
                        <span class="font-semibold">Share:</span>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="mt-16">
            <ul class="flex flex-wrap border-b">
                <li class="mr-2"><a href="#description" class="inline-block px-4 py-2 font-medium border-b-2 border-black">Description</a></li>
                <li class="mr-2"><a href="#reviews" class="inline-block px-4 py-2 text-gray-600 hover:text-black">Reviews (<?php echo $review_count; ?>)</a></li>
                <li><a href="#shipping" class="inline-block px-4 py-2 text-gray-600 hover:text-black">Shipping & Returns</a></li>
            </ul>
            
            <div id="description" class="py-6">
                <div class="prose max-w-none">
                    <p><?php echo $product['description']; ?></p>
                </div>
            </div>
            
            <div id="reviews" class="hidden py-6">
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">Customer Reviews</h3>
                    
                    <?php if ($review_count > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                            <div class="border-b pb-4 mb-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center">
                                            <div class="flex rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo ($i <= $review['rating']) ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="ml-2 font-medium"><?php echo $review['first_name'] . ' ' . $review['last_name']; ?></span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <p><?php echo $review['comment']; ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div>
                        <h3 class="text-xl font-semibold mb-4">Write a Review</h3>
                        <form action="submit-review.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="mb-4">
                                <label class="block mb-2">Your Rating</label>
                                <div class="flex rating-input">
                                    <input type="radio" name="rating" value="5" id="rating-5" class="hidden">
                                    <label for="rating-5" class="text-2xl cursor-pointer text-gray-300 hover:text-yellow-400"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="4" id="rating-4" class="hidden">
                                    <label for="rating-4" class="text-2xl cursor-pointer text-gray-300 hover:text-yellow-400"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="3" id="rating-3" class="hidden">
                                    <label for="rating-3" class="text-2xl cursor-pointer text-gray-300 hover:text-yellow-400"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="2" id="rating-2" class="hidden">
                                    <label for="rating-2" class="text-2xl cursor-pointer text-gray-300 hover:text-yellow-400"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="1" id="rating-1" class="hidden">
                                    <label for="rating-1" class="text-2xl cursor-pointer text-gray-300 hover:text-yellow-400"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="review-comment" class="block mb-2">Your Review</label>
                                <textarea id="review-comment" name="comment" rows="4" class="w-full border rounded-md px-4 py-2"></textarea>
                            </div>
                            
                            <button type="submit" class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 transition">Submit Review</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>Please <a href="login.php" class="text-blue-600 hover:underline">login</a> to write a review.</p>
                <?php endif; ?>
            </div>
            
            <div id="shipping" class="hidden py-6">
                <div class="prose max-w-none">
                    <h3>Shipping Information</h3>
                    <p>We offer standard and express shipping options to most destinations. Shipping costs are calculated based on the weight of your order and the delivery address.</p>
                    
                    <h4 class="mt-4">Delivery Times</h4>
                    <ul>
                        <li>Standard Shipping: 3-7 business days</li>
                        <li>Express Shipping: 1-3 business days</li>
                    </ul>
                    
                    <h3 class="mt-6">Returns Policy</h3>
                    <p>If you're not completely satisfied with your purchase, you can return it within 30 days for a full refund or exchange.</p>
                    
                    <h4 class="mt-4">Return Conditions</h4>
                    <ul>
                        <li>Items must be unused and in original packaging</li>
                        <li>Include the original receipt or proof of purchase</li>
                        <li>Return shipping costs are the responsibility of the customer unless the item is defective</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (mysqli_num_rows($related_result) > 0): ?>
            <div class="mt-16">
                <h2 class="text-2xl font-bold mb-6">You May Also Like</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                    <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                        <div class="product-card bg-white rounded-lg shadow-md overflow-hidden">
                            <a href="product.php?slug=<?php echo $related['slug']; ?>">
                                <img src="assets/images/products/<?php echo $related['image']; ?>" alt="<?php echo $related['name']; ?>" class="w-full h-64 object-cover">
                                <?php if ($related['sale_price']): ?>
                                    <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs">SALE</span>
                                <?php endif; ?>
                            </a>
                            <div class="p-4">
                                <a href="shop.php?category=<?php echo $product['category_slug']; ?>" class="text-sm text-gray-500 hover:text-black"><?php echo $related['category_name']; ?></a>
                                <h3 class="text-lg font-semibold mt-1">
                                    <a href="product.php?slug=<?php echo $related['slug']; ?>" class="hover:text-blue-600"><?php echo $related['name']; ?></a>
                                </h3>
                                <div class="flex justify-between items-center mt-2">
                                    <div>
                                        <?php if ($related['sale_price']): ?>
                                            <span class="text-xl font-bold">$<?php echo $related['sale_price']; ?></span>
                                            <span class="text-sm text-gray-500 line-through ml-2">$<?php echo $related['price']; ?></span>
                                        <?php else: ?>
                                            <span class="text-xl font-bold">$<?php echo $related['price']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Tab navigation
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('ul.flex.flex-wrap.border-b li a');
    const tabContents = document.querySelectorAll('#description, #reviews, #shipping');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            tabs.forEach(t => {
                t.classList.remove('border-b-2', 'border-black', 'font-medium');
                t.classList.add('text-gray-600', 'hover:text-black');
            });
            
            // Add active class to clicked tab
            this.classList.add('border-b-2', 'border-black', 'font-medium');
            this.classList.remove('text-gray-600', 'hover:text-black');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show the selected tab content
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).classList.remove('hidden');
        });
    });
    
    // Size selection
    const sizeOptions = document.querySelectorAll('.size-option');
    const sizeInput = document.getElementById('selected-size');
    
    if (sizeOptions.length > 0) {
        sizeOptions.forEach(option => {
            option.addEventListener('click', function() {
                sizeOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                sizeInput.value = this.dataset.size;
            });
        });
    }
    
    // Color selection
    const colorOptions = document.querySelectorAll('.color-option');
    const colorInput = document.getElementById('selected-color');
    
    if (colorOptions.length > 0) {
        colorOptions.forEach(option => {
            option.addEventListener('click', function() {
                colorOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                colorInput.value = this.dataset.color;
            });
        });
    }
    
    // Product gallery
    const galleryThumbs = document.querySelectorAll('.product-gallery-thumb');
    const mainImage = document.querySelector('.product-main-image');
    
    if (galleryThumbs.length > 0 && mainImage) {
        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                galleryThumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                mainImage.src = this.dataset.image;
            });
        });
    }
    
    // Rating input
    const ratingInputs = document.querySelectorAll('.rating-input input');
    const ratingLabels = document.querySelectorAll('.rating-input label');
    
    if (ratingInputs.length > 0) {
        ratingInputs.forEach((input, index) => {
            input.addEventListener('change', function() {
                const rating = this.value;
                
                ratingLabels.forEach((label, i) => {
                    if (i < rating) {
                        label.classList.remove('text-gray-300');
                        label.classList.add('text-yellow-400');
                    } else {
                        label.classList.add('text-gray-300');
                        label.classList.remove('text-yellow-400');
                    }
                });
            });
        });
    }
});

// Quantity increment/decrement
function incrementQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    let value = parseInt(input.value);
    
    if (value < max) {
        input.value = value + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value);
    
    if (value > 1) {
        input.value = value - 1;
    }
}

// Add to wishlist
function addToWishlist(productId) {
    // This would typically be an AJAX call to add the product to the wishlist
    // For now, we'll just redirect to the wishlist page
    window.location.href = 'wishlist.php?add=' + productId;
}
</script>

<?php include 'includes/footer.php'; ?> 