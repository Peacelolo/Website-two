<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "@Lolo123", "herdtrace_db");

if ($mysqli->connect_error) {
  echo json_encode(["error" => "Database connection failed."]);
  exit;
}

$result = $mysqli->query("SELECT tag_number, latitude, longitude, animal_type FROM animal_tracking");

$animals = [];
while ($row = $result->fetch_assoc()) {
  $animals[] = [
    "id" => $row["tag_number"],
    "lat" => floatval($row["latitude"]),
    "lng" => floatval($row["longitude"]),
    "type" => $row["animal_type"]
  ];
}

echo json_encode($animals);
?>
