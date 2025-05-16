<?php
// Database setup script for Artizo E-commerce

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS artizo_db";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "artizo_db");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT(11) DEFAULT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Categories table created successfully<br>";
} else {
    echo "Error creating categories table: " . mysqli_error($conn) . "<br>";
}

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    quantity INT(11) NOT NULL DEFAULT 0,
    image VARCHAR(255),
    category_id INT(11),
    featured TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Products table created successfully<br>";
} else {
    echo "Error creating products table: " . mysqli_error($conn) . "<br>";
}

// Create product_images table
$sql = "CREATE TABLE IF NOT EXISTS product_images (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Product images table created successfully<br>";
} else {
    echo "Error creating product images table: " . mysqli_error($conn) . "<br>";
}

// Create product_attributes table
$sql = "CREATE TABLE IF NOT EXISTS product_attributes (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    attribute_name VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(50) NOT NULL,
    price_adjustment DECIMAL(10,2) DEFAULT 0,
    quantity INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Product attributes table created successfully<br>";
} else {
    echo "Error creating product attributes table: " . mysqli_error($conn) . "<br>";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(50) NOT NULL,
    shipping_state VARCHAR(50) NOT NULL,
    shipping_postal_code VARCHAR(20) NOT NULL,
    shipping_country VARCHAR(50) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Orders table created successfully<br>";
} else {
    echo "Error creating orders table: " . mysqli_error($conn) . "<br>";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    attributes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Order items table created successfully<br>";
} else {
    echo "Error creating order items table: " . mysqli_error($conn) . "<br>";
}

// Create reviews table
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    rating INT(1) NOT NULL,
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Reviews table created successfully<br>";
} else {
    echo "Error creating reviews table: " . mysqli_error($conn) . "<br>";
}

// Create wishlist table
$sql = "CREATE TABLE IF NOT EXISTS wishlist (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY user_product (user_id, product_id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Wishlist table created successfully<br>";
} else {
    echo "Error creating wishlist table: " . mysqli_error($conn) . "<br>";
}

// Create coupons table
$sql = "CREATE TABLE IF NOT EXISTS coupons (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percentage', 'fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(10,2) DEFAULT 0,
    max_discount_value DECIMAL(10,2),
    start_date DATE,
    end_date DATE,
    usage_limit INT(11),
    used_count INT(11) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Coupons table created successfully<br>";
} else {
    echo "Error creating coupons table: " . mysqli_error($conn) . "<br>";
}

// Create newsletter_subscribers table
$sql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Newsletter subscribers table created successfully<br>";
} else {
    echo "Error creating newsletter subscribers table: " . mysqli_error($conn) . "<br>";
}

// Insert admin user
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (first_name, last_name, email, password, role) 
        VALUES ('Admin', 'User', 'admin@artizo.com', '$admin_password', 'admin')
        ON DUPLICATE KEY UPDATE first_name = 'Admin', last_name = 'User'";

if (mysqli_query($conn, $sql)) {
    echo "Admin user created successfully<br>";
} else {
    echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
}

// Insert sample categories
$categories = [
    ['Men', 'men', NULL, 'Men\'s clothing collection', 'men.jpg'],
    ['Women', 'women', NULL, 'Women\'s clothing collection', 'women.jpg'],
    ['Kids', 'kids', NULL, 'Kids clothing collection', 'kids.jpg'],
    ['Accessories', 'accessories', NULL, 'Fashion accessories', 'accessories.jpg']
];

foreach ($categories as $category) {
    $sql = "INSERT INTO categories (name, slug, parent_id, description, image) 
            VALUES ('$category[0]', '$category[1]', " . ($category[2] === NULL ? "NULL" : $category[2]) . ", '$category[3]', '$category[4]')
            ON DUPLICATE KEY UPDATE name = '$category[0]', description = '$category[3]', image = '$category[4]'";
    
    if (mysqli_query($conn, $sql)) {
        echo "Category '$category[0]' created successfully<br>";
    } else {
        echo "Error creating category '$category[0]': " . mysqli_error($conn) . "<br>";
    }
}

// Get category IDs
$men_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE slug = 'men'"))['id'];
$women_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE slug = 'women'"))['id'];
$kids_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE slug = 'kids'"))['id'];
$accessories_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE slug = 'accessories'"))['id'];

// Insert subcategories
$subcategories = [
    ['T-Shirts', 't-shirts', $men_id, 'Men\'s t-shirts', 'men-tshirts.jpg'],
    ['Shirts', 'shirts', $men_id, 'Men\'s shirts', 'men-shirts.jpg'],
    ['Pants', 'pants', $men_id, 'Men\'s pants', 'men-pants.jpg'],
    ['Jackets', 'jackets', $men_id, 'Men\'s jackets', 'men-jackets.jpg'],
    
    ['Dresses', 'dresses', $women_id, 'Women\'s dresses', 'women-dresses.jpg'],
    ['Tops', 'tops', $women_id, 'Women\'s tops', 'women-tops.jpg'],
    ['Pants', 'women-pants', $women_id, 'Women\'s pants', 'women-pants.jpg'],
    ['Skirts', 'skirts', $women_id, 'Women\'s skirts', 'women-skirts.jpg'],
    
    ['Boys', 'boys', $kids_id, 'Boys clothing', 'kids-boys.jpg'],
    ['Girls', 'girls', $kids_id, 'Girls clothing', 'kids-girls.jpg'],
    ['Infants', 'infants', $kids_id, 'Infant clothing', 'kids-infants.jpg']
];

foreach ($subcategories as $category) {
    $sql = "INSERT INTO categories (name, slug, parent_id, description, image) 
            VALUES ('$category[0]', '$category[1]', $category[2], '$category[3]', '$category[4]')
            ON DUPLICATE KEY UPDATE name = '$category[0]', description = '$category[3]', image = '$category[4]'";
    
    if (mysqli_query($conn, $sql)) {
        echo "Subcategory '$category[0]' created successfully<br>";
    } else {
        echo "Error creating subcategory '$category[0]': " . mysqli_error($conn) . "<br>";
    }
}

// Insert sample products
$products = [
    ['Classic White T-Shirt', 'classic-white-tshirt', 'Premium quality cotton t-shirt for everyday wear', 29.99, NULL, 100, 'tshirt-white.jpg', $men_id, 1],
    ['Blue Denim Shirt', 'blue-denim-shirt', 'Stylish denim shirt for casual occasions', 49.99, 39.99, 75, 'shirt-denim.jpg', $men_id, 1],
    ['Black Slim Fit Pants', 'black-slim-fit-pants', 'Comfortable slim fit pants for a modern look', 59.99, NULL, 50, 'pants-black.jpg', $men_id, 0],
    ['Leather Jacket', 'leather-jacket', 'Premium leather jacket for a stylish appearance', 199.99, 179.99, 30, 'jacket-leather.jpg', $men_id, 1],
    
    ['Floral Summer Dress', 'floral-summer-dress', 'Beautiful floral dress for summer days', 79.99, 69.99, 60, 'dress-floral.jpg', $women_id, 1],
    ['White Blouse', 'white-blouse', 'Elegant white blouse for formal and casual occasions', 45.99, NULL, 80, 'blouse-white.jpg', $women_id, 0],
    ['Black Leggings', 'black-leggings', 'Comfortable stretchy leggings for everyday wear', 29.99, NULL, 120, 'leggings-black.jpg', $women_id, 1],
    ['Denim Skirt', 'denim-skirt', 'Classic denim skirt for a casual look', 39.99, NULL, 70, 'skirt-denim.jpg', $women_id, 0],
    
    ['Boys Graphic T-Shirt', 'boys-graphic-tshirt', 'Fun graphic t-shirt for boys', 19.99, NULL, 90, 'boys-tshirt.jpg', $kids_id, 0],
    ['Girls Party Dress', 'girls-party-dress', 'Beautiful party dress for special occasions', 49.99, 39.99, 40, 'girls-dress.jpg', $kids_id, 1],
    ['Baby Romper', 'baby-romper', 'Soft and comfortable romper for infants', 24.99, NULL, 60, 'baby-romper.jpg', $kids_id, 0],
    
    ['Leather Wallet', 'leather-wallet', 'Genuine leather wallet with multiple card slots', 39.99, NULL, 100, 'wallet.jpg', $accessories_id, 1],
    ['Sunglasses', 'sunglasses', 'UV protection sunglasses with stylish design', 59.99, 49.99, 80, 'sunglasses.jpg', $accessories_id, 0],
    ['Silver Necklace', 'silver-necklace', 'Elegant silver necklace for any occasion', 79.99, NULL, 50, 'necklace.jpg', $accessories_id, 1],
    ['Canvas Belt', 'canvas-belt', 'Durable canvas belt with metal buckle', 24.99, NULL, 120, 'belt.jpg', $accessories_id, 0]
];

foreach ($products as $product) {
    $sql = "INSERT INTO products (name, slug, description, price, sale_price, quantity, image, category_id, featured) 
            VALUES ('$product[0]', '$product[1]', '$product[2]', $product[3], " . ($product[4] === NULL ? "NULL" : $product[4]) . ", $product[5], '$product[6]', $product[7], $product[8])
            ON DUPLICATE KEY UPDATE name = '$product[0]', description = '$product[2]', price = $product[3], sale_price = " . ($product[4] === NULL ? "NULL" : $product[4]) . ", quantity = $product[5], image = '$product[6]', category_id = $product[7], featured = $product[8]";
    
    if (mysqli_query($conn, $sql)) {
        echo "Product '$product[0]' created successfully<br>";
    } else {
        echo "Error creating product '$product[0]': " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>Database setup completed successfully!";

// Close connection
mysqli_close($conn);
?> 