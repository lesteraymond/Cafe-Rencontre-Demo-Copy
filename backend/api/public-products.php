<?php
/**
 * Public Products API - No authentication required
 * Used by the customer-facing menu in Index.php
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get category filter if provided
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT id, name, category, description, base_price, image_url, is_available 
        FROM products 
        WHERE is_available = 1";

$params = [];
$types = "";

if ($category && in_array($category, ['brewed', 'milk', 'soda'])) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY category, name";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$products = [];
while ($row = $result->fetch_assoc()) {
    // Map database fields to frontend expected format
    $products[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'price' => (float)$row['base_price'],
        'cat' => $row['category'],
        'img' => $row['image_url'] ?: 'https://via.placeholder.com/300x200?text=' . urlencode($row['name']),
        'description' => $row['description']
    ];
}

if (isset($stmt)) $stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'data' => $products
]);
