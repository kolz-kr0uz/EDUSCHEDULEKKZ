<?php
require_once('db.php');

header('Content-Type: application/json');

$result = $conn->query("SELECT id, name, full_name FROM faculty ORDER BY name");
$faculties = [];

while($row = $result->fetch_assoc()) {
    $faculties[] = $row;
}

echo json_encode($faculties);
$conn->close();
?>