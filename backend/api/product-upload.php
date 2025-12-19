<?php
header("Access-Control-Allow-Origin: http://localhost:8001");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function
function jsonResp($success, $message = '', $data = [])
{
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if (!empty($data)) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    jsonResp(false, 'Authentication required');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResp(false, 'Invalid request method');
}

if (!isset($_FILES['image'])) {
    jsonResp(false, 'No image uploaded');
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResp(false, 'Upload error code: ' . $file['error']);
}

// Validate file type by extension
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    jsonResp(false, 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
}

// Additional check using getimagesize
$imageInfo = @getimagesize($file['tmp_name']);
if ($imageInfo === false) {
    jsonResp(false, 'File is not a valid image');
}

// Validate file size (5MB max)
if ($file['size'] > 5 * 1024 * 1024) {
    jsonResp(false, 'File too large (max 5MB)');
}

// Create upload directory
$uploadDir = __DIR__ . '/../uploads/products/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        jsonResp(false, 'Failed to create upload directory');
    }
}

// Generate unique filename
$filename = 'product_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
$uploadPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    jsonResp(true, 'Image uploaded successfully', [
        'filename' => $filename,
        'url' => 'backend/uploads/products/' . $filename
    ]);
} else {
    jsonResp(false, 'Failed to move uploaded file');
}
