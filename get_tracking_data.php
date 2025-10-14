<?php
header('Content-Type: application/json');

$mysqli = null;
require_once __DIR__ . '/db_config.php';
$mysqli = get_db_connection();
if (!$mysqli) {
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
