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

    $conn->close();
}

setupDatabase();
