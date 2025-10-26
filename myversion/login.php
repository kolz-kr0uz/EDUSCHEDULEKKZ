<?php
session_start();
require_once('db.php');

$login = trim($_POST['login']);
$pass = trim($_POST['password']);

// Проверка заполнения полей
if(empty($login) || empty($pass)) {
    $_SESSION['msg'] = 'Заполните все поля';
    header('Location: login.html');
    exit;
}

// Проверка на админа
if(($login == "admin") && ($pass == "admin")) {
    $_SESSION['msg'] = 'Успешный вход как администратор';
    $_SESSION['user'] = [
        'id' => 0,
        'login' => 'admin',
        'fullname' => 'Администратор Системы',
        'email' => 'admin@college.ru',
        'role' => 'admin'
    ];
    $_SESSION['admin'] = true;
    header('Location: admin.php');
    exit;
}

// Используем подготовленные запросы для безопасности
$sql = "SELECT u.*, f.name as faculty_name, f.full_name as faculty_full_name 
        FROM users u 
        LEFT JOIN faculty f ON u.faculty_id = f.id 
        WHERE u.login = ? OR u.email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $login, $login);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Проверяем пароль
    if (password_verify($pass, $user['password'])) {
        $_SESSION['msg'] = 'Успешный вход'; 
        $_SESSION['user'] = [
            'id' => $user['id'],
            'login' => $user['login'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'role' => $user['role'],
            'faculty_id' => $user['faculty_id'],
            'group_letter' => $user['group_letter'],
            'faculty_name' => $user['faculty_name'],
            'faculty_full_name' => $user['faculty_full_name'],
            'is_approved' => $user['is_approved']
        ];
        
        // Перенаправляем в профиль
        header('Location: authorized.php');
        exit;
        
    } else {
        $_SESSION['msg'] = 'Неверный логин или пароль'; 
        header('Location: login.html');
        exit;
    }
} else {
    $_SESSION['msg'] = 'Неверный логин или пароль'; 
    header('Location: login.html');
    exit;
}

$stmt->close();
$conn->close();
?>