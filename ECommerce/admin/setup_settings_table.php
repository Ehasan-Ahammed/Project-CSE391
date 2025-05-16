<?php
include '../config/db.php';

// Check if settings table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if (mysqli_num_rows($table_check) == 0) {
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
        echo "Settings table created successfully.";
    } else {
        echo "Error creating settings table: " . mysqli_error($conn);
    }
} else {
    echo "Settings table already exists.";
}

// Check if categories table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'categories'");
if (mysqli_num_rows($table_check) == 0) {
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
        echo "<br>Categories table created successfully.";
    } else {
        echo "<br>Error creating categories table: " . mysqli_error($conn);
    }
} else {
    echo "<br>Categories table already exists.";
}

// Add initial sample categories if there are none
$count_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
$row = mysqli_fetch_assoc($count_check);

if ($row['count'] == 0) {
    $sample_categories = [
        ['name' => 'Men', 'parent_id' => 'NULL'],
        ['name' => 'Women', 'parent_id' => 'NULL'],
        ['name' => 'Kids', 'parent_id' => 'NULL'],
        ['name' => 'Shirts', 'parent_id' => 1],
        ['name' => 'Pants', 'parent_id' => 1],
        ['name' => 'Jackets', 'parent_id' => 1],
        ['name' => 'Dresses', 'parent_id' => 2],
        ['name' => 'Tops', 'parent_id' => 2],
        ['name' => 'Skirts', 'parent_id' => 2],
        ['name' => 'Boys', 'parent_id' => 3],
        ['name' => 'Girls', 'parent_id' => 3]
    ];
    
    foreach ($sample_categories as $category) {
        $name = mysqli_real_escape_string($conn, $category['name']);
        $parent_id = $category['parent_id'];
        
        $insert = "INSERT INTO categories (name, parent_id) VALUES ('$name', $parent_id)";
        if (mysqli_query($conn, $insert)) {
            echo "<br>Added category: " . $category['name'];
        } else {
            echo "<br>Error adding category " . $category['name'] . ": " . mysqli_error($conn);
        }
    }
}

echo "<br><br>Setup completed successfully.";
echo "<br><a href='../admin/'>Go to Admin Panel</a>";
?> 