<?php
session_start();
header('Content-Type: application/json');

// Production: keep JSON responses clean (log errors server-side, do not print)
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

// Verify password (support legacy plain-text, migrate to hash)
$valid = false;
if (password_verify($password, $hashedPassword)) {
    $valid = true;
} elseif (hash_equals((string)$hashedPassword, (string)$password)) {
    // Migrate legacy plain-text password to hashed
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    if ($update = $mysqli->prepare("UPDATE users SET password = ? WHERE user_id = ?")) {
        $update->bind_param("si", $newHash, $user_id);
        $update->execute();
        $update->close();
    }
    $valid = true;
}

if ($valid) {
    // Protect against session fixation
    session_regenerate_id(true);
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