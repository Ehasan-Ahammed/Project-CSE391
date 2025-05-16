<?php
session_start();
include 'config/db.php';
include 'includes/header.php';
?>

<main class="py-16">
    <div class="container mx-auto px-4 text-center">
        <div class="max-w-lg mx-auto">
            <h1 class="text-9xl font-bold text-gray-900 mb-4">404</h1>
            <h2 class="text-2xl font-semibold mb-6">Page Not Found</h2>
            <p class="text-gray-600 mb-8">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
            
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="index.php" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-800 transition">Go to Homepage</a>
                <a href="shop.php" class="border border-black px-6 py-3 rounded-md hover:bg-gray-100 transition">Browse Products</a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 