<?php
/**
 * Public Orders API - For customer-facing order submissions
 * This endpoint allows customers to place orders without authentication
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../includes/functions.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['customer_name', 'total_amount', 'items'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Generate order number
$order_number = generateOrderNumber();

// Sanitize inputs
$customer_name = sanitize($data['customer_name']);
$customer_email = isset($data['customer_email']) ? sanitize($data['customer_email']) : '';
$customer_phone = isset($data['customer_phone']) ? sanitize($data['customer_phone']) : '';
$room_number = isset($data['room_number']) ? sanitize($data['room_number']) : '';
$total_amount = floatval($data['total_amount']);
$payment_method = isset($data['payment_method']) ? sanitize($data['payment_method']) : 'cash';
$payment_proof = isset($data['payment_proof']) ? sanitize($data['payment_proof']) : '';
$is_student = isset($data['is_student']) ? intval($data['is_student']) : 0;
$discount_amount = isset($data['discount_amount']) ? floatval($data['discount_amount']) : 0;
$final_amount = isset($data['final_amount']) ? floatval($data['final_amount']) : $total_amount;

$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, 
                          room_number, total_amount, payment_method, payment_proof, is_student, discount_amount, final_amount) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssdssidd",
        $order_number,
        $customer_name,
        $customer_email,
        $customer_phone,
        $room_number,
        $total_amount,
        $payment_method,
        $payment_proof,
        $is_student,
        $discount_amount,
        $final_amount
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to create order: " . $stmt->error);
    }

    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    $items = $data['items'];
    foreach ($items as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, 
                              temperature, quantity, unit_price, subtotal, customizations) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $product_id = isset($item['product_id']) ? intval($item['product_id']) : 0;
        $product_name = sanitize($item['product_name']);
        $size = isset($item['size']) ? sanitize($item['size']) : '';
        $temperature = isset($item['temperature']) ? sanitize($item['temperature']) : '';
        $quantity = intval($item['quantity']);
        $unit_price = floatval($item['unit_price']);
        $subtotal = floatval($item['subtotal']);
        $customizations = isset($item['customizations']) ? sanitize($item['customizations']) : '';

        $stmt->bind_param(
            "iisssidds",
            $order_id,
            $product_id,
            $product_name,
            $size,
            $temperature,
            $quantity,
            $unit_price,
            $subtotal,
            $customizations
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to add order item: " . $stmt->error);
        }

        $stmt->close();
    }

    $conn->commit();

    // Return success with order details
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'data' => [
            'id' => $order_id,
            'order_number' => $order_number,
            'total_amount' => $total_amount,
            'final_amount' => $final_amount,
            'status' => 'pending'
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
