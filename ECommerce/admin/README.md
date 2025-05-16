# Artizo Admin Panel

This is the administration panel for the Artizo e-commerce website. It provides tools to manage products, categories, orders, customers, and more.

## Setup Instructions

### 1. Create the Admin Table

First, you need to create the admin table in your database. You can do this in two ways:

#### Option 1: Using the Setup Script

1. Navigate to: http://localhost/ECommerce/admin/setup_admin_table.php
2. This will automatically create the admin table and insert a default admin user

#### Option 2: Using SQL Directly

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select your "artizo_db" database
3. Go to the "SQL" tab
4. Copy and paste the contents of the `admin_setup.sql` file or run these queries:

```sql
-- Create admins table
CREATE TABLE IF NOT EXISTS admins (
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
);

-- Insert default admin user with plain text password
INSERT INTO admins (username, password, first_name, last_name, email) 
VALUES ('admin', 'admin123', 'Admin', 'User', 'admin@artizo.com')
ON DUPLICATE KEY UPDATE password = 'admin123';
```

### 2. Access the Admin Panel

1. Go to: http://localhost/ECommerce/admin/login.php
2. Login with the default credentials:
   - Username: admin
   - Password: admin123

## Admin Panel Features

The admin panel includes the following features:

- **Dashboard**: Overview of store statistics
- **Products**: Manage products (add, edit, delete)
- **Categories**: Manage product categories
- **Orders**: View and manage customer orders
- **Customers**: View and manage customer accounts
- **Reviews**: Moderate product reviews
- **Coupons**: Create and manage discount coupons
- **Settings**: Configure store settings

## Security Notes

1. The default setup uses plain text passwords for simplicity. In a production environment, you should:
   - Use password hashing (PHP's password_hash() function)
   - Enable HTTPS
   - Implement proper input validation
   - Consider adding two-factor authentication

2. Change the default admin password after first login

## Troubleshooting

If you encounter issues:

1. **Database Connection**: Ensure your database credentials in `config/db.php` are correct
2. **Permissions**: Make sure your web server has proper permissions to access the files
3. **Session Issues**: Check PHP session configuration if login doesn't work

For further assistance, contact the development team. 