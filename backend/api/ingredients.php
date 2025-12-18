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

require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/database.php';

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
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

    $sql = "SELECT *, 
            ROUND((available_quantity / (bottles_count * bottle_capacity)) * 100, 2) as percentage 
            FROM ingredients WHERE 1=1";

    $params = [];
    $types = "";

    if ($search) {
        $sql .= " AND name LIKE ?";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $types .= "s";
    }

    $sql .= " ORDER BY name ASC";

    if ($params) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $ingredients = [];
    while ($row = $result->fetch_assoc()) {
        if (!isset($row['percentage'])) {
            $totalCapacity = $row['bottles_count'] * $row['bottle_capacity'];
            $row['percentage'] = $totalCapacity > 0 ?
                round(($row['available_quantity'] / $totalCapacity) * 100, 2) : 0;
        }
        $ingredients[] = $row;
    }

    if (isset($stmt)) $stmt->close();

    jsonResponse(true, '', $ingredients);
}

function handlePostRequest($conn)
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        jsonResponse(false, 'Invalid JSON data');
    }

    $required = ['name', 'unit', 'bottles_count', 'available_quantity'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            jsonResponse(false, "Field '$field' is required");
        }
    }

    $name = sanitize(trim($data['name']));
    $unit = sanitize(trim($data['unit']));
    $bottles_count = intval($data['bottles_count']);
    $available_quantity = floatval($data['available_quantity']);
    $bottle_capacity = isset($data['bottle_capacity']) ? floatval($data['bottle_capacity']) : 2000;
    $min_threshold = isset($data['min_threshold']) ? floatval($data['min_threshold']) : 100;

    if ($bottles_count < 0) {
        jsonResponse(false, 'Bottles count cannot be negative');
    }

    if ($available_quantity < 0) {
        jsonResponse(false, 'Available quantity cannot be negative');
    }

    if ($bottle_capacity <= 0) {
        jsonResponse(false, 'Bottle capacity must be greater than 0');
    }

    $stmt = $conn->prepare("INSERT INTO ingredients (name, unit, bottles_count, available_quantity, bottle_capacity, min_threshold) 
                           VALUES (?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        jsonResponse(false, 'Database error: ' . $conn->error);
    }

    $stmt->bind_param("ssiddd", $name, $unit, $bottles_count, $available_quantity, $bottle_capacity, $min_threshold);

    if ($stmt->execute()) {
        $ingredientId = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("SELECT *, 
                               ROUND((available_quantity / (bottles_count * bottle_capacity)) * 100, 2) as percentage 
                               FROM ingredients WHERE id = ?");
        $stmt->bind_param("i", $ingredientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $newIngredient = $result->fetch_assoc();
        $stmt->close();

        jsonResponse(true, 'Ingredient added successfully', $newIngredient);
    } else {
        $error = $stmt->error;
        $stmt->close();
        jsonResponse(false, 'Failed to add ingredient: ' . $error);
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
        jsonResponse(false, 'Ingredient ID is required for update');
    }

    $id = intval($data['id']);

    $checkStmt = $conn->prepare("SELECT id FROM ingredients WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        jsonResponse(false, 'Ingredient not found');
    }
    $checkStmt->close();

    $allowedFields = ['name', 'unit', 'bottles_count', 'available_quantity', 'bottle_capacity', 'min_threshold'];
    $updates = [];
    $params = [];
    $types = "";

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";

            if (in_array($field, ['bottles_count'])) {
                $params[] = intval($data[$field]);
                $types .= "i";
            } elseif (in_array($field, ['available_quantity', 'bottle_capacity', 'min_threshold'])) {
                $params[] = floatval($data[$field]);
                $types .= "d";
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

    $sql = "UPDATE ingredients SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        jsonResponse(false, 'Database error: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $stmt->close();

        $stmt = $conn->prepare("SELECT *, 
                               ROUND((available_quantity / (bottles_count * bottle_capacity)) * 100, 2) as percentage 
                               FROM ingredients WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $updatedIngredient = $result->fetch_assoc();
        $stmt->close();

        jsonResponse(true, 'Ingredient updated successfully', $updatedIngredient);
    } else {
        $error = $stmt->error;
        $stmt->close();
        jsonResponse(false, 'Failed to update ingredient: ' . $error);
    }
}

function handleDeleteRequest($conn)
{
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        jsonResponse(false, 'Ingredient ID is required for deletion');
    }

    $id = intval($_GET['id']);

    $checkStmt = $conn->prepare("SELECT id FROM ingredients WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        jsonResponse(false, 'Ingredient not found');
    }
    $checkStmt->close();

    $stmt = $conn->prepare("DELETE FROM ingredients WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        jsonResponse(true, 'Ingredient deleted successfully');
    } else {
        $error = $stmt->error;
        $stmt->close();
        jsonResponse(false, 'Failed to delete ingredient: ' . $error);
    }
}
