<?php
session_start();
require_once('db.php');

$login = trim($_POST['login']);
$pass = trim($_POST['password']);

if(empty($login) || empty($pass)) {
    $_SESSION['msg'] = 'Заполните все поля';
    header('Location: login.html');
    exit;
}

// Админка
if(($login == "admin") && ($pass == "admin")) {
    $_SESSION['msg'] = 'Успешный вход как администратор';
    $_SESSION['user'] = [
        'id' => 0,
        'login' => 'admin',
        'fullname' => 'Администратор Системы',
        'email' => 'admin@school.ru',
        'role' => 'admin'
    ];
    $_SESSION['admin'] = true;
    header('Location: admin.php');
    exit;
}

$sql = "SELECT * FROM users WHERE login = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $login, $login);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Проверка пароля с помощью password_verify (так как пароли хешированы)
    if (password_verify($pass, $user['password'])) {
        $_SESSION['msg'] = 'Успешный вход'; 
        $_SESSION['user'] = [
            'id' => $user['id'],
            'login' => $user['login'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        // Добавляем дополнительную информацию в сессию в зависимости от роли
        if ($user['role'] == 'student') {
            $_SESSION['user']['class_info'] = $user['class_info'];
        } else {
            $_SESSION['user']['subject'] = $user['subject'];
        }
        
        // Перенаправляем на authorized.php
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