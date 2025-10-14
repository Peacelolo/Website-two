<?php
session_start();
header('Content-Type: application/json');

// Debug mode (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to herdtrace_db
require_once __DIR__ . '/db_config.php';
$mysqli = get_db_connection();
if (!$mysqli) {
    exit;
}

// Get logged-in user's id from session (login sets user_id)
$user_id = $_SESSION['user_id'] ?? '';
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validate required fields
if (!$user_id || !$currentPassword || !$newPassword || !$confirmPassword) {
    echo json_encode(["error" => "All fields are required."]);
    exit;
}

// Password strength check (same as your frontend)


        $strongPasswordPattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-={}\[\];:\'"\\|,.<>\/\?]).{8,}$/';
if (!preg_match($strongPasswordPattern, $newPassword)) {
    echo json_encode(["error" => "Password must be at least 8 characters and include a letter, a number, and a special character."]);
    exit;
}

// Check if new passwords match
if ($newPassword !== $confirmPassword) {
    echo json_encode(["error" => "New passwords do not match."]);
    exit;
}

    $stmt = $mysqli->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "User not found."]);
    exit;
}

$row = $result->fetch_assoc();
if (!password_verify($currentPassword, $row['password'])) {
    echo json_encode(["error" => "Current password is incorrect."]);
    exit;
}

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $mysqli->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $updateStmt->bind_param("si", $hashed, $user_id);

if ($updateStmt->execute()) {
    echo json_encode(["success" => "Password updated successfully!"]);
} else {
    echo json_encode(["error" => "Failed to update password: " . $updateStmt->error]);
}

$updateStmt->close();
$mysqli->close();
?>
