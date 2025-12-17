<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

checkAuth();
require_once '../config/database.php';
require_once '../includes/functions.php';

$conn = getDBConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

        $whereClauses = [];
        $params = [];
        $types = '';

        if ($status && in_array($status, ['pending', 'approved', 'completed', 'rejected'])) {
            $whereClauses[] = "status = ?";
            $params[] = $status;
            $types .= 's';
        }

        if ($search) {
            $whereClauses[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }

        $whereSQL = $whereClauses ? "WHERE " . implode(' AND ', $whereClauses) : "";

        $sql = "SELECT * FROM orders $whereSQL ORDER BY order_date DESC";
        $stmt = $conn->prepare($sql);

        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];

        while ($row = $result->fetch_assoc()) {
            $itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemsStmt->bind_param("i", $row['id']);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();
            $items = [];

            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }

            $row['items'] = $items;
            $row['items_count'] = count($items);
            $orders[] = $row;

            $itemsStmt->close();
        }

        $stmt->close();
        jsonResponse(true, '', $orders);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['customer_name', 'total_amount', 'items'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                jsonResponse(false, "Field '$field' is required");
            }
        }

        $order_number = generateOrderNumber();

        $customer_name = sanitize($data['customer_name']);
        $customer_email = isset($data['customer_email']) ? sanitize($data['customer_email']) : '';
        $customer_phone = isset($data['customer_phone']) ? sanitize($data['customer_phone']) : '';
        $room_number = isset($data['room_number']) ? sanitize($data['room_number']) : '';
        $total_amount = floatval($data['total_amount']);
        $payment_method = isset($data['payment_method']) ? sanitize($data['payment_method']) : 'cash';
        $is_student = isset($data['is_student']) ? intval($data['is_student']) : 0;
        $discount_amount = isset($data['discount_amount']) ? floatval($data['discount_amount']) : 0;
        $final_amount = isset($data['final_amount']) ? floatval($data['final_amount']) : $total_amount;

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, 
                                  room_number, total_amount, payment_method, is_student, discount_amount, final_amount) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssssdssdd",
                $order_number,
                $customer_name,
                $customer_email,
                $customer_phone,
                $room_number,
                $total_amount,
                $payment_method,
                $is_student,
                $discount_amount,
                $final_amount
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create order: " . $stmt->error);
            }

            $order_id = $stmt->insert_id;
            $stmt->close();

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
                    "iissiidds",
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

            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();

            $itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemsStmt->bind_param("i", $order_id);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();
            $orderItems = [];

            while ($item = $itemsResult->fetch_assoc()) {
                $orderItems[] = $item;
            }

            $order['items'] = $orderItems;
            $order['items_count'] = count($orderItems);

            jsonResponse(true, 'Order created successfully', $order);
        } catch (Exception $e) {
            $conn->rollback();
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $putData);
        $data = json_decode(array_keys($putData)[0], true);

        if (!isset($data['id']) || !isset($data['status'])) {
            jsonResponse(false, 'Order ID and status are required');
        }

        $id = intval($data['id']);
        $status = sanitize($data['status']);

        if (!in_array($status, ['pending', 'approved', 'completed', 'rejected'])) {
            jsonResponse(false, 'Invalid status');
        }

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            jsonResponse(true, 'Order status updated successfully');
        } else {
            jsonResponse(false, 'Failed to update order status');
        }
        break;

    case 'DELETE':
        if (!isAdmin()) {
            jsonResponse(false, 'Admin access required');
        }

        if (!isset($_GET['id'])) {
            jsonResponse(false, 'Order ID is required');
        }

        $id = intval($_GET['id']);

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete order items");
            }

            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $conn->commit();
                jsonResponse(true, 'Order deleted successfully');
            } else {
                throw new Exception("Failed to delete order");
            }
        } catch (Exception $e) {
            $conn->rollback();
            jsonResponse(false, $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Method not allowed');
}

$conn->close();
