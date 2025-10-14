<?php
session_start();
header('Content-Type: application/json');

// Production: keep JSON responses clean (log errors server-side, do not print)
error_reporting(E_ALL);
// Temporarily enable display_errors to help debug the 500. Remove or set to 0 in production.
ini_set('display_errors', 1);

// Database connection
require_once __DIR__ . '/db_config.php';
$mysqli = get_db_connection();
if (!$mysqli) {
    // get_db_connection already attempted to output a JSON error; ensure a proper 500 response
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed. Check server logs or db_config.php."]);
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
// Prepare statement and check for errors
$stmt = $mysqli->prepare("SELECT user_id, password, first_name FROM users WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to prepare statement: " . $mysqli->error]);
    $mysqli->close();
    exit;
}
$stmt->bind_param("s", $email);
$ok = $stmt->execute();
if ($ok === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to execute statement: " . $stmt->error]);
    $stmt->close();
    $mysqli->close();
    exit;
}
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