<?php
// Database setup script for Artizo E-commerce

echo "<h1>Artizo E-commerce Setup</h1>";
echo "<p>This script will set up the database and create necessary files and directories.</p>";

// Create directories if they don't exist
$directories = [
    'assets/images/products',
    'assets/images/categories',
    'assets/css',
    'assets/js',
    'admin',
    'config',
    'includes',
    'database'
];

echo "<h2>Creating Directories</h2>";
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✓ Created directory: $dir</p>";
        } else {
            echo "<p>✗ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p>✓ Directory already exists: $dir</p>";
    }
}

// Run the database setup script
echo "<h2>Setting Up Database</h2>";
include 'database/setup.php';

echo "<h2>Setup Complete</h2>";
echo "<p>The Artizo E-commerce system has been set up successfully.</p>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/index.php'>Go to Admin Panel</a></p>";
?> 