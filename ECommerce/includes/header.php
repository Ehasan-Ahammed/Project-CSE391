<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artizo - Fashion Clothing Store</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <!-- Top Bar -->
        <div class="bg-black text-white text-sm py-2">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <div>                    <span><i class="fas fa-phone me-1"></i> +880 1700 000000</span>                    <span class="ms-4"><i class="fas fa-envelope me-1"></i> info@artizo.com.bd</span>                </div>
                <div>
                    <a href="#" class="text-white hover:text-gray-300 me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white hover:text-gray-300 me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white hover:text-gray-300"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="py-4 border-b">
            <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
                <!-- Logo -->
                <a href="index.php" class="text-3xl font-bold mb-4 md:mb-0">Artizo</a>
                
                <!-- Search Bar -->
                <div class="w-full md:w-1/3 mb-4 md:mb-0">
                    <form action="search.php" method="get" class="relative">
                        <input type="text" name="query" placeholder="Search products..." class="w-full py-2 px-4 border rounded-md">
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-search text-gray-500"></i>
                        </button>
                    </form>
                </div>
                
                <!-- User Actions -->
                <div class="flex items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown me-4">
                            <a href="#" class="flex items-center text-gray-700 hover:text-black" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-2"></i>
                                <span>My Account</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="account.php">Profile</a></li>
                                <li><a class="dropdown-item" href="#orders">Orders</a></li>
                                <li><a class="dropdown-item" href="wishlist.php">Wishlist</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="flex items-center text-gray-700 hover:text-black me-4">
                            <i class="fas fa-user me-2"></i>
                            <span>Login / Register</span>
                        </a>
                        <div class="dropdown me-4">
                            <a href="#" class="flex items-center text-gray-700 hover:text-black" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-lock me-2"></i>
                                <span>Admin</span>
                                <i class="fas fa-chevron-down ms-1 text-xs"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="admin/login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
                                <li><a class="dropdown-item" href="login.php"><i class="fas fa-user me-2"></i>User Login</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <a href="wishlist.php" class="flex items-center text-gray-700 hover:text-black me-4">
                        <i class="fas fa-heart me-2"></i>
                        <span>Wishlist</span>
                    </a>
                    
                    <a href="cart.php" class="flex items-center text-gray-700 hover:text-black relative">
                        <i class="fas fa-shopping-cart me-2"></i>
                        <span>Cart</span>
                        <?php
                        $cartCount = 0;
                        if (isset($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                $cartCount += $item['quantity'];
                            }
                        }
                        if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="bg-white py-4">
            <div class="container mx-auto px-4">
                <ul class="flex flex-wrap justify-center space-x-1 md:space-x-8">
                    <li><a href="index.php" class="px-3 py-2 hover:text-blue-600 font-medium">Home</a></li>
                    <li class="relative group">
                        <a href="shop.php?category=men" class="px-3 py-2 hover:text-blue-600 font-medium flex items-center">
                            Men <i class="fas fa-chevron-down ms-1 text-xs"></i>
                        </a>
                        <div class="absolute left-0 mt-2 w-48 bg-white shadow-lg rounded-md p-2 hidden group-hover:block z-10">
                            <a href="shop.php?category=men&subcategory=shirts" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Shirts</a>
                            <a href="shop.php?category=men&subcategory=pants" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Pants</a>
                            <a href="shop.php?category=men&subcategory=jackets" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Jackets</a>
                            <a href="shop.php?category=men&subcategory=accessories" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Accessories</a>
                        </div>
                    </li>
                    <li class="relative group">
                        <a href="shop.php?category=women" class="px-3 py-2 hover:text-blue-600 font-medium flex items-center">
                            Women <i class="fas fa-chevron-down ms-1 text-xs"></i>
                        </a>
                        <div class="absolute left-0 mt-2 w-48 bg-white shadow-lg rounded-md p-2 hidden group-hover:block z-10">
                            <a href="shop.php?category=women&subcategory=dresses" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Dresses</a>
                            <a href="shop.php?category=women&subcategory=tops" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Tops</a>
                            <a href="shop.php?category=women&subcategory=pants" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Pants</a>
                            <a href="shop.php?category=women&subcategory=accessories" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Accessories</a>
                        </div>
                    </li>
                    <li class="relative group">
                        <a href="shop.php?category=kids" class="px-3 py-2 hover:text-blue-600 font-medium flex items-center">
                            Kids <i class="fas fa-chevron-down ms-1 text-xs"></i>
                        </a>
                        <div class="absolute left-0 mt-2 w-48 bg-white shadow-lg rounded-md p-2 hidden group-hover:block z-10">
                            <a href="shop.php?category=kids&subcategory=boys" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Boys</a>
                            <a href="shop.php?category=kids&subcategory=girls" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Girls</a>
                            <a href="shop.php?category=kids&subcategory=infants" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Infants</a>
                        </div>
                    </li>
                    <li><a href="shop.php?category=accessories" class="px-3 py-2 hover:text-blue-600 font-medium">Accessories</a></li>
                    <li><a href="sale.php" class="px-3 py-2 text-red-600 hover:text-red-800 font-medium">Sale</a></li>
                    <li><a href="contact.php" class="px-3 py-2 hover:text-blue-600 font-medium">Contact</a></li>
                </ul>
            </div>
        </nav>
        </header> 