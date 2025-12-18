<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed');
}

$view = isset($_GET['view']) ? $_GET['view'] : 'daily';
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;
$startDate = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');

switch ($view) {
    case 'daily':
        // Daily stats for date range
        $sql = "SELECT pos.order_date, p.name as product_name, p.category,
                       pos.order_count, pos.total_quantity, pos.total_revenue
                FROM product_order_stats pos
                JOIN products p ON p.id = pos.product_id
                WHERE pos.order_date BETWEEN ? AND ?";
        
        if ($productId) {
            $sql .= " AND pos.product_id = ?";
        }
        
        $sql .= " ORDER BY pos.order_date DESC, pos.total_revenue DESC";
        
        $stmt = $conn->prepare($sql);
        if ($productId) {
            $stmt->bind_param("ssi", $startDate, $endDate, $productId);
        } else {
            $stmt->bind_param("ss", $startDate, $endDate);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        $stmt->close();
        jsonResponse(true, '', $stats);
        break;

    case 'monthly':
        // Monthly aggregated stats
        $sql = "SELECT DATE_FORMAT(pos.order_date, '%Y-%m') as month,
                       p.name as product_name, p.category,
                       SUM(pos.order_count) as total_orders,
                       SUM(pos.total_quantity) as total_quantity,
                       SUM(pos.total_revenue) as total_revenue
                FROM product_order_stats pos
                JOIN products p ON p.id = pos.product_id
                WHERE pos.order_date BETWEEN ? AND ?";
        
        if ($productId) {
            $sql .= " AND pos.product_id = ?";
        }
        
        $sql .= " GROUP BY month, p.id, p.name, p.category
                  ORDER BY month DESC, total_revenue DESC";
        
        $stmt = $conn->prepare($sql);
        if ($productId) {
            $stmt->bind_param("ssi", $startDate, $endDate, $productId);
        } else {
            $stmt->bind_param("ss", $startDate, $endDate);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        $stmt->close();
        jsonResponse(true, '', $stats);
        break;

    case 'summary':
        // Product summary for date range
        $sql = "SELECT p.id, p.name as product_name, p.category,
                       COALESCE(SUM(pos.order_count), 0) as total_orders,
                       COALESCE(SUM(pos.total_quantity), 0) as total_quantity,
                       COALESCE(SUM(pos.total_revenue), 0) as total_revenue
                FROM products p
                LEFT JOIN product_order_stats pos ON p.id = pos.product_id 
                    AND pos.order_date BETWEEN ? AND ?
                GROUP BY p.id, p.name, p.category
                ORDER BY total_revenue DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        $stmt->close();
        jsonResponse(true, '', $stats);
        break;

    case 'top':
        // Top selling products
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        
        $sql = "SELECT p.id, p.name as product_name, p.category,
                       COALESCE(SUM(pos.order_count), 0) as total_orders,
                       COALESCE(SUM(pos.total_quantity), 0) as total_quantity,
                       COALESCE(SUM(pos.total_revenue), 0) as total_revenue
                FROM products p
                LEFT JOIN product_order_stats pos ON p.id = pos.product_id 
                    AND pos.order_date BETWEEN ? AND ?
                GROUP BY p.id, p.name, p.category
                HAVING total_orders > 0
                ORDER BY total_quantity DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $startDate, $endDate, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        $stmt->close();
        jsonResponse(true, '', $stats);
        break;

    default:
        jsonResponse(false, 'Invalid view. Use: daily, monthly, summary, or top');
}

$conn->close();
