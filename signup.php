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
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';

// Validate required fields
if (!$firstName || !$lastName || !$email || !$mobile || !$password) {
    echo json_encode(["error" => "All fields are required."]);
    exit;
}

// Check if email already exists
$check = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(["error" => "Email already registered."]);
    $check->close();
    exit;
}
$check->close();

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user

$stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, mobile, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $firstName, $lastName, $email, $mobile, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(["success" => "Account created successfully!"]);
} else {
    echo json_encode(["error" => "Failed to create account: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>