<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Home page content
?>

<main>
    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="container mx-auto px-4 py-16">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to Artizo</h1>
                    <p class="text-lg mb-6">Discover the latest fashion trends for all seasons</p>
                    <a href="shop.php" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-800 transition">Shop Now</a>
                </div>
                <div class="md:w-1/2">
                    <img src="assets\images\home.png" alt="Artizo Fashion" class="rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Featured Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php
                // Fetch featured products from database
                $featuredQuery = "SELECT * FROM products WHERE featured = 1 LIMIT 8";
                $featuredResult = mysqli_query($conn, $featuredQuery);
                
                if (mysqli_num_rows($featuredResult) > 0) {
                    while ($product = mysqli_fetch_assoc($featuredResult)) {
                        ?>
                        <div class="product-card bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-64 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold"><?php echo $product['name']; ?></h3>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xl font-bold">$<?php echo $product['price']; ?></span>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:underline">View Details</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='text-center col-span-full'>No featured products available.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="new-arrivals py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">New Arrivals</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php
                // Fetch new arrivals from database
                $newArrivalsQuery = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
                $newArrivalsResult = mysqli_query($conn, $newArrivalsQuery);
                
                if (mysqli_num_rows($newArrivalsResult) > 0) {
                    while ($product = mysqli_fetch_assoc($newArrivalsResult)) {
                        ?>
                        <div class="product-card bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="relative">
                                <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-64 object-cover">
                                <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs">NEW</span>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold"><?php echo $product['name']; ?></h3>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xl font-bold">$<?php echo $product['price']; ?></span>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:underline">View Details</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='text-center col-span-full'>No new arrivals available.</p>";
                }
                ?>
            </div>
            <div class="text-center mt-8">
                <a href="shop.php" class="inline-block border border-black text-black px-6 py-3 rounded-md hover:bg-black hover:text-white transition">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Promotional Banner -->
    <section class="promo-banner bg-gray-900 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h2 class="text-3xl font-bold mb-4">Summer Sale</h2>
                    <p class="text-lg mb-6">Get up to 50% off on selected summer collection</p>
                    <a href="shop.php?category=summer" class="bg-white text-black px-6 py-3 rounded-md hover:bg-gray-200 transition">Shop the Sale</a>
                </div>
                <div class="md:w-1/3">
                    <img src="assets\images\summer.png" alt="Summer Sale" class="rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Shop by Category</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <a href="shop.php?category=men" class="category-card relative rounded-lg overflow-hidden h-64">
                    <img src="assets/images/categories/men.jpg" alt="Men's Collection" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <h3 class="text-white text-2xl font-bold">Men</h3>
                    </div>
                </a>
                <a href="shop.php?category=women" class="category-card relative rounded-lg overflow-hidden h-64">
                    <img src="assets/images/categories/women.jpg" alt="Women's Collection" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <h3 class="text-white text-2xl font-bold">Women</h3>
                    </div>
                </a>
                <a href="shop.php?category=kids" class="category-card relative rounded-lg overflow-hidden h-64">
                    <img src="assets/images/categories/kids.jpg" alt="Kids Collection" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <h3 class="text-white text-2xl font-bold">Kids</h3>
                    </div>
                </a>
                <a href="shop.php?category=accessories" class="category-card relative rounded-lg overflow-hidden h-64">
                    <img src="assets/images/categories/accessories.jpg" alt="Accessories" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <h3 class="text-white text-2xl font-bold">Accessories</h3>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">Subscribe to Our Newsletter</h2>
                <p class="text-lg mb-6">Get updates on new arrivals and special offers</p>
                <form action="newsletter-subscribe.php" method="post" class="flex flex-col sm:flex-row gap-4">
                    <input type="email" name="email" placeholder="Your email address" required class="flex-grow px-4 py-3 rounded-md border">
                    <button type="submit" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-800 transition">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> 