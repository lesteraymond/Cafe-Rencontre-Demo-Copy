<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

checkAuth();

$result = logoutUser();
jsonResponse($result['success'], 'Logged out successfully');
