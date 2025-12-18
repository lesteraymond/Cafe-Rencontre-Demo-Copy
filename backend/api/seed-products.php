<?php
/**
 * Seed Products Script - Run once to populate the database with initial products
 * Access via: http://localhost/backend/api/seed-products.php
 */
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

$conn = getDBConnection();
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Products data from the original hardcoded list
$products = [
    // Brewed Based
    [
        'name' => 'Spanish Latte',
        'category' => 'brewed',
        'description' => 'Rich espresso with sweetened condensed milk for a creamy, indulgent taste',
        'base_price' => 49.00,
        'image_url' => 'https://data.thefeedfeed.com/static/2022/12/19/167148578963a0d95dd1a02.jpg'
    ],
    [
        'name' => 'Machiato',
        'category' => 'brewed',
        'description' => 'Bold espresso marked with a dollop of foamed milk',
        'base_price' => 49.00,
        'image_url' => 'https://www.thespruceeats.com/thmb/HXaU0FwlEoZ6d5MoPVzGCXKx41k=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/85153452-56a176765f9b58b7d0bf84dd.jpg'
    ],
    [
        'name' => 'Hazelnut Latte',
        'category' => 'brewed',
        'description' => 'Smooth espresso with hazelnut syrup and steamed milk',
        'base_price' => 49.00,
        'image_url' => 'https://lifestyleofafoodie.com/wp-content/uploads/2024/04/Spanish-Latte-Recipe-5.jpg'
    ],
    [
        'name' => 'Americano',
        'category' => 'brewed',
        'description' => 'Classic espresso diluted with hot water for a smooth, bold flavor',
        'base_price' => 49.00,
        'image_url' => 'https://loveincrediblerecipes.com/wp-content/uploads/2023/12/nespresso-americano-1200x1200-1.jpg'
    ],
    [
        'name' => 'Caramel Macchiato',
        'category' => 'brewed',
        'description' => 'Vanilla-flavored drink marked with espresso and caramel drizzle',
        'base_price' => 49.00,
        'image_url' => 'https://athome.starbucks.com/sites/default/files/styles/recipe_banner_xlarge/public/2024-05/CaramelMacchiato_RecipeHeader_848x539_%402x.jpg.webp?itok=jO6d0gba'
    ],
    [
        'name' => 'Vanilla Latte',
        'category' => 'brewed',
        'description' => 'Espresso with vanilla syrup and creamy steamed milk',
        'base_price' => 49.00,
        'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcReXQigakVzOdxqfoOQsuvQ6PCm3icW--IOYg&s'
    ],
    // Milk Series
    [
        'name' => 'Strawberry Milk',
        'category' => 'milk',
        'description' => 'Fresh strawberry blended with creamy milk',
        'base_price' => 69.00,
        'image_url' => 'https://www.cookerru.com/wp-content/uploads/2021/01/korean-strawberry-milk-feature-main.jpg'
    ],
    [
        'name' => 'Strawberry Hazelnut',
        'category' => 'milk',
        'description' => 'Strawberry milk with a hint of hazelnut flavor',
        'base_price' => 69.00,
        'image_url' => 'https://theveggieyaya.com/wp-content/uploads/2024/06/iced-strawberry-latte-square-image.jpg'
    ],
    [
        'name' => 'Choco Milk',
        'category' => 'milk',
        'description' => 'Rich chocolate blended with fresh milk',
        'base_price' => 69.00,
        'image_url' => 'https://www.thespruceeats.com/thmb/93UHJ043ztF5RyiPsyFJt_OVCs8=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/chocolate-milk-recipe-2355494-hero-02-80cffdb175904e03a8fd6bb7d6ffc0dd.jpg'
    ],
    // Fruit Soda
    [
        'name' => 'Strawberry',
        'category' => 'soda',
        'description' => 'Refreshing strawberry-flavored sparkling soda',
        'base_price' => 49.00,
        'image_url' => 'https://mocktail.net/wp-content/uploads/2022/06/Homemade-Strawberry-Soda_11ig.jpg'
    ],
    [
        'name' => 'Blueberry',
        'category' => 'soda',
        'description' => 'Sweet blueberry soda with a fizzy kick',
        'base_price' => 49.00,
        'image_url' => 'https://img.freepik.com/premium-photo/blueberry-soda-plastic-cup_504796-499.jpg'
    ],
    [
        'name' => 'Green Apple',
        'category' => 'soda',
        'description' => 'Crisp green apple flavored sparkling drink',
        'base_price' => 49.00,
        'image_url' => 'https://www.shutterstock.com/image-photo/plastic-glass-refreshing-iced-green-260nw-1850934520.jpg'
    ],
    [
        'name' => 'Blue Lemonade',
        'category' => 'soda',
        'description' => 'Tangy blue lemonade with sparkling water',
        'base_price' => 49.00,
        'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRrabIPKt5940sVoStVoFKt7iV9QBlSWVaZLA&s'
    ],
    [
        'name' => 'Lychee',
        'category' => 'soda',
        'description' => 'Sweet lychee flavored refreshing soda',
        'base_price' => 49.00,
        'image_url' => 'https://img.freepik.com/free-photo/lychee-juice-lychee-fruit_1150-13685.jpg?semt=ais_hybrid&w=740&q=80'
    ],
    [
        'name' => 'Four Season',
        'category' => 'soda',
        'description' => 'Mixed tropical fruits in a fizzy drink',
        'base_price' => 49.00,
        'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVEX8dwT83HVWXLS5WuX7YY0fZZwuwZVZwOA&s'
    ]
];

$inserted = 0;
$skipped = 0;
$errors = [];

foreach ($products as $product) {
    // Check if product already exists
    $checkStmt = $conn->prepare("SELECT id FROM products WHERE name = ? AND category = ?");
    $checkStmt->bind_param("ss", $product['name'], $product['category']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $skipped++;
        $checkStmt->close();
        continue;
    }
    $checkStmt->close();
    
    // Insert new product
    $stmt = $conn->prepare("INSERT INTO products (name, category, description, base_price, image_url, is_available) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssds", 
        $product['name'], 
        $product['category'], 
        $product['description'], 
        $product['base_price'], 
        $product['image_url']
    );
    
    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "Failed to insert {$product['name']}: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();

echo json_encode([
    'success' => true,
    'message' => "Seeding complete",
    'inserted' => $inserted,
    'skipped' => $skipped,
    'errors' => $errors
]);
