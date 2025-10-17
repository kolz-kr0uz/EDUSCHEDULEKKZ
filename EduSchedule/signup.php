<?php 
session_start();
require_once('db.php'); 

// Получаем данные из формы
$role = trim($_POST['role']);
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$login = trim($_POST['login']);
$pass = trim($_POST['password']);

// Формируем class_info для учеников
if ($role == 'student') {
    $class = trim($_POST['class']);
    $class_letter = trim($_POST['class_letter']);
    $class_info = $class . $class_letter;
    $subject = NULL;
} else {
    $subject = trim($_POST['subject']);
    $class_info = NULL;
}

// Проверка заполнения обязательных полей
if (empty($login) || empty($pass) || empty($fullname) || empty($email) || empty($role)) {
    $_SESSION['msg'] = 'Заполните все обязательные поля';
    header('location: signup.php');
    exit;
}

// Проверка email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['msg'] = 'Некорректный формат email';
    header('location: signup.php');
    exit;
}

// Проверка занятости логина
$stmt = $conn->prepare("SELECT id FROM users WHERE login = ?"); 
$stmt->bind_param('s', $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['msg'] = 'Данный логин занят';
    header('location: signup.php');
    exit;
}

// Проверка занятости email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?"); 
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['msg'] = 'Данный email уже зарегистрирован';
    header('location: signup.php');
    exit;
}

// Хеширование пароля
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

// Вставка данных в базу
$stmt = $conn->prepare("INSERT INTO users (role, fullname, email, login, password, class_info, subject) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssssss', $role, $fullname, $email, $login, $hashed_password, $class_info, $subject);

if ($stmt->execute()) {
    // Успешная регистрация - показываем страницу с уведомлением
    $success_message = "Вы успешно зарегистрированы! Через 10 секунд вы будете перенаправлены на страницу входа.";
} else {
    $success_message = "Ошибка регистрации: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация успешна</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #3498db;
            --dark-blue: #2980b9;
            --light-blue: #5dade2;
            --gradient-primary: linear-gradient(135deg, #3498db, #2c3e50);
            --gradient-secondary: linear-gradient(135deg, #5dade2, #3498db);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .success-container {
            background: white;
            color: #333;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: #2ecc71;
            margin-bottom: 20px;
        }
        
        .countdown {
            font-size: 1.2rem;
            margin-top: 20px;
            color: var(--primary-blue);
            font-weight: 600;
        }
        
        .btn-primary {
            background: var(--gradient-secondary);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="bi bi-check-circle"></i>
        </div>
        <h2>Регистрация завершена!</h2>
        <p><?php echo $success_message; ?></p>
        <div class="countdown" id="countdown">Перенаправление через: 10 секунд</div>
        <a href="login.html" class="btn btn-primary">Перейти к входу сейчас</a>
    </div>

    <script>
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(function() {
            seconds--;
            countdownElement.textContent = `Перенаправление через: ${seconds} секунд`;
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'login.html';
            }
        }, 1000);
    </script>
</body>
</html>