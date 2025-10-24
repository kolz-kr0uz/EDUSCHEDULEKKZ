<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $faculty_id = $_POST['faculty_id'];
    $lesson_number = $_POST['lesson_number'];
    
    // Удаляем все подгруппы для этого урока
    $stmt = $conn->prepare("DELETE FROM schedule WHERE date = ? AND faculty_id = ? AND lesson_number = ?");
    $stmt->bind_param('sii', $date, $faculty_id, $lesson_number);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Урок успешно удален!";
    } else {
        $_SESSION['error'] = "Ошибка при удалении урока: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
    header("Location: admin.php?faculty_id=$faculty_id");
    exit;
}
?>