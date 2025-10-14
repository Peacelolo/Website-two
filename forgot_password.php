<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Connect to herdtrace_db
require_once __DIR__ . '/db_config.php';
$mysqli = get_db_connection();
if (!$mysqli) {
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!$email) {
    echo json_encode(["error" => "Email is required."]);
    exit;
}

// Check if email exists
$stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "No account found with that email."]);
    exit;
}

// Generate token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Store token
$stmt = $mysqli->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expires);
$stmt->execute();

// Send email
$resetLink = "http://localhost/HerdTrace/reset_password.html?token=$token";
$subject = "HerdTrace Password Reset";
$message = "Hi there,\n\nClick the link below to reset your password:\n$resetLink\n\nThis link will expire in 1 hour.";
$headers = "From: no-reply@herdtrace.co.za";

mail($email, $subject, $message, $headers);

echo json_encode(["success" => "Reset link sent to your email."]);
$mysqli->close();
?>
