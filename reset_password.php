<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Connect to herdtrace_db
$mysqli = new mysqli("localhost", "root", "@Lolo123", "herdtrace_db");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

$token = $_POST['token'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

if (!$token || !$newPassword) {
    echo json_encode(["error" => "Missing token or password."]);
    exit;
}

// Find token
$stmt = $mysqli->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Invalid or expired token."]);
    exit;
}

$row = $result->fetch_assoc();
if (strtotime($row['expires_at']) < time()) {
    echo json_encode(["error" => "Token has expired."]);
    exit;
}

// Update password
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed, $row['email']);
$stmt->execute();

// Delete token
$stmt = $mysqli->prepare("DELETE FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

echo json_encode(["success" => "Password reset successful."]);
$mysqli->close();
?>
