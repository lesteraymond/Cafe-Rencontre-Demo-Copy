<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

checkAuth();
require_once '../config/database.php';

$conn = getDBConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

        if ($search) {
            $searchTerm = "%$search%";
            $stmt = $conn->prepare("SELECT *, 
                                   ROUND((available_quantity / (bottles_count * bottle_capacity)) * 100, 2) as percentage 
                                   FROM ingredients 
                                   WHERE name LIKE ? 
                                   ORDER BY name");
            $stmt->bind_param("s", $searchTerm);
        } else {
            $stmt = $conn->prepare("SELECT *, 
                                   ROUND((available_quantity / (bottles_count * bottle_capacity)) * 100, 2) as percentage 
                                   FROM ingredients 
                                   ORDER BY name");
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $ingredients = [];

        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }

        $stmt->close();
        jsonResponse(true, '', $ingredients);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['name', 'unit', 'bottles_count', 'available_quantity'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                jsonResponse(false, "Field '$field' is required");
            }
        }

        $name = sanitize($data['name']);
        $unit = sanitize($data['unit']);
        $bottles_count = intval($data['bottles_count']);
        $available_quantity = floatval($data['available_quantity']);
        $bottle_capacity = isset($data['bottle_capacity']) ? floatval($data['bottle_capacity']) : 2000;
        $min_threshold = isset($data['min_threshold']) ? floatval($data['min_threshold']) : 100;

        $stmt = $conn->prepare("INSERT INTO ingredients (name, unit, bottles_count, available_quantity, bottle_capacity, min_threshold) 
                               VALUES (?, ?, ?, ?, ?, ?)");
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
            $ingredient = $result->fetch_assoc();

            jsonResponse(true, 'Ingredient added successfully', $ingredient);
        } else {
            jsonResponse(false, 'Failed to add ingredient: ' . $stmt->error);
        }
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $putData);
        $data = json_decode(array_keys($putData)[0], true);

        if (!isset($data['id'])) {
            jsonResponse(false, 'Ingredient ID is required');
        }

        $id = intval($data['id']);
        $fields = [];
        $types = '';
        $values = [];

        $allowedFields = ['name', 'unit', 'bottles_count', 'available_quantity', 'bottle_capacity', 'min_threshold'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                if (in_array($field, ['bottles_count'])) {
                    $types .= 'i';
                    $values[] = intval($data[$field]);
                } elseif (in_array($field, ['available_quantity', 'bottle_capacity', 'min_threshold'])) {
                    $types .= 'd';
                    $values[] = floatval($data[$field]);
                } else {
                    $types .= 's';
                    $values[] = sanitize($data[$field]);
                }
            }
        }

        if (empty($fields)) {
            jsonResponse(false, 'No fields to update');
        }

        $values[] = $id;
        $types .= 'i';

        $sql = "UPDATE ingredients SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            jsonResponse(true, 'Ingredient updated successfully');
        } else {
            jsonResponse(false, 'Failed to update ingredient: ' . $stmt->error);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            jsonResponse(false, 'Ingredient ID is required');
        }

        $id = intval($_GET['id']);
        $stmt = $conn->prepare("DELETE FROM ingredients WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            jsonResponse(true, 'Ingredient deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete ingredient');
        }
        break;

    default:
        jsonResponse(false, 'Method not allowed');
}

$conn->close();
