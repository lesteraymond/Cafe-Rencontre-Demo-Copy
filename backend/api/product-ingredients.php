<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    jsonResponse(false, 'Authentication required');
}

$conn = getDBConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get ingredients for a product
        if (isset($_GET['product_id'])) {
            $productId = intval($_GET['product_id']);
            $stmt = $conn->prepare("SELECT pi.*, i.name as ingredient_name, i.unit 
                                    FROM product_ingredients pi
                                    JOIN ingredients i ON i.id = pi.ingredient_id
                                    WHERE pi.product_id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $ingredients = [];
            while ($row = $result->fetch_assoc()) {
                $ingredients[] = $row;
            }
            $stmt->close();
            jsonResponse(true, '', $ingredients);
        } else {
            // Get all product-ingredient mappings
            $result = $conn->query("SELECT pi.*, p.name as product_name, i.name as ingredient_name, i.unit
                                    FROM product_ingredients pi
                                    JOIN products p ON p.id = pi.product_id
                                    JOIN ingredients i ON i.id = pi.ingredient_id
                                    ORDER BY p.name, i.name");
            $mappings = [];
            while ($row = $result->fetch_assoc()) {
                $mappings[] = $row;
            }
            jsonResponse(true, '', $mappings);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['product_id']) || !isset($data['ingredient_id']) || !isset($data['quantity_needed'])) {
            jsonResponse(false, 'Product ID, Ingredient ID, and quantity needed are required');
        }

        $productId = intval($data['product_id']);
        $ingredientId = intval($data['ingredient_id']);
        $quantityNeeded = floatval($data['quantity_needed']);

        if ($quantityNeeded <= 0) {
            jsonResponse(false, 'Quantity needed must be greater than 0');
        }

        $stmt = $conn->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, quantity_needed) 
                                VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE quantity_needed = ?");
        $stmt->bind_param("iidd", $productId, $ingredientId, $quantityNeeded, $quantityNeeded);

        if ($stmt->execute()) {
            jsonResponse(true, 'Product ingredient mapping saved');
        } else {
            jsonResponse(false, 'Failed to save mapping: ' . $stmt->error);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['product_id']) || !isset($_GET['ingredient_id'])) {
            jsonResponse(false, 'Product ID and Ingredient ID are required');
        }

        $productId = intval($_GET['product_id']);
        $ingredientId = intval($_GET['ingredient_id']);

        $stmt = $conn->prepare("DELETE FROM product_ingredients WHERE product_id = ? AND ingredient_id = ?");
        $stmt->bind_param("ii", $productId, $ingredientId);

        if ($stmt->execute()) {
            jsonResponse(true, 'Product ingredient mapping deleted');
        } else {
            jsonResponse(false, 'Failed to delete mapping');
        }
        break;

    default:
        jsonResponse(false, 'Method not allowed');
}

$conn->close();
