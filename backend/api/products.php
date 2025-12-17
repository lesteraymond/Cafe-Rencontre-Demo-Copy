<?php
header("Access-Control-Allow-Origin: http://localhost:8001");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/session.php'; // or sessions.php depending on your file
require_once '../includes/functions.php';
require_once '../config/database.php';

// Check authentication
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    jsonResponse(false, 'Authentication required');
}

$conn = getDBConnection();
if (!$conn) {
    jsonResponse(false, 'Database connection failed');
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGetRequest($conn);
        break;

    case 'POST':
        handlePostRequest($conn);
        break;

    case 'PUT':
        handlePutRequest($conn);
        break;

    case 'DELETE':
        handleDeleteRequest($conn);
        break;

    default:
        jsonResponse(false, 'Method not allowed');
}

$conn->close();


function handleGetRequest($conn)
{
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            jsonResponse(false, 'Product not found');
        }

        $product = $result->fetch_assoc();
        $stmt->close();

        jsonResponse(true, 'Product retrieved', $product);
    }

    $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    $types = "";

    if ($category && in_array($category, ['brewed', 'milk', 'soda'])) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }

    if ($search) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    $sql .= " ORDER BY created_at DESC";

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
        $products[] = $row;
    }

    if (isset($stmt)) $stmt->close();

    jsonResponse(true, '', $products);
}

function handlePostRequest($conn)
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        jsonResponse(false, 'Invalid JSON data');
    }

    $required = ['name', 'category', 'description', 'base_price'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            jsonResponse(false, "Field '$field' is required");
        }
    }

    $name = sanitize(trim($data['name']));
    $category = sanitize(trim($data['category']));
    $description = sanitize(trim($data['description']));
    $base_price = floatval($data['base_price']);
    $image_url = isset($data['image_url']) ? sanitize(trim($data['image_url'])) : '';
    $is_available = isset($data['is_available']) ? intval($data['is_available']) : 1;

    $validCategories = ['brewed', 'milk', 'soda'];
    if (!in_array($category, $validCategories)) {
        jsonResponse(false, 'Invalid category. Must be: brewed, milk, or soda');
    }

    if ($base_price <= 0) {
        jsonResponse(false, 'Price must be greater than 0');
    }

    $stmt = $conn->prepare("INSERT INTO products (name, category, description, base_price, image_url, is_available) 
                           VALUES (?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        jsonResponse(false, 'Database error: ' . $conn->error);
    }

    $stmt->bind_param("sssdss", $name, $category, $description, $base_price, $image_url, $is_available);

    if ($stmt->execute()) {
        $productId = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $newProduct = $result->fetch_assoc();
        $stmt->close();

        jsonResponse(true, 'Product created successfully', $newProduct);
    } else {
        $error = $stmt->error;
        $stmt->close();
        jsonResponse(false, 'Failed to create product: ' . $error);
    }
}

function handlePutRequest($conn)
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        jsonResponse(false, 'Invalid JSON data');
    }

    if (!isset($data['id']) || !is_numeric($data['id'])) {
        jsonResponse(false, 'Product ID is required for update');
    }

    $id = intval($data['id']);

    $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        jsonResponse(false, 'Product not found');
    }
    $checkStmt->close();

    $allowedFields = ['name', 'category', 'description', 'base_price', 'image_url', 'is_available'];
    $updates = [];
    $params = [];
    $types = "";

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";

            if ($field === 'base_price') {
                $params[] = floatval($data[$field]);
                $types .= "d";
            } elseif ($field === 'is_available') {
                $params[] = intval($data[$field]);
                $types .= "i";
            } else {
                $params[] = sanitize(trim($data[$field]));
                $types .= "s";
            }
        }
    }

    if (empty($updates)) {
        jsonResponse(false, 'No fields to update');
    }

    $params[] = $id;
    $types .= "i";

    $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        jsonResponse(false, 'Database error: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $stmt->close();

        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $updatedProduct = $result->fetch_assoc();
        $stmt->close();

        jsonResponse(true, 'Product updated successfully', $updatedProduct);
    } else {
        $error = $stmt->error;
        $stmt->close();
        jsonResponse(false, 'Failed to update product: ' . $error);
    }
}

function handleDeleteRequest($conn)
{
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        jsonResponse(false, 'Product ID is required for deletion');
    }

    $id = intval($_GET['id']);

    $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        jsonResponse(false, 'Product not found');
    }
    $checkStmt->close();

    $orderCheck = $conn->prepare("SELECT COUNT(*) as order_count FROM order_items WHERE product_id = ?");
    $orderCheck->bind_param("i", $id);
    $orderCheck->execute();
    $orderResult = $orderCheck->get_result();
    $orderRow = $orderResult->fetch_assoc();
    $orderCheck->close();

    if ($orderRow['order_count'] > 0) {
        jsonResponse(false, 'Cannot delete product with existing orders');
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        jsonResponse(true, 'Product deleted successfully');
    } else {
        $error = $stmt->error;
        $stmt->close();
        jsonResponse(false, 'Failed to delete product: ' . $error);
    }
}
