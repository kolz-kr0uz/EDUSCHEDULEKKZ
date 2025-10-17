<?php
session_start();
require_once('db.php');

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$user = $_SESSION['user'];
$current_week = isset($_GET['week']) ? $_GET['week'] : date('W'); // Текущая неделя
$current_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Получаем даты для текущей недели
function getWeekDates($year, $week) {
    $dates = [];
    $first_day = new DateTime();
    $first_day->setISODate($year, $week);
    
    for ($i = 0; $i < 6; $i++) { // Только понедельник-суббота
        $date = clone $first_day;
        $date->modify("+$i days");
        $dates[] = $date->format('Y-m-d');
    }
    return $dates;
}

$week_dates = getWeekDates($current_year, $current_week);

// Получаем расписание в зависимости от роли
if ($user['role'] == 'student') {
    // Для ученика - расписание его класса
    $schedule_data = [];
    foreach ($week_dates as $date) {
        $stmt = $conn->prepare("
            SELECT s.*, u.fullname as teacher_name 
            FROM schedule s 
            LEFT JOIN users u ON s.teacher_id = u.id 
            WHERE s.date = ? AND s.class_info = ? 
            ORDER BY s.lesson_number
        ");
        $stmt->bind_param('ss', $date, $user['class_info']);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule_data[$date] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} else {
    // Для учителя - уроки которые он ведет
    $schedule_data = [];
    foreach ($week_dates as $date) {
        $stmt = $conn->prepare("
            SELECT s.*, u.fullname as teacher_name 
            FROM schedule s 
            LEFT JOIN users u ON s.teacher_id = u.id 
            WHERE s.date = ? AND s.teacher_id = ? 
            ORDER BY s.lesson_number
        ");
        $stmt->bind_param('si', $date, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule_data[$date] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Функции для форматирования
function getRussianDayOfWeek($date) {
    $days = [
        'Monday' => 'Понедельник', 
        'Tuesday' => 'Вторник',
        'Wednesday' => 'Среда',
        'Thursday' => 'Четверг',
        'Friday' => 'Пятница',
        'Saturday' => 'Суббота'
    ];
    $englishDay = date('l', strtotime($date));
    return $days[$englishDay];
}

function getShortDayName($date) {
    $days = [
        'Monday' => 'Пн', 
        'Tuesday' => 'Вт',
        'Wednesday' => 'Ср',
        'Thursday' => 'Чт',
        'Friday' => 'Пт',
        'Saturday' => 'Сб'
    ];
    $englishDay = date('l', strtotime($date));
    return $days[$englishDay];
}

function getLessonTime($number) {
    $times = [
        1 => '08:30-09:15',
        2 => '09:20-10:05',
        3 => '10:10-10:55',
        4 => '11:00-11:45',
        5 => '12:25-13:10',
        6 => '13:15-14:00',
        7 => '14:05-14:50',
        8 => '14:55-15:40'
    ];
    return $times[$number] ?? '';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Моё расписание</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #3498db;
            --dark-blue: #2980b9;
            --gradient-primary: linear-gradient(135deg, #3498db, #2c3e50);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        .navbar {
            background: var(--gradient-primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .user-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .user-header {
            background: var(--gradient-primary);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
        }
        
        .schedule-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .schedule-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .day-column {
            min-height: 600px;
            border-right: 1px solid #dee2e6;
        }
        
        .day-column:last-child {
            border-right: none;
        }
        
        .day-header {
            background: var(--primary-blue);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
            height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .today .day-header {
            background: var(--dark-blue);
        }
        
        .lesson-time-header {
            background: #e9ecef;
            height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }
        
        .lesson-time-cell {
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
        }
        
        .lesson-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 10px;
            margin: 4px;
            background: white;
            transition: all 0.3s ease;
            height: 92px;
            overflow: hidden;
        }
        
        .lesson-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .lesson-time {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 600;
        }
        
        .lesson-subject {
            font-weight: 600;
            color: #2c3e50;
            margin: 2px 0;
            font-size: 0.85rem;
            line-height: 1.2;
        }
        
        .lesson-info {
            font-size: 0.7rem;
            color: #6c757d;
            line-height: 1.2;
        }
        
        .empty-lesson {
            border: 2px dashed #dee2e6;
            background: transparent;
            color: #6c757d;
            text-align: center;
            padding: 10px;
            height: 92px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
            border-radius: 8px;
        }
        
        .week-navigation {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .role-badge {
            background: var(--primary-blue);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .schedule-grid {
            display: flex;
            min-height: 680px;
        }
        
        .time-column {
            flex: 0 0 80px;
            background: #f8f9fa;
        }
        
        .days-container {
            flex: 1;
            display: flex;
        }
        
        .day-cell {
            flex: 1;
            border-right: 1px solid #dee2e6;
        }
        
        .day-cell:last-child {
            border-right: none;
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-journal-check me-2"></i>EduSchedule
            </span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><?= $user['fullname'] ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Информация о пользователе -->
        <div class="user-card mb-4">
            <div class="user-header">
                <div class="user-avatar">
                    <i class="bi bi-<?= $user['role'] == 'student' ? 'person' : 'person-badge' ?>"></i>
                </div>
                <h3><?= $user['fullname'] ?></h3>
                <div class="role-badge d-inline-block mt-2">
                    <?= $user['role'] == 'student' ? 'Ученик' : 'Учитель' ?>
                    <?php if ($user['role'] == 'student'): ?>
                        • <?= $user['class_info'] ?> класс
                    <?php else: ?>
                        • <?= $user['subject'] ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Навигация по неделям -->
        <div class="week-navigation">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week"></i>
                        Расписание на неделю
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <a href="?week=<?= $current_week - 1 ?>&year=<?= $current_year ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-chevron-left"></i> Предыдущая
                        </a>
                        <span class="btn btn-primary btn-sm">
                            <?= date('d.m.Y', strtotime($week_dates[0])) ?> - <?= date('d.m.Y', strtotime($week_dates[5])) ?>
                        </span>
                        <a href="?week=<?= $current_week + 1 ?>&year=<?= $current_year ?>" class="btn btn-outline-primary btn-sm">
                            Следующая <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Расписание -->
        <div class="schedule-container">
            <div class="schedule-header">
                <h6 class="mb-0 text-center">
                    <?php if ($user['role'] == 'student'): ?>
                        Расписание для <?= $user['class_info'] ?> класса
                    <?php else: ?>
                        Мои уроки (<?= $user['subject'] ?>)
                    <?php endif; ?>
                </h6>
            </div>
            
            <div class="schedule-grid">
                <!-- Колонка с временами -->
                <div class="time-column">
                    <div class="lesson-time-header">
                        <small>Урок</small>
                        <small>Время</small>
                    </div>
                    <?php for($i = 1; $i <= 8; $i++): ?>
                        <div class="lesson-time-cell">
                            <small class="fw-bold"><?= $i ?></small>
                            <small><?= getLessonTime($i) ?></small>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <!-- Дни недели -->
                <div class="days-container">
                    <?php foreach($week_dates as $date): ?>
                        <?php 
                        $is_today = $date == date('Y-m-d');
                        $day_schedule = $schedule_data[$date] ?? [];
                        ?>
                        <div class="day-cell <?= $is_today ? 'today' : '' ?>">
                            <div class="day-header">
                                <div><?= getShortDayName($date) ?></div>
                                <div><?= date('d.m', strtotime($date)) ?></div>
                            </div>
                            
                            <?php for($lesson_num = 1; $lesson_num <= 8; $lesson_num++): ?>
                                <?php
                                $lesson = null;
                                foreach($day_schedule as $l) {
                                    if ($l['lesson_number'] == $lesson_num) {
                                        $lesson = $l;
                                        break;
                                    }
                                }
                                ?>
                                
                                <div class="p-1" style="height: 100px;">
                                    <?php if ($lesson): ?>
                                        <div class="lesson-card">
                                            <div class="lesson-subject"><?= $lesson['subject'] ?></div>
                                            <?php if ($user['role'] == 'student'): ?>
                                                <div class="lesson-info"><?= $lesson['teacher_name'] ?></div>
                                            <?php else: ?>
                                                <div class="lesson-info"><?= $lesson['class_info'] ?> кл.</div>
                                            <?php endif; ?>
                                            <div class="lesson-info">Каб. <?= $lesson['classroom'] ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-lesson">
                                            <small>—</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">
                            <?= array_sum(array_map('count', $schedule_data)) ?>
                        </h3>
                        <p class="text-muted mb-0">Уроков на неделю</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">
                            <?= count(array_filter($schedule_data[date('Y-m-d')] ?? [])) ?>
                        </h3>
                        <p class="text-muted mb-0">Уроков сегодня</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>