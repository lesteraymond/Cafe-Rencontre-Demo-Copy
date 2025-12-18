<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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
        $action = isset($_GET['action']) ? $_GET['action'] : 'status';
        
        switch ($action) {
            case 'logs':
                // Get inventory logs
                $ingredientId = isset($_GET['ingredient_id']) ? intval($_GET['ingredient_id']) : null;
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
                
                $sql = "SELECT il.*, i.name as ingredient_name 
                        FROM inventory_logs il
                        JOIN ingredients i ON i.id = il.ingredient_id";
                
                if ($ingredientId) {
                    $sql .= " WHERE il.ingredient_id = " . $ingredientId;
                }
                
                $sql .= " ORDER BY il.created_at DESC LIMIT " . $limit;
                
                $result = $conn->query($sql);
                $logs = [];
                while ($row = $result->fetch_assoc()) {
                    $logs[] = $row;
                }
                jsonResponse(true, '', $logs);
                break;
                
            case 'low_stock':
                // Get low stock warnings
                $result = $conn->query("SELECT id, name, unit, bottles_count, available_quantity, min_threshold,
                                        ROUND((available_quantity / NULLIF(bottles_count * bottle_capacity, 0)) * 100, 2) as percentage
                                        FROM ingredients 
                                        WHERE available_quantity <= min_threshold 
                                        OR (bottles_count > 0 AND (available_quantity / (bottles_count * bottle_capacity)) * 100 <= 20)
                                        ORDER BY percentage ASC");
                $lowStock = [];
                while ($row = $result->fetch_assoc()) {
                    $row['status'] = $row['available_quantity'] <= $row['min_threshold'] ? 'CRITICAL' : 'LOW';
                    $lowStock[] = $row;
                }
                jsonResponse(true, '', $lowStock);
                break;
                
            case 'status':
            default:
                // Get full inventory status
                $result = $conn->query("SELECT *,
                                        ROUND((available_quantity / NULLIF(bottles_count * bottle_capacity, 0)) * 100, 2) as percentage,
                                        CASE 
                                            WHEN available_quantity <= min_threshold THEN 'CRITICAL'
                                            WHEN bottles_count > 0 AND (available_quantity / (bottles_count * bottle_capacity)) * 100 <= 20 THEN 'LOW'
                                            WHEN bottles_count > 0 AND (available_quantity / (bottles_count * bottle_capacity)) * 100 <= 50 THEN 'MEDIUM'
                                            ELSE 'OK'
                                        END as stock_status
                                        FROM ingredients
                                        ORDER BY percentage ASC");
                $inventory = [];
                while ($row = $result->fetch_assoc()) {
                    $inventory[] = $row;
                }
                jsonResponse(true, '', $inventory);
                break;
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = isset($data['action']) ? $data['action'] : '';
        
        switch ($action) {
            case 'restock':
                if (!isset($data['ingredient_id']) || !isset($data['quantity'])) {
                    jsonResponse(false, 'Ingredient ID and quantity are required');
                }
                
                $ingredientId = intval($data['ingredient_id']);
                $quantity = floatval($data['quantity']);
                $notes = isset($data['notes']) ? sanitize($data['notes']) : 'Manual restock';
                
                if ($quantity <= 0) {
                    jsonResponse(false, 'Quantity must be greater than 0');
                }
                
                // Get current quantity
                $stmt = $conn->prepare("SELECT available_quantity FROM ingredients WHERE id = ?");
                $stmt->bind_param("i", $ingredientId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if (!$result) {
                    jsonResponse(false, 'Ingredient not found');
                }
                
                $previousQty = $result['available_quantity'];
                $newQty = $previousQty + $quantity;
                
                $conn->begin_transaction();
                try {
                    // Update ingredient
                    $stmt = $conn->prepare("UPDATE ingredients SET available_quantity = ? WHERE id = ?");
                    $stmt->bind_param("di", $newQty, $ingredientId);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Log the change
                    $stmt = $conn->prepare("INSERT INTO inventory_logs 
                                            (ingredient_id, change_type, quantity_change, previous_quantity, new_quantity, notes)
                                            VALUES (?, 'restock', ?, ?, ?, ?)");
                    $stmt->bind_param("iddds", $ingredientId, $quantity, $previousQty, $newQty, $notes);
                    $stmt->execute();
                    $stmt->close();
                    
                    $conn->commit();
                    jsonResponse(true, 'Ingredient restocked successfully', [
                        'previous_quantity' => $previousQty,
                        'added' => $quantity,
                        'new_quantity' => $newQty
                    ]);
                } catch (Exception $e) {
                    $conn->rollback();
                    jsonResponse(false, 'Failed to restock: ' . $e->getMessage());
                }
                break;
                
            case 'adjust':
                if (!isset($data['ingredient_id']) || !isset($data['new_quantity'])) {
                    jsonResponse(false, 'Ingredient ID and new quantity are required');
                }
                
                $ingredientId = intval($data['ingredient_id']);
                $newQty = floatval($data['new_quantity']);
                $notes = isset($data['notes']) ? sanitize($data['notes']) : 'Manual adjustment';
                
                if ($newQty < 0) {
                    jsonResponse(false, 'Quantity cannot be negative');
                }
                
                // Get current quantity
                $stmt = $conn->prepare("SELECT available_quantity FROM ingredients WHERE id = ?");
                $stmt->bind_param("i", $ingredientId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if (!$result) {
                    jsonResponse(false, 'Ingredient not found');
                }
                
                $previousQty = $result['available_quantity'];
                $change = $newQty - $previousQty;
                
                $conn->begin_transaction();
                try {
                    // Update ingredient
                    $stmt = $conn->prepare("UPDATE ingredients SET available_quantity = ? WHERE id = ?");
                    $stmt->bind_param("di", $newQty, $ingredientId);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Log the change
                    $stmt = $conn->prepare("INSERT INTO inventory_logs 
                                            (ingredient_id, change_type, quantity_change, previous_quantity, new_quantity, notes)
                                            VALUES (?, 'adjustment', ?, ?, ?, ?)");
                    $stmt->bind_param("iddds", $ingredientId, $change, $previousQty, $newQty, $notes);
                    $stmt->execute();
                    $stmt->close();
                    
                    $conn->commit();
                    jsonResponse(true, 'Inventory adjusted successfully', [
                        'previous_quantity' => $previousQty,
                        'change' => $change,
                        'new_quantity' => $newQty
                    ]);
                } catch (Exception $e) {
                    $conn->rollback();
                    jsonResponse(false, 'Failed to adjust: ' . $e->getMessage());
                }
                break;
                
            default:
                jsonResponse(false, 'Invalid action. Use: restock or adjust');
        }
        break;

    default:
        jsonResponse(false, 'Method not allowed');
}

$conn->close();
