<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['username']) || !isset($data['password'])) {
        jsonResponse(false, 'Username and password required');
    }

    $username = sanitize($data['username']);
    $password = $data['password'];

    $result = loginUser($username, $password);

    if ($result['success']) {
        jsonResponse(true, 'Login successful', $result['user']);
    } else {
        jsonResponse(false, $result['message']);
    }
} else {
    jsonResponse(false, 'Invalid request method');
}
