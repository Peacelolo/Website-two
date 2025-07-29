<?php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "your_db_user", "your_db_pass", "herdtrace_db");

if ($mysqli->connect_error) {
  echo json_encode(["error" => "Database connection failed."]);
  exit;
}

$animalType = $_POST['animalType'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$tagNumber = $_POST['tagNumber'] ?? '';

if (!$animalType || !$age || !$gender || !$tagNumber) {
  echo json_encode(["error" => "All fields are required."]);
  exit;
}

// Prevent SQL injection with prepared statements
$stmt = $mysqli->prepare("INSERT INTO animal_tracking (animal_type, age, gender, tag_number) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $animalType, $age, $gender, $tagNumber);

if ($stmt->execute()) {
  echo json_encode(["success" => "Animal registered successfully."]);
} else {
  echo json_encode(["error" => "Failed to register animal."]);
}
$stmt->close();
$mysqli->close();
?>
