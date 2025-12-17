<?php
require_once 'session.php';

function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($success, $message = '', $data = [])
{
    $response = ['success' => $success];

    if ($message) {
        $response['message'] = $message;
    }

    if (!empty($data)) {
        $response['data'] = $data;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

function generateOrderNumber()
{
    return 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Validate email
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function calculatePercentage($bottles, $available, $capacity = 2000)
{
    $totalCapacity = $bottles * $capacity;
    if ($totalCapacity <= 0) return 0;

    $percentage = ($available / $totalCapacity) * 100;
    return min(100, round($percentage, 2));
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function uploadFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }

    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = '../uploads/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Failed to upload file'];
}
