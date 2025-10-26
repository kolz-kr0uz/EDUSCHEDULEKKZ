<?php
session_start();
require_once('db.php');

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$user = $_SESSION['user'];
$current_week = isset($_GET['week']) ? $_GET['week'] : date('W');
$current_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Получаем даты для текущей недели
function getWeekDates($year, $week) {
    $dates = [];
    $first_day = new DateTime();
    $first_day->setISODate($year, $week);
    
    for ($i = 0; $i < 6; $i++) {
        $date = clone $first_day;
        $date->modify("+$i days");
        $dates[] = $date->format('Y-m-d');
    }
    return $dates;
}

// Получаем расписание только если пользователь подтвержден или это студент
$schedule_data = [];
$show_schedule = true;

if ($user['role'] == 'teacher' && !$user['is_approved']) {
    $show_schedule = false;
} else {
    if ($user['role'] == 'student') {
        // Для студента - расписание его группы
        foreach (getWeekDates($current_year, $current_week) as $date) {
            $stmt = $conn->prepare("
                SELECT s.*, u.fullname as teacher_name, f.name as faculty_name 
                FROM schedule s 
                LEFT JOIN users u ON s.teacher_id = u.id 
                LEFT JOIN faculty f ON s.faculty_id = f.id 
                WHERE s.date = ? AND s.faculty_id = ? 
                ORDER BY s.lesson_number
            ");
            $stmt->bind_param('si', $date, $user['faculty_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedule_data[$date] = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    } else {
        // Для преподавателя - уроки которые он ведет
        foreach (getWeekDates($current_year, $current_week) as $date) {
            $stmt = $conn->prepare("
                SELECT s.*, u.fullname as teacher_name, f.name as faculty_name 
                FROM schedule s 
                LEFT JOIN users u ON s.teacher_id = u.id 
                LEFT JOIN faculty f ON s.faculty_id = f.id 
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
        8 => '14:55-15:40',
        9 => '16:05-16:50',
        10 => '16:55-17:40',
        11 => '17:45-18:30',
        12 => '18:35-19:20'
    ];
    return $times[$number] ?? '';
}

$week_dates = getWeekDates($current_year, $current_week);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Моё расписание - EduSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            overflow-x: auto;
        }
        
        .schedule-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .day-header {
            background: var(--primary-blue);
            color: black;
            padding: 15px;
            text-align: center;
            font-weight: 600;
            min-width: 150px;
        }
        
        .today .day-header {
            background: var(--dark-blue);
        }
        
        .lesson-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px;
            margin: 2px;
            background: #f8f9fa;
            font-size: 0.8rem;
            min-height: 70px;
        }
        
        .lesson-time {
            font-size: 0.7rem;
            color: #6c757d;
            font-weight: 600;
        }
        
        .lesson-subject {
            font-weight: 600;
            color: #2c3e50;
            margin: 3px 0;
            font-size: 0.85rem;
        }
        
        .lesson-info {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .empty-lesson {
            border: 2px dashed #dee2e6;
            background: transparent;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
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
        
        .pending-badge {
            background: #dc3545;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .time-column {
            background: #f8f9fa;
            min-width: 100px;
        }
        
        .break-row {
            background: #fff3cd;
            font-size: 0.7rem;
            text-align: center;
            padding: 2px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .schedule-table {
            min-width: 1200px;
        }
        
        .subgroup-badge {
            font-size: 0.6rem;
            padding: 1px 4px;
            margin-top: 2px;
        }
        
        .pending-alert {
            border-left: 4px solid #dc3545;
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
                <div class="d-inline-block mt-2">
                    <?php if ($user['role'] == 'teacher' && !$user['is_approved']): ?>
                        <span class="pending-badge">
                            <i class="bi bi-clock"></i> Ожидает подтверждения
                        </span>
                    <?php else: ?>
                        <span class="role-badge">
                            <?= $user['role'] == 'student' ? 'Студент' : 'Преподаватель' ?>
                            <?php if ($user['role'] == 'student' && isset($user['faculty_name'])): ?>
                                • <?= $user['faculty_name'] ?><?= $user['group_letter'] ? ' (' . $user['group_letter'] . ')' : '' ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($user['role'] == 'student' && isset($user['faculty_full_name'])): ?>
                    <p class="mt-2 mb-0"><?= $user['faculty_full_name'] ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Уведомление о неподтвержденном аккаунте -->
        <?php if ($user['role'] == 'teacher' && !$user['is_approved']): ?>
            <div class="alert alert-danger pending-alert">
                <h5><i class="bi bi-exclamation-triangle"></i> Аккаунт ожидает подтверждения</h5>
                <p class="mb-0">
                    Ваш аккаунт еще не подтвержден администратором. После подтверждения вы сможете просматривать свое расписание.
                    Обратитесь к администратору для активации вашего аккаунта.
                </p>
            </div>
        <?php else: ?>
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
                            Расписание для группы <?= $user['faculty_name'] ?><?= $user['group_letter'] ? ' (' . $user['group_letter'] . ')' : '' ?>
                        <?php else: ?>
                            Мои уроки
                        <?php endif; ?>
                    </h6>
                </div>
                
                <div class="schedule-table">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th class="time-column text-center">Урок</th>
                                <?php foreach($week_dates as $date): ?>
                                    <th class="day-header <?= $date == date('Y-m-d') ? 'today' : '' ?>">
                                        <?= getShortDayName($date) ?><br>
                                        <?= date('d.m', strtotime($date)) ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($lesson_num = 1; $lesson_num <= 12; $lesson_num++): ?>
                                <?php if ($lesson_num == 5): ?>
                                    <tr class="break-row">
                                        <td colspan="7">Обед 11:45-12:25</td>
                                    </tr>
                                <?php elseif ($lesson_num == 9): ?>
                                    <tr class="break-row">
                                        <td colspan="7">Обед 15:40-16:05</td>
                                    </tr>
                                <?php endif; ?>
                                
                                <tr>
                                    <td class="time-column text-center">
                                        <small class="fw-bold"><?= $lesson_num ?></small><br>
                                        <small class="lesson-time"><?= getLessonTime($lesson_num) ?></small>
                                    </td>
                                    
                                    <?php foreach($week_dates as $date): ?>
                                        <td style="min-width: 150px; vertical-align: top;">
                                            <?php
                                            $lessons = array_filter($schedule_data[$date] ?? [], function($l) use ($lesson_num) {
                                                return $l['lesson_number'] == $lesson_num;
                                            });
                                            ?>
                                            
                                            <?php if (count($lessons) > 0): ?>
                                                <?php foreach($lessons as $lesson): ?>
                                                    <div class="lesson-card">
                                                        <div class="lesson-subject"><?= $lesson['subject'] ?></div>
                                                        <?php if ($user['role'] == 'student'): ?>
                                                            <div class="lesson-info"><?= $lesson['teacher_name'] ?></div>
                                                        <?php else: ?>
                                                            <div class="lesson-info"><?= $lesson['faculty_name'] ?></div>
                                                        <?php endif; ?>
                                                        <div class="lesson-info">Каб. <?= $lesson['classroom'] ?></div>
                                                        <?php if ($lesson['subgroup'] > 0): ?>
                                                            <span class="badge bg-secondary subgroup-badge">Подгр. <?= $lesson['subgroup'] ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="empty-lesson">
                                                    <small>—</small>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
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
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>