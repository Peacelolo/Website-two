<?php
session_start();
header('Content-Type: application/json');

// Debug mode (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = new mysqli("localhost", "root", "@Lolo123", "herdtrace_db");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch profile data
    $stmt = $mysqli->prepare("SELECT first_name, last_name, email, mobile, business_name, address, cattle_count, sheep_count, goat_count, geo_latitude, geo_longitude, geo_radius FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        echo json_encode(["error" => "User not found."]);
        $stmt->close();
        exit;
    }
    $stmt->bind_result($first_name, $last_name, $email, $mobile, $business_name, $address, $cattle_count, $sheep_count, $goat_count, $geo_latitude, $geo_longitude, $geo_radius);
    $stmt->fetch();
    echo json_encode([
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "mobile" => $mobile,
        "business_name" => $business_name,
        "address" => $address,
        "cattle_count" => $cattle_count,
        "sheep_count" => $sheep_count,
        "goat_count" => $goat_count,
        "geo_latitude" => $geo_latitude,
        "geo_longitude" => $geo_longitude,
        "geo_radius" => $geo_radius
    ]);
    $stmt->close();
    $mysqli->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile data
    $business_name = trim($_POST['businessName'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $mobile = trim($_POST['contact'] ?? '');
    $cattle_count = intval($_POST['cattleCount'] ?? 0);
    $sheep_count = intval($_POST['sheepCount'] ?? 0);
    $goat_count = intval($_POST['goatCount'] ?? 0);
    $geo_latitude = floatval($_POST['geoLatitude'] ?? 0);
    $geo_longitude = floatval($_POST['geoLongitude'] ?? 0);
    $geo_radius = intval($_POST['geoRadius'] ?? 0);

    $stmt = $mysqli->prepare("UPDATE users SET business_name=?, address=?, mobile=?, cattle_count=?, sheep_count=?, goat_count=?, geo_latitude=?, geo_longitude=?, geo_radius=? WHERE user_id=?");
    $stmt->bind_param("sssiiidddi", $business_name, $address, $mobile, $cattle_count, $sheep_count, $goat_count, $geo_latitude, $geo_longitude, $geo_radius, $user_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => "Profile updated successfully."]);
    } else {
        echo json_encode(["error" => "Failed to update profile: " . $stmt->error]);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}

echo json_encode(["error" => "Invalid request method."]);
?>
