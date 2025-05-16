<?php
include '../config/db.php';

// Create admins table
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(20) DEFAULT 'admin',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Admins table created successfully<br>";
} else {
    echo "Error creating admins table: " . mysqli_error($conn) . "<br>";
}

// Check if admin user exists
$check_query = "SELECT * FROM admins WHERE username = 'admin'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    // Insert default admin user with plain text password
    $insert_query = "INSERT INTO admins (username, password, first_name, last_name, email) 
                    VALUES ('admin', 'admin123', 'Admin', 'User', 'admin@artizo.com')";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "Default admin user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating default admin user: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

echo "<br>Setup completed!";
echo "<br><a href='login.php'>Go to Admin Login</a>";
?> 