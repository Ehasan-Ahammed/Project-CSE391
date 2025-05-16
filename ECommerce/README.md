# Artizo - E-commerce Clothing Store

Artizo is a full-featured e-commerce platform for a clothing store built with PHP, MySQL, HTML, CSS, JavaScript, Tailwind CSS, and Bootstrap.

## Features

### Customer Features
- **Home Page**: Featured products, new arrivals, promotional banners
- **Product Listing**: Category-wise browsing with filters for size, color, price
- **Product Detail Page**: Product images, description, size guide, reviews
- **Shopping Cart**: Add/remove items, update quantities
- **Checkout Process**: User registration/login, shipping details, payment options
- **User Account**: Order history, profile management, wishlist
- **Search Functionality**: Search by product name, category, or brand
- **Contact & Support**: Contact form, FAQs, return policy

### Admin Features
- **Dashboard**: Summary of orders, revenue, products, and customers
- **Product Management**: Add, edit, delete products and manage inventory
- **Order Management**: View and update order status
- **Customer Management**: View customer details and orders
- **Inventory Management**: Track stock levels with low stock alerts
- **Discount Management**: Create and manage coupon codes
- **Reports**: Sales reports and analytics

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **CSS Frameworks**: Tailwind CSS, Bootstrap
- **Icons**: Font Awesome
- **Charts**: Chart.js

## Installation

### Requirements
- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx)

### Setup Instructions

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/artizo-ecommerce.git
   ```

2. Create a MySQL database named `artizo_db`.

3. Configure the database connection:
   - Open `config/db.php`
   - Update the database credentials if needed:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "artizo_db";
     ```

4. Run the setup script:
   - Navigate to `http://yourdomain.com/setup.php`
   - This will create all necessary tables and sample data

5. Access the website:
   - Frontend: `http://yourdomain.com/`
   - Admin Panel: `http://yourdomain.com/admin/`
     - Default admin credentials:
       - Email: admin@artizo.com
       - Password: admin123

## Directory Structure

```
artizo-ecommerce/
├── admin/                  # Admin panel files
├── assets/                 # Static assets
│   ├── css/                # CSS files
│   ├── js/                 # JavaScript files
│   └── images/             # Images
│       ├── products/       # Product images
│       └── categories/     # Category images
├── config/                 # Configuration files
├── database/               # Database setup and migration files
├── includes/               # Reusable PHP components
└── index.php               # Main entry point
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Tailwind CSS](https://tailwindcss.com/)
- [Bootstrap](https://getbootstrap.com/)
- [Font Awesome](https://fontawesome.com/)
- [Chart.js](https://www.chartjs.org/) 