<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.html');
    exit;
}

// Дата для просмотра (по умолчанию сегодня)
$view_date = isset($_GET['view_date']) ? $_GET['view_date'] : date('Y-m-d');

// Тип сортировки (по умолчанию по классу)
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'class';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $date = $_POST['date'];
    $class_info = $_POST['class_info'];
    $lesson_number = $_POST['lesson_number'];
    $subject = $_POST['subject'];
    $teacher_id = $_POST['teacher_id'];
    $classroom = $_POST['classroom'];
    
    // Время уроков по времени
    $lesson_times = [
        1 => ['08:30:00', '09:15:00'],
        2 => ['09:20:00', '10:05:00'],
        3 => ['10:10:00', '10:55:00'],
        4 => ['11:00:00', '11:45:00'],
        5 => ['12:25:00', '13:10:00'],
        6 => ['13:15:00', '14:00:00'],
        7 => ['14:05:00', '14:50:00'],
        8 => ['14:55:00', '15:40:00']
    ];
    
    $start_time = $lesson_times[$lesson_number][0];
    $end_time = $lesson_times[$lesson_number][1];
    
    // Проверка существования учителя и его предмета
    $check_teacher = $conn->prepare("SELECT id, subject FROM users WHERE id = ? AND role = 'teacher'");
    $check_teacher->bind_param('i', $teacher_id);
    $check_teacher->execute();
    $check_teacher->store_result();
    
    if ($check_teacher->num_rows === 0) {
        $_SESSION['error'] = "Выбранный учитель не существует!";
        $check_teacher->close();
    } else {
        $check_teacher->bind_result($teacher_db_id, $teacher_subject);
        $check_teacher->fetch();
        
        // Проверка что учитель ведет этот предмет
        if ($teacher_subject !== $subject) {
            $_SESSION['error'] = "Учитель не ведет предмет '$subject'!";
        } else {
            // Проверка занятости кабинета
            $check_classroom = $conn->prepare("SELECT id, class_info FROM schedule WHERE date = ? AND lesson_number = ? AND classroom = ?");
            $check_classroom->bind_param('sis', $date, $lesson_number, $classroom);
            $check_classroom->execute();
            $check_classroom->store_result();
            
            if ($check_classroom->num_rows > 0) {
                $check_classroom->bind_result($conflict_id, $conflict_class);
                $check_classroom->fetch();
                $_SESSION['error'] = "Кабинет $classroom уже занят классом $conflict_class в это время!";
            } else {
                // Проверка занятости класса в это время
                $check_class = $conn->prepare("SELECT id FROM schedule WHERE date = ? AND class_info = ? AND lesson_number = ?");
                $check_class->bind_param('ssi', $date, $class_info, $lesson_number);
                $check_class->execute();
                $check_class->store_result();
                
                if ($check_class->num_rows > 0) {
                    $_SESSION['error'] = "У класса $class_info уже есть урок в это время!";
                } else {
                    // Добавление расписания
                    $stmt = $conn->prepare("INSERT INTO schedule (date, class_info, lesson_number, start_time, end_time, subject, teacher_id, classroom) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('ssisssis', $date, $class_info, $lesson_number, $start_time, $end_time, $subject, $teacher_id, $classroom);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Урок успешно добавлен!";
                    } else {
                        $_SESSION['error'] = "Ошибка при добавлении урока: " . $conn->error;
                    }
                    $stmt->close();
                }
                $check_class->close();
            }
            $check_classroom->close();
        }
        $check_teacher->close();
    }
}

// Получение списка классов
$classes = $conn->query("SELECT DISTINCT class_info FROM users WHERE class_info IS NOT NULL ORDER BY class_info");

// Получение расписания на выбранную дату с сортировкой
$order_by = '';
switch($sort_by) {
    case 'classroom':
        $order_by = 's.classroom, s.lesson_number';
        break;
    case 'teacher':
        $order_by = 'u.fullname, s.lesson_number';
        break;
    case 'subject':
        $order_by = 's.subject, s.lesson_number';
        break;
    default: // class
        $order_by = 's.class_info, s.lesson_number';
}

$schedule_query = $conn->prepare("
    SELECT s.*, u.fullname as teacher_name 
    FROM schedule s 
    LEFT JOIN users u ON s.teacher_id = u.id 
    WHERE s.date = ? 
    ORDER BY $order_by
");
$schedule_query->bind_param('s', $view_date);
$schedule_query->execute();
$schedule_result = $schedule_query->get_result();

// Функция для получения русского дня недели
function getRussianDayOfWeek($date) {
    $days = [
        'Sunday' => 'Воскресенье',
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

// Функция для получения названия типа сортировки
function getSortTypeName($sort_by) {
    $names = [
        'class' => 'по классам',
        'classroom' => 'по кабинетам', 
        'teacher' => 'по учителям',
        'subject' => 'по предметам'
    ];
    return $names[$sort_by] ?? 'по классам';
}

// Функция для получения значения группировки
function getGroupValue($lesson, $sort_by) {
    switch($sort_by) {
        case 'classroom': return $lesson['classroom'];
        case 'teacher': return $lesson['teacher_name'] ?? 'Без учителя';
        case 'subject': return $lesson['subject'];
        default: return $lesson['class_info'];
    }
}

// Функция для получения заголовка группы
function getGroupHeader($sort_by, $value) {
    switch($sort_by) {
        case 'classroom': return "Кабинет: $value";
        case 'teacher': return "Учитель: $value";
        case 'subject': return "Предмет: $value";
        default: return "Класс: $value";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #3498db;
            --gradient-primary: linear-gradient(135deg, #3498db, #2c3e50);
        }
        .navbar { background: var(--gradient-primary); }
        .compact-card { 
            border: 1px solid #dee2e6; 
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }
        .compact-card:hover {
            background-color: #f8f9fa;
        }
        .group-header { 
            background-color: #e9ecef; 
            padding: 8px 12px;
            border-radius: 5px; 
            margin: 15px 0 8px 0;
            font-weight: 600;
        }
        .lesson-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .form-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .sort-buttons .btn { margin-right: 5px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container">
            <span class="navbar-brand"><i class="bi bi-speedometer2"></i> Панель администратора</span>
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

        <!-- Форма добавления расписания -->
        <div class="form-section">
            <h5 class="mb-4"><i class="bi bi-plus-circle"></i> Добавить урок в расписание</h5>
            <form method="POST" class="row g-3">
                <input type="hidden" name="add_schedule" value="1">
                
                <div class="col-md-2">
                    <label class="form-label">Дата</label>
                    <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Класс</label>
                    <select class="form-select" name="class_info" required>
                        <option value="">Выберите класс</option>
                        <?php while($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_info'] ?>"><?= $class['class_info'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Урок</label>
                    <select class="form-select" name="lesson_number" required>
                        <option value="">Выберите урок</option>
                        <?php for($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Предмет</label>
                    <select class="form-select" name="subject" id="subjectSelect" required>
                        <option value="">Выберите предмет</option>
                        <option value="Математика">Математика</option>
                        <option value="Русский язык">Русский язык</option>
                        <option value="Английский язык">Английский язык</option>
                        <option value="Информатика">Информатика</option>
                        <option value="История">История</option>
                        <option value="Обществознание">Обществознание</option>
                        <option value="Физика">Физика</option>
                        <option value="Химия">Химия</option>
                        <option value="Биология">Биология</option>
                        <option value="География">География</option>
                        <option value="Литература">Литература</option>
                        <option value="Физкультура">Физкультура</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Учитель</label>
                    <select class="form-select" name="teacher_id" required id="teacherSelect">
                        <option value="">Сначала выберите предмет</option>
                        <?php 
                        // Получаем всех учителей
                        $teachers_result = $conn->query("SELECT id, fullname, subject FROM users WHERE role = 'teacher' AND subject != 'Администратор' ORDER BY subject, fullname");
                        
                        while($teacher = $teachers_result->fetch_assoc()): ?>
                            <option value="<?= $teacher['id'] ?>" data-subject="<?= $teacher['subject'] ?>" style="display: none;">
                                <?= $teacher['fullname'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Кабинет</label>
                    <select class="form-select" name="classroom" required>
                        <option value="">Выберите кабинет</option>
                        <?php for($i = 100; $i <= 110; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                        <?php for($i = 200; $i <= 210; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                        <?php for($i = 300; $i <= 310; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                        <option value="спортзал">Спортзал</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Добавить урок</button>
                </div>
            </form>
        </div>

        <!-- Просмотр расписания -->
        <div class="form-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Просмотр расписания</h5>
                <form method="GET" class="d-flex align-items-center gap-3">
                    <div>
                        <label class="form-label mb-1">Дата</label>
                        <input type="date" class="form-control" name="view_date" value="<?= $view_date ?>" required style="width: 150px;">
                    </div>
                    <div>
                        <label class="form-label mb-1">Сортировка</label>
                        <div class="sort-buttons">
                            <button type="submit"   class="btn btn-<?= $sort_by == 'class' ? 'outline-primary' : 'outline-primary' ?> btn-sm">Обновить</button>
                            <button type="submit" name="sort_by" value="class" class="btn btn-<?= $sort_by == 'class' ? 'primary' : 'outline-primary' ?> btn-sm">Классы</button>
                            <button type="submit" name="sort_by" value="classroom" class="btn btn-<?= $sort_by == 'classroom' ? 'primary' : 'outline-primary' ?> btn-sm">Кабинеты</button>
                            <button type="submit" name="sort_by" value="teacher" class="btn btn-<?= $sort_by == 'teacher' ? 'primary' : 'outline-primary' ?> btn-sm">Учителя</button>
                            <button type="submit" name="sort_by" value="subject" class="btn btn-<?= $sort_by == 'subject' ? 'primary' : 'outline-primary' ?> btn-sm">Предметы</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <h6 class="text-muted mb-3">
                <?= date('d.m.Y', strtotime($view_date)) ?> (<?= getRussianDayOfWeek($view_date) ?>) 
                • Сортировка: <?= getSortTypeName($sort_by) ?>
                <?php if ($schedule_result->num_rows > 0): ?>
                    • Уроков: <?= $schedule_result->num_rows ?>
                <?php endif; ?>
            </h6>
            
            <?php if ($schedule_result->num_rows > 0): ?>
                <?php 
                $current_group = '';
                while($lesson = $schedule_result->fetch_assoc()): 
                    $group_value = getGroupValue($lesson, $sort_by);
                    
                    if ($current_group != $group_value):
                        if ($current_group != '') echo '</div>';
                        $current_group = $group_value;
                ?>
                    <div class="group-header">
                        <?= getGroupHeader($sort_by, $group_value) ?>
                    </div>
                    <div class="lessons-container">
                <?php endif; ?>
                
                <div class="compact-card">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <span class="badge bg-primary"><?= $lesson['lesson_number'] ?></span>
                        </div>
                        <div class="col-md-2">
                            <span class="lesson-time"><?= substr($lesson['start_time'], 0, 5) ?>-<?= substr($lesson['end_time'], 0, 5) ?></span>
                        </div>
                        <div class="col-md-2">
                            <strong><?= $lesson['subject'] ?></strong>
                        </div>
                        <div class="col-md-2">
                            <small>Класс: <?= $lesson['class_info'] ?></small>
                        </div>
                        <div class="col-md-3">
                            <small>Учитель: <?= !empty($lesson['teacher_name']) ? $lesson['teacher_name'] : '<span class="text-muted">Не назначен</span>' ?></small>
                        </div>
                        <div class="col-md-2">
                            <small>Каб. <?= $lesson['classroom'] ?></small>
                        </div>
                    </div>
                </div>
                
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #6c757d;"></i>
                    <p class="text-muted mt-2">На выбранную дату расписания нет</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subjectSelect = document.getElementById('subjectSelect');
            const teacherSelect = document.getElementById('teacherSelect');
            const teacherOptions = teacherSelect.querySelectorAll('option[data-subject]');
            
            // Функция для фильтрации учителей по предмету
            function filterTeachersBySubject(selectedSubject) {
                teacherOptions.forEach(option => {
                    const teacherSubject = option.getAttribute('data-subject');
                    
                    if (selectedSubject === '' || teacherSubject === selectedSubject) {
                        option.style.display = '';
                        option.disabled = false;
                    } else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });
                
                // Сбрасываем выбор учителя если он не подходит к предмету
                const selectedOption = teacherSelect.options[teacherSelect.selectedIndex];
                if (selectedSubject && selectedOption && selectedOption.getAttribute('data-subject') !== selectedSubject) {
                    teacherSelect.value = '';
                }
                
                // Обновляем placeholder
                const firstOption = teacherSelect.querySelector('option[value=""]');
                if (selectedSubject) {
                    firstOption.textContent = 'Выберите учителя';
                } else {
                    firstOption.textContent = 'Сначала выберите предмет';
                }
            }
            
            // Обработчик изменения предмета
            subjectSelect.addEventListener('change', function() {
                filterTeachersBySubject(this.value);
            });
            
            // Инициализация при загрузке
            filterTeachersBySubject(subjectSelect.value);
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>