<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

checkAuth();
require_once '../config/database.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $range = isset($_GET['range']) ? sanitize($_GET['range']) : 'month';

    $dateFormat = '';
    $groupBy = '';

    switch ($range) {
        case 'day':
            $dateFormat = '%Y-%m-%d %H:00';
            $groupBy = 'HOUR(order_date)';
            $limit = 24;
            break;
        case 'week':
            $dateFormat = '%Y-%m-%d';
            $groupBy = 'DATE(order_date)';
            $limit = 7;
            break;
        case 'year':
            $dateFormat = '%Y-%m';
            $groupBy = 'MONTH(order_date)';
            $limit = 12;
            break;
        case 'month':
        default:
            $dateFormat = '%Y-%m-%d';
            $groupBy = 'DATE(order_date)';
            $limit = 30;
            break;
    }

    $stats = [];

    $result = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['total_orders'];

    $result = $conn->query("SELECT SUM(final_amount) as total_revenue FROM orders WHERE status = 'completed'");
    $revenue = $result->fetch_assoc()['total_revenue'];
    $stats['total_revenue'] = $revenue ? floatval($revenue) : 0;

    $result = $conn->query("SELECT COUNT(*) as completed FROM orders WHERE status = 'completed'");
    $stats['completed_orders'] = $result->fetch_assoc()['completed'];

    $result = $conn->query("SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['pending'];

    $result = $conn->query("SELECT COUNT(*) as approved FROM orders WHERE status = 'approved'");
    $stats['approved_orders'] = $result->fetch_assoc()['approved'];

    $result = $conn->query("SELECT COUNT(*) as rejected FROM orders WHERE status = 'rejected'");
    $stats['rejected_orders'] = $result->fetch_assoc()['rejected'];

    $result = $conn->query("SELECT AVG(final_amount) as avg_order_value FROM orders WHERE status = 'completed'");
    $avg = $result->fetch_assoc()['avg_order_value'];
    $stats['avg_order_value'] = $avg ? round(floatval($avg), 2) : 0;

    $trendSQL = "SELECT 
                    DATE_FORMAT(order_date, '$dateFormat') as period,
                    COUNT(*) as order_count,
                    SUM(final_amount) as revenue
                 FROM orders 
                 WHERE status = 'completed'
                 GROUP BY period
                 ORDER BY period DESC
                 LIMIT $limit";

    $result = $conn->query($trendSQL);
    $sales_trend = [];

    while ($row = $result->fetch_assoc()) {
        $sales_trend[] = [
            'period' => $row['period'],
            'orders' => intval($row['order_count']),
            'revenue' => floatval($row['revenue'])
        ];
    }

    $sales_trend = array_reverse($sales_trend);

    $productsSQL = "SELECT 
                        p.name,
                        p.category,
                        COUNT(oi.id) as sales_count,
                        SUM(oi.quantity) as total_quantity,
                        SUM(oi.subtotal) as total_revenue
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    JOIN products p ON p.id = oi.product_id
                    WHERE o.status = 'completed'
                    GROUP BY p.id, p.name, p.category
                    ORDER BY total_revenue DESC
                    LIMIT 10";

    $result = $conn->query($productsSQL);
    $product_performance = [];

    while ($row = $result->fetch_assoc()) {
        $product_performance[] = [
            'name' => $row['name'],
            'category' => $row['category'],
            'sales_count' => intval($row['sales_count']),
            'total_quantity' => intval($row['total_quantity']),
            'total_revenue' => floatval($row['total_revenue'])
        ];
    }

    $categorySQL = "SELECT 
                        p.category,
                        COUNT(oi.id) as sales_count,
                        SUM(oi.subtotal) as total_revenue
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    JOIN products p ON p.id = oi.product_id
                    WHERE o.status = 'completed'
                    GROUP BY p.category
                    ORDER BY total_revenue DESC";

    $result = $conn->query($categorySQL);
    $category_performance = [];

    while ($row = $result->fetch_assoc()) {
        $category_performance[] = [
            'category' => $row['category'],
            'sales_count' => intval($row['sales_count']),
            'total_revenue' => floatval($row['total_revenue'])
        ];
    }

    $paymentSQL = "SELECT 
                        payment_method,
                        COUNT(*) as order_count,
                        SUM(final_amount) as total_revenue
                    FROM orders 
                    WHERE status = 'completed'
                    GROUP BY payment_method";

    $result = $conn->query($paymentSQL);
    $payment_distribution = [];

    while ($row = $result->fetch_assoc()) {
        $payment_distribution[] = [
            'method' => $row['payment_method'] ?: 'Not specified',
            'order_count' => intval($row['order_count']),
            'total_revenue' => floatval($row['total_revenue'])
        ];
    }

    $customerSQL = "SELECT 
                        is_student,
                        COUNT(*) as order_count,
                        SUM(final_amount) as total_revenue,
                        AVG(final_amount) as avg_spent
                    FROM orders 
                    WHERE status = 'completed'
                    GROUP BY is_student";

    $result = $conn->query($customerSQL);
    $customer_distribution = [];

    while ($row = $result->fetch_assoc()) {
        $customer_distribution[] = [
            'type' => $row['is_student'] ? 'Student' : 'Visitor',
            'order_count' => intval($row['order_count']),
            'total_revenue' => floatval($row['total_revenue']),
            'avg_spent' => round(floatval($row['avg_spent']), 2)
        ];
    }

    $dailySQL = "SELECT 
                    DATE(order_date) as date,
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'completed' THEN final_amount ELSE 0 END) as daily_revenue
                 FROM orders 
                 WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY date
                 ORDER BY date DESC";

    $result = $conn->query($dailySQL);
    $daily_summary = [];

    while ($row = $result->fetch_assoc()) {
        $daily_summary[] = [
            'date' => $row['date'],
            'total_orders' => intval($row['total_orders']),
            'completed_orders' => intval($row['completed_orders']),
            'pending_orders' => intval($row['pending_orders']),
            'daily_revenue' => floatval($row['daily_revenue'])
        ];
    }

    $ingredientsSQL = "SELECT 
                          name,
                          unit,
                          bottles_count,
                          available_quantity,
                          bottle_capacity,
                          ROUND((available_quantity / (bottles_count * bottle_capacity)) * 100, 2) as percentage
                       FROM ingredients 
                       WHERE available_quantity <= min_threshold 
                       OR (available_quantity / (bottles_count * bottle_capacity)) * 100 <= 20
                       ORDER BY percentage ASC";

    $result = $conn->query($ingredientsSQL);
    $low_stock = [];

    while ($row = $result->fetch_assoc()) {
        $low_stock[] = [
            'name' => $row['name'],
            'unit' => $row['unit'],
            'bottles_count' => intval($row['bottles_count']),
            'available_quantity' => floatval($row['available_quantity']),
            'percentage' => floatval($row['percentage'])
        ];
    }

    $peakSQL = "SELECT 
                    HOUR(order_date) as hour,
                    COUNT(*) as order_count
                FROM orders 
                WHERE status = 'completed'
                GROUP BY hour
                ORDER BY order_count DESC
                LIMIT 5";

    $result = $conn->query($peakSQL);
    $peak_hours = [];

    while ($row = $result->fetch_assoc()) {
        $hour = intval($row['hour']);
        $time_label = sprintf("%02d:00-%02d:00", $hour, $hour + 1);
        $peak_hours[] = [
            'hour' => $hour,
            'time_range' => $time_label,
            'order_count' => intval($row['order_count'])
        ];
    }

    $analytics_data = [
        'stats' => $stats,
        'sales_trend' => $sales_trend,
        'product_performance' => $product_performance,
        'category_performance' => $category_performance,
        'payment_distribution' => $payment_distribution,
        'customer_distribution' => $customer_distribution,
        'daily_summary' => $daily_summary,
        'low_stock_ingredients' => $low_stock,
        'peak_hours' => $peak_hours,
        'time_range' => $range
    ];

    jsonResponse(true, 'Analytics data retrieved', $analytics_data);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkAuth();

    if (!isAdmin()) {
        jsonResponse(false, 'Admin access required for reports');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $report_type = isset($data['report_type']) ? sanitize($data['report_type']) : 'sales';
    $start_date = isset($data['start_date']) ? sanitize($data['start_date']) : date('Y-m-01');
    $end_date = isset($data['end_date']) ? sanitize($data['end_date']) : date('Y-m-d');
    $format = isset($data['format']) ? sanitize($data['format']) : 'json';

    if (!strtotime($start_date) || !strtotime($end_date)) {
        jsonResponse(false, 'Invalid date format');
    }

    switch ($report_type) {
        case 'sales':
            $reportSQL = "SELECT 
                            o.order_number,
                            o.customer_name,
                            o.order_date,
                            o.status,
                            o.total_amount,
                            o.discount_amount,
                            o.final_amount,
                            o.payment_method,
                            COUNT(oi.id) as item_count
                          FROM orders o
                          LEFT JOIN order_items oi ON o.id = oi.order_id
                          WHERE DATE(o.order_date) BETWEEN ? AND ?
                          GROUP BY o.id
                          ORDER BY o.order_date DESC";
            break;

        case 'products':
            $reportSQL = "SELECT 
                            p.name,
                            p.category,
                            p.base_price,
                            SUM(oi.quantity) as total_sold,
                            SUM(oi.subtotal) as total_revenue
                          FROM products p
                          LEFT JOIN order_items oi ON p.id = oi.product_id
                          LEFT JOIN orders o ON oi.order_id = o.id
                          WHERE DATE(o.order_date) BETWEEN ? AND ?
                            AND o.status = 'completed'
                          GROUP BY p.id
                          ORDER BY total_revenue DESC";
            break;

        case 'inventory':
            $reportSQL = "SELECT 
                            name,
                            unit,
                            bottles_count,
                            available_quantity,
                            ROUND((available_quantity / (bottles_count * bottle_capacity)) * 100, 2) as percentage,
                            CASE 
                                WHEN available_quantity <= min_threshold THEN 'CRITICAL'
                                WHEN (available_quantity / (bottles_count * bottle_capacity)) * 100 <= 30 THEN 'LOW'
                                ELSE 'OK'
                            END as stock_status
                          FROM ingredients 
                          ORDER BY percentage ASC";
            break;

        default:
            jsonResponse(false, 'Invalid report type');
    }

    $stmt = $conn->prepare($reportSQL);

    if (in_array($report_type, ['sales', 'products'])) {
        $stmt->bind_param("ss", $start_date, $end_date);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $report_data = [];

    while ($row = $result->fetch_assoc()) {
        $report_data[] = $row;
    }

    $stmt->close();

    $report_summary = [
        'report_type' => $report_type,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'generated_at' => date('Y-m-d H:i:s'),
        'total_records' => count($report_data)
    ];

    $report = [
        'summary' => $report_summary,
        'data' => $report_data
    ];

    if ($format === 'csv') {
        $csv_content = '';

        if (!empty($report_data)) {
            $headers = array_keys($report_data[0]);
            $csv_content .= implode(',', $headers) . "\n";

            foreach ($report_data as $row) {
                $csv_content .= implode(',', array_map(function ($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row)) . "\n";
            }
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $report_type . '_report_' . date('Y-m-d') . '.csv"');
        echo $csv_content;
        exit;
    } else {
        jsonResponse(true, 'Report generated successfully', $report);
    }
} else {
    jsonResponse(false, 'Method not allowed');
}

$conn->close();
