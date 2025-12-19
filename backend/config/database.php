<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'raymond');
define('DB_PASS', '111901');
define('DB_NAME', 'cafe_rencontre');

function getDBConnection()
{
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        die(json_encode([
            'success' => false,
            'message' => 'Database connection error'
        ]));
    }
}

function setupDatabase()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->select_db(DB_NAME);

    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            email VARCHAR(100),
            role ENUM('admin', 'staff') DEFAULT 'staff',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            category ENUM('brewed', 'milk', 'soda') NOT NULL,
            description TEXT,
            base_price DECIMAL(10,2) NOT NULL,
            image_url VARCHAR(255),
            is_available BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS ingredients (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            unit VARCHAR(20) NOT NULL,
            bottles_count INT DEFAULT 0,
            available_quantity DECIMAL(10,2) NOT NULL,
            bottle_capacity DECIMAL(10,2) DEFAULT 2000,
            min_threshold DECIMAL(10,2) DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_number VARCHAR(20) UNIQUE NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_email VARCHAR(100),
            customer_phone VARCHAR(20),
            room_number VARCHAR(20),
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'approved', 'completed', 'rejected') DEFAULT 'pending',
            payment_method ENUM('cash', 'card', 'online'),
            payment_proof VARCHAR(255),
            payment_status ENUM('pending', 'paid') DEFAULT 'pending',
            is_student BOOLEAN DEFAULT FALSE,
            discount_amount DECIMAL(10,2) DEFAULT 0,
            final_amount DECIMAL(10,2) NOT NULL,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(100) NOT NULL,
            size VARCHAR(20),
            temperature VARCHAR(20),
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            customizations TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )",

        "CREATE TABLE IF NOT EXISTS product_ingredients (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            ingredient_id INT NOT NULL,
            quantity_needed DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
            UNIQUE KEY unique_product_ingredient (product_id, ingredient_id)
        )",

        "CREATE TABLE IF NOT EXISTS product_order_stats (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            order_date DATE NOT NULL,
            order_count INT DEFAULT 0,
            total_quantity INT DEFAULT 0,
            total_revenue DECIMAL(10,2) DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_product_date (product_id, order_date)
        )",

        "CREATE TABLE IF NOT EXISTS inventory_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            ingredient_id INT NOT NULL,
            order_id INT,
            change_type ENUM('deduction', 'restock', 'adjustment') NOT NULL,
            quantity_change DECIMAL(10,2) NOT NULL,
            previous_quantity DECIMAL(10,2) NOT NULL,
            new_quantity DECIMAL(10,2) NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
        )"
    ];

    foreach ($tables as $tableSQL) {
        if (!$conn->query($tableSQL)) {
            error_log("Table creation failed: " . $conn->error);
        }
    }

    $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO users (username, password_hash, full_name, role) 
                  VALUES ('admin', '$hashedPassword', 'Administrator', 'admin')");

    // Seed default products if table is empty
    seedProducts($conn);

    $conn->close();
}

function seedProducts($conn)
{
    // Check if products already exist
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        return; // Products already seeded
    }

    $products = [
        // Brewed Based
        ['Spanish Latte', 'brewed', 'Rich espresso with sweetened condensed milk for a creamy, indulgent taste', 49.00, 'https://data.thefeedfeed.com/static/2022/12/19/167148578963a0d95dd1a02.jpg'],
        ['Machiato', 'brewed', 'Bold espresso marked with a dollop of foamed milk', 49.00, 'https://www.thespruceeats.com/thmb/HXaU0FwlEoZ6d5MoPVzGCXKx41k=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/85153452-56a176765f9b58b7d0bf84dd.jpg'],
        ['Hazelnut Latte', 'brewed', 'Smooth espresso with hazelnut syrup and steamed milk', 49.00, 'https://lifestyleofafoodie.com/wp-content/uploads/2024/04/Spanish-Latte-Recipe-5.jpg'],
        ['Americano', 'brewed', 'Classic espresso diluted with hot water for a smooth, bold flavor', 49.00, 'https://loveincrediblerecipes.com/wp-content/uploads/2023/12/nespresso-americano-1200x1200-1.jpg'],
        ['Caramel Macchiato', 'brewed', 'Vanilla-flavored drink marked with espresso and caramel drizzle', 49.00, 'https://athome.starbucks.com/sites/default/files/styles/recipe_banner_xlarge/public/2024-05/CaramelMacchiato_RecipeHeader_848x539_%402x.jpg.webp?itok=jO6d0gba'],
        ['Vanilla Latte', 'brewed', 'Espresso with vanilla syrup and creamy steamed milk', 49.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcReXQigakVzOdxqfoOQsuvQ6PCm3icW--IOYg&s'],
        // Milk Series
        ['Strawberry Milk', 'milk', 'Fresh strawberry blended with creamy milk', 69.00, 'https://www.cookerru.com/wp-content/uploads/2021/01/korean-strawberry-milk-feature-main.jpg'],
        ['Strawberry Hazelnut', 'milk', 'Strawberry milk with a hint of hazelnut flavor', 69.00, 'https://theveggieyaya.com/wp-content/uploads/2024/06/iced-strawberry-latte-square-image.jpg'],
        ['Choco Milk', 'milk', 'Rich chocolate blended with fresh milk', 69.00, 'https://www.thespruceeats.com/thmb/93UHJ043ztF5RyiPsyFJt_OVCs8=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/chocolate-milk-recipe-2355494-hero-02-80cffdb175904e03a8fd6bb7d6ffc0dd.jpg'],
        // Fruit Soda
        ['Strawberry', 'soda', 'Refreshing strawberry-flavored sparkling soda', 49.00, 'https://mocktail.net/wp-content/uploads/2022/06/Homemade-Strawberry-Soda_11ig.jpg'],
        ['Blueberry', 'soda', 'Sweet blueberry soda with a fizzy kick', 49.00, 'https://img.freepik.com/premium-photo/blueberry-soda-plastic-cup_504796-499.jpg'],
        ['Green Apple', 'soda', 'Crisp green apple flavored sparkling drink', 49.00, 'https://www.shutterstock.com/image-photo/plastic-glass-refreshing-iced-green-260nw-1850934520.jpg'],
        ['Blue Lemonade', 'soda', 'Tangy blue lemonade with sparkling water', 49.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRrabIPKt5940sVoStVoFKt7iV9QBlSWVaZLA&s'],
        ['Lychee', 'soda', 'Sweet lychee flavored refreshing soda', 49.00, 'https://img.freepik.com/free-photo/lychee-juice-lychee-fruit_1150-13685.jpg?semt=ais_hybrid&w=740&q=80'],
        ['Four Season', 'soda', 'Mixed tropical fruits in a fizzy drink', 49.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVEX8dwT83HVWXLS5WuX7YY0fZZwuwZVZwOA&s']
    ];

    $stmt = $conn->prepare("INSERT INTO products (name, category, description, base_price, image_url, is_available) VALUES (?, ?, ?, ?, ?, 1)");

    foreach ($products as $p) {
        $stmt->bind_param("sssds", $p[0], $p[1], $p[2], $p[3], $p[4]);
        $stmt->execute();
    }

    $stmt->close();
}

setupDatabase();
