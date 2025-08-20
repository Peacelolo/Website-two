<?php
header('Content-Type: application/json');

// Database connection
$mysqli = new mysqli("localhost", "root", "@Lolo123", "herdtrace_db");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

// Use user_id from session
$user_id = $_SESSION['user_id'];

// Get POST data
$animal_type = $_POST['animal_type'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$tag_number = $_POST['tag_number'] ?? '';

if (!$animal_type || !$age || !$gender || !$tag_number) {
    echo json_encode(["error" => "All required fields must be filled."]);
    exit;
}

// Check for duplicate tag_number
$check = $mysqli->prepare("SELECT animal_id FROM register_animal WHERE tag_number = ?");
$check->bind_param("s", $tag_number);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(["error" => "Tag number already exists."]);
    exit;
}
$check->close();

// Get the latest counts for this user
$countQuery = $mysqli->prepare("
    SELECT count_cattle, count_sheep, count_goat
    FROM register_animal
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$countQuery->bind_param("i", $user_id);
$countQuery->execute();
$countQuery->bind_result($currCattle, $currSheep, $currGoat);

if (!$countQuery->fetch()) {
    // No previous record — start from zero
    $currCattle = 0;
    $currSheep = 0;
    $currGoat = 0;
}
$countQuery->close();

// Increment the count for the selected animal type
switch (strtolower($animal_type)) {
    case 'cattle':
        $currCattle++;
        break;
    case 'sheep':
        $currSheep++;
        break;
    case 'goat':
        $currGoat++;
        break;
}

// Insert the new animal record
$stmt = $mysqli->prepare("
    INSERT INTO register_animal 
    (user_id, count_cattle, count_sheep, count_goat, animal_type, age, gender, tag_number, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiiissss", $user_id, $currCattle, $currSheep, $currGoat, $animal_type, $age, $gender, $tag_number);

if ($stmt->execute()) {
    echo json_encode([
        "success" => "Animal registered successfully.",
        "counts" => [
            "count_cattle" => $currCattle,
            "count_sheep" => $currSheep,
            "count_goat" => $currGoat
        ]
    ]);
} else {
    echo json_encode(["error" => "Failed to register animal."]);
}

$stmt->close();
$mysqli->close();
?>
