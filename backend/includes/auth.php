<?php
require_once 'functions.php';
require_once __DIR__ . '/../config/database.php';

function checkAuth()
{
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        jsonResponse(false, 'Authentication required');
    }
    return true;
}

function loginUser($username, $password)
{
    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            $stmt->close();
            $conn->close();

            return [
                'success' => true,
                'user' => [
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ];
        }
    }

    $stmt->close();
    $conn->close();

    return ['success' => false, 'message' => 'Invalid username or password'];
}

function logoutUser()
{
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    return ['success' => true];
}
