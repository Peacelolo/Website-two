<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$mysqli = null;
require_once __DIR__ . '/db_config.php';
$mysqli = get_db_connection();
if (!$mysqli) {
    http_response_code(500);
    // get_db_connection already echoed a JSON error
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please log in."]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $sql = "SELECT 
                first_name,
                last_name,
                email,
                mobile,
                business_name,
                address_line1,
                address_line2,
                postal_code,
                cattle_count,
                sheep_count,
                goat_count,
                geo_latitude,
                geo_longitude,
                geo_radius
            FROM users
            WHERE user_id = ?";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to prepare statement."]);
        exit;
    }

    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        $stmt->close();
        http_response_code(500);
        echo json_encode(["error" => "Failed to execute statement."]);
        exit;
    }

    $stmt->bind_result(
        $firstName,
        $lastName,
        $email,
        $mobile,
        $businessName,
        $addressLine1,
        $addressLine2,
        $postalCode,
        $cattleCount,
        $sheepCount,
        $goatCount,
        $geoLat,
        $geoLng,
        $geoRadius
    );

    if (!$stmt->fetch()) {
        $stmt->close();
        http_response_code(404);
        echo json_encode(["error" => "User not found."]);
        exit;
    }
    $stmt->close();

    $data = [
        'first_name'    => $firstName ?? '',
        'last_name'     => $lastName ?? '',
        'email'         => $email ?? '',
        'mobile'        => $mobile ?? '',
        'business_name' => $businessName ?? '',
        'address_line1' => $addressLine1 ?? '',
        'address_line2' => $addressLine2 ?? '',
        'postal_code'   => $postalCode ?? '',
        'cattle_count'  => ($cattleCount !== null ? (int)$cattleCount : null),
        'sheep_count'   => ($sheepCount  !== null ? (int)$sheepCount  : null),
        'goat_count'    => ($goatCount   !== null ? (int)$goatCount   : null),
        'geo_latitude'  => ($geoLat !== null ? (float)$geoLat : null),
        'geo_longitude' => ($geoLng !== null ? (float)$geoLng : null),
        'geo_radius'    => ($geoRadius !== null ? (int)$geoRadius : null),
    ];

    echo json_encode($data);
    exit;
}

if ($method === 'POST') {
    $businessName = trim($_POST['businessName'] ?? '');
    $addressLine1 = trim($_POST['addressLine1'] ?? '');
    $addressLine2 = trim($_POST['addressLine2'] ?? '');
    $postalCode   = trim($_POST['postalCode'] ?? '');
    $cattleCount  = isset($_POST['cattleCount']) ? (int)$_POST['cattleCount'] : 0;
    $sheepCount   = isset($_POST['sheepCount']) ? (int)$_POST['sheepCount'] : 0;
    $goatCount    = isset($_POST['goatCount']) ? (int)$_POST['goatCount'] : 0;
    $geoLatitude  = isset($_POST['geoLatitude']) && $_POST['geoLatitude'] !== '' ? (float)$_POST['geoLatitude'] : null;
    $geoLongitude = isset($_POST['geoLongitude']) && $_POST['geoLongitude'] !== '' ? (float)$_POST['geoLongitude'] : null;
    $geoRadius    = isset($_POST['geoRadius']) && $_POST['geoRadius'] !== '' ? (int)$_POST['geoRadius'] : null;

    $stmt = $mysqli->prepare(
        "UPDATE users SET
            business_name = ?,
            address_line1 = ?,
            address_line2 = ?,
            postal_code = ?,
            cattle_count = ?,
            sheep_count = ?,
            goat_count = ?,
            geo_latitude = ?,
            geo_longitude = ?,
            geo_radius = ?
         WHERE user_id = ?"
    );

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to prepare update statement."]);
        exit;
    }

    $stmt->bind_param(
        'ssssiiiddii',
        $businessName,
        $addressLine1,
        $addressLine2,
        $postalCode,
        $cattleCount,
        $sheepCount,
        $goatCount,
        $geoLatitude,
        $geoLongitude,
        $geoRadius,
        $user_id
    );

    if (!$stmt->execute()) {
        $stmt->close();
        http_response_code(500);
        echo json_encode(["error" => "Failed to update profile."]);
        exit;
    }

    $stmt->close();
    echo json_encode(["success" => "Profile updated successfully."]);
    exit;
}

http_response_code(405);
header('Allow: GET, POST');
echo json_encode(["error" => "Method not allowed."]);
