<?php
$servername = "MySQL-8.2";
$username = "root";
$password = "";
$dbname = "EduSchedule";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}
?>
