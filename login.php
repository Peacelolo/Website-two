<?php
header('Content-Type: application/json');

// Debug mode (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$mysqli = new mysqli("localhost", "root", "@Lolo123", "herdtrace_db");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

// Get POST data and sanitize
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["error" => "Email and password are required."]);
    exit;
}

// Find user by email
$stmt = $mysqli->prepare("SELECT user_id, password, first_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(["error" => "Invalid email or password."]);
    $stmt->close();
    exit;
}

$stmt->bind_result($user_id, $hashedPassword, $first_name);
$stmt->fetch();

// Verify password
if (password_verify($password, $hashedPassword)) {
    $_SESSION['user_id'] = $user_id;
    echo json_encode([
        "success" => "Welcome back, $first_name!",
        "user_id" => $user_id
    ]);
} else {
    echo json_encode(["error" => "Invalid email or password."]);
}

$stmt->close();
$mysqli->close();
?>