<?php
$conn = new mysqli('localhost', 'raymond', '111901', 'cafe_rencontre');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!<br>";

    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "Users table exists!<br>";

        $result = $conn->query("SELECT * FROM users WHERE username = 'admin'");
        if ($result->num_rows > 0) {
            echo "Admin user found!<br>";
            $user = $result->fetch_assoc();
            echo "Password hash: " . $user['password_hash'] . "<br>";
        } else {
            echo "Admin user NOT found!<br>";
        }
    } else {
        echo "Users table NOT found!<br>";
    }
}

$conn->close();
