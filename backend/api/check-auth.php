<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

if (isset($_SESSION['user_id']) && $_SESSION['logged_in']) {
    $response = [
        'authenticated' => true,
        'user' => [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ]
    ];
} else {
    $response = ['authenticated' => false];
}

echo json_encode($response);
