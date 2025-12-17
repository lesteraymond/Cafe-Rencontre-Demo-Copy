<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

checkAuth();
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) {
        jsonResponse(false, 'No file uploaded');
    }

    $file = $_FILES['file'];
    $result = uploadFile($file);

    if ($result['success']) {
        jsonResponse(true, 'File uploaded successfully', [
            'filename' => $result['filename'],
            'url' => '../uploads/' . $result['filename']
        ]);
    } else {
        jsonResponse(false, $result['message']);
    }
} else {
    jsonResponse(false, 'Invalid request method');
}
