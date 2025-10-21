<?php
session_start();
require_once('db.php');

// Проверка авторизации админа
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.html');
    exit;
}

// Текущая неделя и выбранная группа
$current_week = isset($_GET['week']) ? $_GET['week'] : date('W');
$current_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selected_faculty = isset($_GET['faculty_id']) ? $_GET['faculty_id'] : null;

// Обработка подтверждения/отклонения аккаунтов учителей
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_teacher'])) {
        $teacher_id = $_POST['teacher_id'];
        $stmt = $conn->prepare("UPDATE users SET is_approved = TRUE WHERE id = ?");
        $stmt->bind_param('i', $teacher_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Аккаунт преподавателя успешно подтвержден!";
        } else {
            $_SESSION['error'] = "Ошибка при подтверждении аккаунта: " . $conn->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['reject_teacher'])) {
        $teacher_id = $_POST['teacher_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'");
        $stmt->bind_param('i', $teacher_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Аккаунт преподавателя успешно удален!";
        } else {
            $_SESSION['error'] = "Ошибка при удалении аккаунта: " . $conn->error;
        }
        $stmt->close();
    }
}

// Обработка добавления новой группы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
    $course = trim($_POST['course']);
    $abbreviation = trim($_POST['abbreviation']);
    $letter = trim($_POST['letter']);
    $full_name = trim($_POST['full_name']);
    
    $name = $course . $abbreviation . '-' . $letter;
    
    if (!empty($course) && !empty($abbreviation) && !empty($letter) && !empty($full_name)) {
        $stmt = $conn->prepare("INSERT INTO faculty (name, full_name) VALUES (?, ?)");
        $stmt->bind_param('ss', $name, $full_name);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Группа $name успешно добавлена!";
        } else {
            $_SESSION['error'] = "Ошибка при добавлении группы: " . $conn->error;
        }
        $stmt->close();
    }
}

// Обработка добавления/изменения расписания
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $date = $_POST['date'];
    $faculty_id = $_POST['faculty_id'];
    $lesson_number = $_POST['lesson_number'];
    $use_subgroups = isset($_POST['use_subgroups']) ? 1 : 0;
    
    // Время уроков по номеру (12 уроков)
    $lesson_times = [
        1 => ['08:30:00', '09:15:00'],
        2 => ['09:20:00', '10:05:00'],
        3 => ['10:10:00', '10:55:00'],
        4 => ['11:00:00', '11:45:00'],
        5 => ['12:25:00', '13:10:00'],
        6 => ['13:15:00', '14:00:00'],
        7 => ['14:05:00', '14:50:00'],
        8 => ['14:55:00', '15:40:00'],
        9 => ['16:05:00', '16:50:00'],
        10 => ['16:55:00', '17:40:00'],
        11 => ['17:45:00', '18:30:00'],
        12 => ['18:35:00', '19:20:00']
    ];
    
    $start_time = $lesson_times[$lesson_number][0];
    $end_time = $lesson_times[$lesson_number][1];
    
    // Удаляем старые записи для этого урока
    $delete_stmt = $conn->prepare("DELETE FROM schedule WHERE date = ? AND faculty_id = ? AND lesson_number = ?");
    $delete_stmt->bind_param('sii', $date, $faculty_id, $lesson_number);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    $errors = [];
    
    if ($use_subgroups) {
        // Обработка для подгрупп
        $subgroup1_subject = $_POST['subgroup1_subject'];
        $subgroup1_teacher = $_POST['subgroup1_teacher'];
        $subgroup1_classroom = $_POST['subgroup1_classroom'];
        
        $subgroup2_subject = $_POST['subgroup2_subject'];
        $subgroup2_teacher = $_POST['subgroup2_teacher'];
        $subgroup2_classroom = $_POST['subgroup2_classroom'];
        
        // Проверка подгруппы 1
        if (!empty($subgroup1_subject) && !empty($subgroup1_teacher) && !empty($subgroup1_classroom)) {
            $error = checkConflicts($date, $lesson_number, $subgroup1_classroom, $subgroup1_teacher, $conn);
            if ($error) $errors[] = "Подгруппа 1: " . $error;
        }
        
        // Проверка подгруппы 2
        if (!empty($subgroup2_subject) && !empty($subgroup2_teacher) && !empty($subgroup2_classroom)) {
            $error = checkConflicts($date, $lesson_number, $subgroup2_classroom, $subgroup2_teacher, $conn);
            if ($error) $errors[] = "Подгруппа 2: " . $error;
        }
        
        if (empty($errors)) {
            // Сохраняем подгруппу 1
            if (!empty($subgroup1_subject) && !empty($subgroup1_teacher) && !empty($subgroup1_classroom)) {
                saveLesson($date, $faculty_id, 1, $lesson_number, $start_time, $end_time, 
                          $subgroup1_subject, $subgroup1_teacher, $subgroup1_classroom, $conn);
            }
            
            // Сохраняем подгруппу 2
            if (!empty($subgroup2_subject) && !empty($subgroup2_teacher) && !empty($subgroup2_classroom)) {
                saveLesson($date, $faculty_id, 2, $lesson_number, $start_time, $end_time, 
                          $subgroup2_subject, $subgroup2_teacher, $subgroup2_classroom, $conn);
            }
            
            $_SESSION['success'] = "Расписание для подгрупп успешно сохранено!";
        }
    } else {
        // Обработка без подгрупп (subgroup = 0)
        $subject = $_POST['subject'];
        $teacher_id = $_POST['teacher_id'];
        $classroom = $_POST['classroom'];
        
        $error = checkConflicts($date, $lesson_number, $classroom, $teacher_id, $conn);
        if ($error) {
            $errors[] = $error;
        }
        
        if (empty($errors)) {
            saveLesson($date, $faculty_id, 0, $lesson_number, $start_time, $end_time, 
                      $subject, $teacher_id, $classroom, $conn);
            $_SESSION['success'] = "Урок успешно сохранен!";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Функция проверки конфликтов
function checkConflicts($date, $lesson_number, $classroom, $teacher_id, $conn) {
    // Проверка занятости кабинета
    $check_classroom = $conn->prepare("
        SELECT f.name, s.subgroup 
        FROM schedule s 
        JOIN faculty f ON s.faculty_id = f.id 
        WHERE s.date = ? AND s.lesson_number = ? AND s.classroom = ?
    ");
    $check_classroom->bind_param('sis', $date, $lesson_number, $classroom);
    $check_classroom->execute();
    $check_classroom->store_result();
    
    if ($check_classroom->num_rows > 0) {
        $check_classroom->bind_result($conflict_faculty, $conflict_subgroup);
        $check_classroom->fetch();
        $check_classroom->close();
        return "Кабинет $classroom уже занят группой $conflict_faculty" . ($conflict_subgroup > 0 ? " (подгруппа $conflict_subgroup)" : "");
    }
    $check_classroom->close();
    
    // Проверка занятости преподавателя
    $check_teacher = $conn->prepare("
        SELECT f.name, s.subgroup 
        FROM schedule s 
        JOIN faculty f ON s.faculty_id = f.id 
        WHERE s.date = ? AND s.lesson_number = ? AND s.teacher_id = ?
    ");
    $check_teacher->bind_param('sii', $date, $lesson_number, $teacher_id);
    $check_teacher->execute();
    $check_teacher->store_result();
    
    if ($check_teacher->num_rows > 0) {
        $check_teacher->bind_result($conflict_faculty, $conflict_subgroup);
        $check_teacher->fetch();
        $check_teacher->close();
        return "Преподаватель уже занят с группой $conflict_faculty" . ($conflict_subgroup > 0 ? " (подгруппа $conflict_subgroup)" : "");
    }
    $check_teacher->close();
    
    return null;
}

// Функция сохранения урока
function saveLesson($date, $faculty_id, $subgroup, $lesson_number, $start_time, $end_time, $subject, $teacher_id, $classroom, $conn) {
    $insert_stmt = $conn->prepare("
        INSERT INTO schedule (date, faculty_id, subgroup, lesson_number, start_time, end_time, subject, teacher_id, classroom) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert_stmt->bind_param('siiisssis', $date, $faculty_id, $subgroup, $lesson_number, $start_time, $end_time, $subject, $teacher_id, $classroom);
    $insert_stmt->execute();
    $insert_stmt->close();
}

// Получаем список групп
$faculties = $conn->query("SELECT * FROM faculty ORDER BY name");

// Получаем список подтвержденных преподавателей
$teachers = $conn->query("SELECT id, fullname FROM users WHERE role = 'teacher' AND is_approved = TRUE ORDER BY fullname");

// Получаем список неподтвержденных преподавателей
$pending_teachers = $conn->query("SELECT id, fullname, email, login, created_at FROM users WHERE role = 'teacher' AND is_approved = FALSE ORDER BY created_at");

// Функции для работы с датами
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

function getShortDayName($date) {
    $days = ['Monday' => 'Пн', 'Tuesday' => 'Вт', 'Wednesday' => 'Ср', 'Thursday' => 'Чт', 'Friday' => 'Пт', 'Saturday' => 'Сб'];
    $englishDay = date('l', strtotime($date));
    return $days[$englishDay];
}

function getLessonTime($number) {
    $times = [
        1 => '08:30-09:15', 2 => '09:20-10:05', 3 => '10:10-10:55', 4 => '11:00-11:45',
        5 => '12:25-13:10', 6 => '13:15-14:00', 7 => '14:05-14:50', 8 => '14:55-15:40',
        9 => '16:05-16:50', 10 => '16:55-17:40', 11 => '17:45-18:30', 12 => '18:35-19:20'
    ];
    return $times[$number] ?? '';
}

$week_dates = getWeekDates($current_year, $current_week);

// Получаем расписание для выбранной группы
$schedule_data = [];
if ($selected_faculty) {
    foreach ($week_dates as $date) {
        $stmt = $conn->prepare("
            SELECT s.*, u.fullname as teacher_name, f.name as faculty_name 
            FROM schedule s 
            LEFT JOIN users u ON s.teacher_id = u.id 
            LEFT JOIN faculty f ON s.faculty_id = f.id 
            WHERE s.date = ? AND s.faculty_id = ? 
            ORDER BY s.subgroup, s.lesson_number
        ");
        $stmt->bind_param('si', $date, $selected_faculty);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule_data[$date] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - EduSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-blue: #3498db; --dark-blue: #2980b9; }
        .navbar { background: linear-gradient(135deg, #3498db, #2c3e50); }
        .schedule-container { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); overflow-x: auto; }
        .day-header { background: var(--primary-blue); color: white; padding: 15px; text-align: center; font-weight: 600; min-width: 150px; }
        .today .day-header { background: var(--dark-blue); }
        .lesson-card { border: 1px solid #e9ecef; border-radius: 8px; padding: 6px; margin: 2px; background: #f8f9fa; font-size: 0.75rem; min-height: 80px; }
        .lesson-card:hover { background: #e9ecef; cursor: pointer; }
        .empty-lesson { border: 2px dashed #dee2e6; background: transparent; height: 80px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .subgroup-badge { font-size: 0.6rem; padding: 1px 4px; }
        .time-column { background: #f8f9fa; min-width: 100px; }
        .break-row { background: #fff3cd; font-size: 0.7rem; text-align: center; padding: 2px; border-bottom: 1px solid #dee2e6; }
        .schedule-table { min-width: 1200px; }
        .teacher-card { border-left: 4px solid #ffc107; }
        .teacher-card.approved { border-left-color: #28a745; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container">
            <span class="navbar-brand"><i class="bi bi-speedometer2"></i> Админ-панель</span>
            <a href="logout.php" class="btn btn-outline-light">Выйти</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Навигация по разделам -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                    <i class="bi bi-calendar-plus"></i> Расписание
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="teachers-tab" data-bs-toggle="tab" data-bs-target="#teachers" type="button" role="tab">
                    <i class="bi bi-people"></i> Подтверждение учителей
                    <?php if ($pending_teachers->num_rows > 0): ?>
                        <span class="badge bg-danger"><?= $pending_teachers->num_rows ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="groups-tab" data-bs-toggle="tab" data-bs-target="#groups" type="button" role="tab">
                    <i class="bi bi-building"></i> Управление группами
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabsContent">
            <!-- Вкладка расписания -->
            <div class="tab-pane fade show active" id="schedule" role="tabpanel">
                <!-- Выбор группы и навигация -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label">Выберите группу для редактирования расписания:</label>
                                <form method="GET" class="d-flex">
                                    <select class="form-select me-2" name="faculty_id" onchange="this.form.submit()">
                                        <option value="">-- Выберите группу --</option>
                                        <?php while($faculty = $faculties->fetch_assoc()): ?>
                                            <option value="<?= $faculty['id'] ?>" <?= $selected_faculty == $faculty['id'] ? 'selected' : '' ?>>
                                                <?= $faculty['name'] ?> - <?= $faculty['full_name'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="hidden" name="week" value="<?= $current_week ?>">
                                    <input type="hidden" name="year" value="<?= $current_year ?>">
                                </form>
                            </div>
                            <div class="col-md-8 text-end">
                                <div class="btn-group">
                                    <a href="?week=<?= $current_week - 1 ?>&year=<?= $current_year ?>&faculty_id=<?= $selected_faculty ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-chevron-left"></i> Предыдущая
                                    </a>
                                    <span class="btn btn-primary">
                                        Неделя <?= $current_week ?> (<?= date('d.m.Y', strtotime($week_dates[0])) ?> - <?= date('d.m.Y', strtotime($week_dates[5])) ?>)
                                    </span>
                                    <a href="?week=<?= $current_week + 1 ?>&year=<?= $current_year ?>&faculty_id=<?= $selected_faculty ?>" class="btn btn-outline-primary">
                                        Следующая <i class="bi bi-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($selected_faculty): ?>
                <!-- Расписание -->
                <div class="schedule-container">
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
                                            <small><?= getLessonTime($lesson_num) ?></small>
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
                                                        <div class="lesson-card" data-bs-toggle="modal" data-bs-target="#editLessonModal"
                                                             data-date="<?= $date ?>" 
                                                             data-lesson="<?= $lesson_num ?>"
                                                             data-subgroup="<?= $lesson['subgroup'] ?>"
                                                             data-subject="<?= $lesson['subject'] ?>"
                                                             data-teacher="<?= $lesson['teacher_id'] ?>"
                                                             data-classroom="<?= $lesson['classroom'] ?>">
                                                            <div class="fw-bold"><?= $lesson['subject'] ?></div>
                                                            <div><?= $lesson['teacher_name'] ?></div>
                                                            <div>Каб. <?= $lesson['classroom'] ?></div>
                                                            <?php if ($lesson['subgroup'] > 0): ?>
                                                                <span class="badge bg-secondary subgroup-badge">Подгр. <?= $lesson['subgroup'] ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="empty-lesson" data-bs-toggle="modal" data-bs-target="#editLessonModal"
                                                         data-date="<?= $date ?>" 
                                                         data-lesson="<?= $lesson_num ?>"
                                                         data-subgroup="0"
                                                         data-subject=""
                                                         data-teacher=""
                                                         data-classroom="">
                                                        <small class="text-muted">+ Добавить</small>
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
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> Выберите группу для просмотра и редактирования расписания
                    </div>
                <?php endif; ?>
            </div>

            <!-- Вкладка подтверждения учителей -->
            <div class="tab-pane fade" id="teachers" role="tabpanel">
                <div class="row">
                    <!-- Неподтвержденные учителя -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i> Ожидают подтверждения
                                    <?php if ($pending_teachers->num_rows > 0): ?>
                                        <span class="badge bg-danger"><?= $pending_teachers->num_rows ?></span>
                                    <?php endif; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($pending_teachers->num_rows > 0): ?>
                                    <?php while($teacher = $pending_teachers->fetch_assoc()): ?>
                                        <div class="card teacher-card mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= $teacher['fullname'] ?></h6>
                                                <p class="card-text mb-1">
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope"></i> <?= $teacher['email'] ?>
                                                    </small>
                                                </p>
                                                <p class="card-text mb-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-person"></i> Логин: <?= $teacher['login'] ?>
                                                    </small>
                                                </p>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i> Зарегистрирован: <?= date('d.m.Y H:i', strtotime($teacher['created_at'])) ?>
                                                    </small>
                                                </p>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                                                        <button type="submit" name="approve_teacher" class="btn btn-success btn-sm">
                                                            <i class="bi bi-check-lg"></i> Подтвердить
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                                                        <button type="submit" name="reject_teacher" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить этого преподавателя?')">
                                                            <i class="bi bi-x-lg"></i> Отклонить
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                                        <p class="text-muted mt-2">Нет преподавателей, ожидающих подтверждения</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Подтвержденные учителя -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-check-circle"></i> Подтвержденные преподаватели
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php 
                                $approved_teachers = $conn->query("SELECT id, fullname, email, login FROM users WHERE role = 'teacher' AND is_approved = TRUE ORDER BY fullname");
                                if ($approved_teachers->num_rows > 0): ?>
                                    <?php while($teacher = $approved_teachers->fetch_assoc()): ?>
                                        <div class="card teacher-card approved mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= $teacher['fullname'] ?></h6>
                                                <p class="card-text mb-1">
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope"></i> <?= $teacher['email'] ?>
                                                    </small>
                                                </p>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="bi bi-person"></i> Логин: <?= $teacher['login'] ?>
                                                    </small>
                                                </p>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-lg"></i> Подтвержден
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-people" style="font-size: 3rem; color: #6c757d;"></i>
                                        <p class="text-muted mt-2">Нет подтвержденных преподавателей</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка управления группами -->
            <div class="tab-pane fade" id="groups" role="tabpanel">
                <!-- Добавление новой группы -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Добавить новую группу</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="add_faculty" value="1">
                            <div class="col-md-3">
                                <label class="form-label">Курс</label>
                                <select class="form-select" name="course" required>
                                    <option value="">Выберите курс</option>
                                    <option value="1">1 курс</option>
                                    <option value="2">2 курс</option>
                                    <option value="3">3 курс</option>
                                    <option value="4">4 курс</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Сокращение</label>
                                <input type="text" class="form-control" name="abbreviation" placeholder="ИСИП" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Буква</label>
                                <select class="form-select" name="letter" required>
                                    <option value="">Выберите букву</option>
                                    <option value="А">А</option>
                                    <option value="Б">Б</option>
                                    <option value="В">В</option>
                                    <option value="Г">Г</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Полная расшифровка</label>
                                <input type="text" class="form-control" name="full_name" placeholder="Информационные системы и программирование" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Добавить группу</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Список существующих групп -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Существующие группы</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Полное название</th>
                                        <th>Дата создания</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $all_faculties = $conn->query("SELECT * FROM faculty ORDER BY name");
                                    while($faculty = $all_faculties->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= $faculty['name'] ?></strong></td>
                                            <td><?= $faculty['full_name'] ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($faculty['created_at'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования урока -->
    <div class="modal fade" id="editLessonModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="scheduleForm">
                    <input type="hidden" name="save_schedule" value="1">
                    <input type="hidden" name="date" id="modalDate">
                    <input type="hidden" name="faculty_id" value="<?= $selected_faculty ?>">
                    <input type="hidden" name="lesson_number" id="modalLesson">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование урока</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Переключатель подгрупп -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="useSubgroups" name="use_subgroups">
                                <label class="form-check-label" for="useSubgroups">По подгруппам</label>
                            </div>
                        </div>

                        <!-- Форма без подгрупп (по умолчанию) -->
                        <div id="normalForm">
                            <div class="mb-3">
                                <label class="form-label">Предмет</label>
                                <input type="text" class="form-control" name="subject" placeholder="Например: Математика">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Преподаватель</label>
                                <select class="form-select" name="teacher_id">
                                    <option value="">Выберите преподавателя</option>
                                    <?php 
                                    $teachers->data_seek(0);
                                    while($teacher = $teachers->fetch_assoc()): ?>
                                        <option value="<?= $teacher['id'] ?>"><?= $teacher['fullname'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Кабинет</label>
                                <select class="form-select" name="classroom">
                                    <option value="">Выберите кабинет</option>
                                    <?php for($i = 100; $i <= 110; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                    <?php for($i = 200; $i <= 210; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                    <?php for($i = 300; $i <= 310; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                    <option value="спортзал">Спортзал</option>
                                    <option value="актовый">Актовый зал</option>
                                    <option value="лаборатория">Лаборатория</option>
                                </select>
                            </div>
                        </div>

                        <!-- Форма с подгруппами (скрыта по умолчанию) -->
                        <div id="subgroupsForm" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Подгруппа 1</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Предмет</label>
                                        <input type="text" class="form-control" name="subgroup1_subject" placeholder="Например: Математика">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Преподаватель</label>
                                        <select class="form-select" name="subgroup1_teacher">
                                            <option value="">Выберите преподавателя</option>
                                            <?php 
                                            $teachers->data_seek(0);
                                            while($teacher = $teachers->fetch_assoc()): ?>
                                                <option value="<?= $teacher['id'] ?>"><?= $teacher['fullname'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Кабинет</label>
                                        <select class="form-select" name="subgroup1_classroom">
                                            <option value="">Выберите кабинет</option>
                                            <?php for($i = 100; $i <= 110; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                            <?php for($i = 200; $i <= 210; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                            <?php for($i = 300; $i <= 310; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Подгруппа 2</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Предмет</label>
                                        <input type="text" class="form-control" name="subgroup2_subject" placeholder="Например: Программирование">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Преподаватель</label>
                                        <select class="form-select" name="subgroup2_teacher">
                                            <option value="">Выберите преподавателя</option>
                                            <?php 
                                            $teachers->data_seek(0);
                                            while($teacher = $teachers->fetch_assoc()): ?>
                                                <option value="<?= $teacher['id'] ?>"><?= $teacher['fullname'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Кабинет</label>
                                        <select class="form-select" name="subgroup2_classroom">
                                            <option value="">Выберите кабинет</option>
                                            <?php for($i = 100; $i <= 110; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                            <?php for($i = 200; $i <= 210; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                            <?php for($i = 300; $i <= 310; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="deleteLesson()">Удалить урок</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editModal = document.getElementById('editLessonModal');
        const useSubgroups = document.getElementById('useSubgroups');
        const normalForm = document.getElementById('normalForm');
        const subgroupsForm = document.getElementById('subgroupsForm');

        // Переключение между формами
        useSubgroups.addEventListener('change', function() {
            if (this.checked) {
                normalForm.style.display = 'none';
                subgroupsForm.style.display = 'block';
            } else {
                normalForm.style.display = 'block';
                subgroupsForm.style.display = 'none';
            }
        });

        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('modalDate').value = button.getAttribute('data-date');
            document.getElementById('modalLesson').value = button.getAttribute('data-lesson');
            
            const subgroup = button.getAttribute('data-subgroup');
            const subject = button.getAttribute('data-subject');
            const teacher = button.getAttribute('data-teacher');
            const classroom = button.getAttribute('data-classroom');
            
            // Сбрасываем форму
            useSubgroups.checked = false;
            normalForm.style.display = 'block';
            subgroupsForm.style.display = 'none';
            document.getElementById('scheduleForm').reset();
            
            // Заполняем данные если урок существует
            if (subgroup == '0') {
                // Обычный урок
                document.querySelector('input[name="subject"]').value = subject;
                document.querySelector('select[name="teacher_id"]').value = teacher;
                document.querySelector('select[name="classroom"]').value = classroom;
            } else if (subgroup == '1') {
                // Подгруппа 1
                useSubgroups.checked = true;
                normalForm.style.display = 'none';
                subgroupsForm.style.display = 'block';
                document.querySelector('input[name="subgroup1_subject"]').value = subject;
                document.querySelector('select[name="subgroup1_teacher"]').value = teacher;
                document.querySelector('select[name="subgroup1_classroom"]').value = classroom;
            }
        });

        function deleteLesson() {
            if (confirm('Удалить этот урок?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_schedule.php';
                
                const date = document.createElement('input');
                date.name = 'date';
                date.value = document.getElementById('modalDate').value;
                form.appendChild(date);
                
                const faculty = document.createElement('input');
                faculty.name = 'faculty_id';
                faculty.value = '<?= $selected_faculty ?>';
                form.appendChild(faculty);
                
                const lesson = document.createElement('input');
                lesson.name = 'lesson_number';
                lesson.value = document.getElementById('modalLesson').value;
                form.appendChild(lesson);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>