-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.2
-- Время создания: Окт 26 2025 г., 21:29
-- Версия сервера: 8.2.0
-- Версия PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `EduSchedule`
--

-- --------------------------------------------------------

--
-- Структура таблицы `faculty`
--

CREATE TABLE `faculty` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `faculty`
--

INSERT INTO `faculty` (`id`, `name`, `full_name`, `created_at`) VALUES
(1, '1ИСИП', '1 курс Информационные системы и программирование', '2025-10-21 14:43:45'),
(2, '1ДОШ', '1 курс Дошкольное образование', '2025-10-21 14:43:45'),
(3, '1НАЧ', '1 курс Начальное образование', '2025-10-21 14:43:45'),
(4, '1ОИБАС', '1 курс Обеспечение информационной безопасности', '2025-10-21 14:43:45'),
(5, '2ИСИП', '2 курс Информационные системы и программирование', '2025-10-21 14:43:45'),
(6, '2ДОШ', '2 курс Дошкольное образование', '2025-10-21 14:43:45'),
(7, '1ПРИВЕТ-А', 'ДОПУСТИМ', '2025-10-21 15:02:30'),
(8, '2ДАУН-Г', 'ДАУНИДЗЕ', '2025-10-21 15:15:24'),
(9, '1АКУНА-А', 'МАТАТА', '2025-10-21 16:24:35'),
(13, '3ИТИП-В', 'АЛАЛЛАЛАЛ', '2025-10-24 22:41:36'),
(14, '3КРИК-Б', 'ФЫВФЫВ', '2025-10-24 22:41:50'),
(15, '1ПРИВЕТ-Б', 'ФЫВФЫВ', '2025-10-24 22:42:33'),
(18, '2фыфывфыв-А', 'фыфывфывфыв', '2025-10-25 00:10:20');

-- --------------------------------------------------------

--
-- Структура таблицы `room`
--

CREATE TABLE `room` (
  `id` int NOT NULL,
  `room` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `room`
--

INSERT INTO `room` (`id`, `room`) VALUES
(3, '100'),
(4, '101'),
(5, '102'),
(6, '103'),
(7, '104'),
(8, '105'),
(9, '106'),
(10, '107'),
(11, '108'),
(12, '109'),
(13, '110'),
(14, '200'),
(15, '201'),
(16, '202'),
(17, '203'),
(18, '204'),
(19, '205'),
(20, '206'),
(21, '207'),
(22, '208'),
(23, '209'),
(24, '210'),
(25, '300'),
(26, '301'),
(27, '302'),
(28, '303'),
(29, '304'),
(30, '305'),
(31, '306'),
(32, '307'),
(33, '308'),
(34, '309'),
(35, '310'),
(36, '1111'),
(37, 'Спортазл');

-- --------------------------------------------------------

--
-- Структура таблицы `schedule`
--

CREATE TABLE `schedule` (
  `id` int NOT NULL,
  `date` date NOT NULL,
  `faculty_id` int NOT NULL,
  `subgroup` int DEFAULT '1',
  `lesson_number` int NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject` varchar(100) NOT NULL,
  `teacher_id` int NOT NULL,
  `classroom` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `schedule`
--

INSERT INTO `schedule` (`id`, `date`, `faculty_id`, `subgroup`, `lesson_number`, `start_time`, `end_time`, `subject`, `teacher_id`, `classroom`, `created_at`) VALUES
(34, '2025-10-20', 9, 0, 1, '08:30:00', '09:15:00', 'Залез за забор?', 10, '307', '2025-10-24 21:19:32'),
(35, '2025-10-20', 2, 0, 1, '08:30:00', '09:15:00', '', 1, '203', '2025-10-24 21:50:59'),
(36, '2025-10-20', 9, 0, 2, '09:20:00', '10:05:00', '12312', 10, '307', '2025-10-24 22:00:56'),
(37, '2025-10-20', 9, 0, 3, '10:10:00', '10:55:00', '515151', 1, '307', '2025-10-24 22:01:05'),
(40, '2025-10-20', 9, 0, 4, '11:00:00', '11:45:00', '123', 1, '1111', '2025-10-24 22:04:50'),
(41, '2025-10-21', 9, 0, 1, '08:30:00', '09:15:00', 'Математика', 10, '202', '2025-10-24 22:55:06'),
(49, '2025-10-21', 9, 0, 2, '09:20:00', '10:05:00', 'Математика', 1, '203', '2025-10-24 23:05:09'),
(51, '2025-10-21', 9, 0, 3, '10:10:00', '10:55:00', 'ЧЕТО', 9, '203', '2025-10-24 23:17:47'),
(52, '2025-10-21', 9, 0, 4, '11:00:00', '11:45:00', '123', 10, '203', '2025-10-24 23:18:25'),
(53, '2026-10-19', 9, 0, 1, '08:30:00', '09:15:00', 'Математика', 10, '203', '2025-10-25 00:08:41'),
(55, '2025-10-22', 9, 0, 1, '08:30:00', '09:15:00', 'фывфывфывфывфыв', 10, '201', '2025-10-25 00:10:48'),
(56, '2025-10-22', 9, 1, 2, '09:20:00', '10:05:00', 'фывфывфыв', 10, '203', '2025-10-25 00:10:59');

-- --------------------------------------------------------

--
-- Структура таблицы `subjects`
--

CREATE TABLE `subjects` (
  `id` int NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `subjects`
--

INSERT INTO `subjects` (`id`, `name`) VALUES
(1, 'Математика'),
(13, 'Математика'),
(14, 'Физика'),
(15, 'Химия'),
(16, 'Информатика'),
(17, 'Программирование'),
(18, 'Базы данных'),
(19, 'Веб-разработка'),
(20, 'Английский язык'),
(21, 'Русский язык'),
(22, 'Литература'),
(23, 'История'),
(24, 'Обществознание'),
(25, 'Биология'),
(26, 'География'),
(27, 'Физкультура'),
(28, 'ОБЖ'),
(29, 'Черчение'),
(30, 'Экономика'),
(31, 'Право'),
(32, 'Искусство'),
(33, 'ЧЕТО'),
(34, '123'),
(35, 'фывфывфывфывфыв');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `faculty_id` int DEFAULT NULL,
  `group_letter` char(1) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `role`, `fullname`, `email`, `login`, `password`, `faculty_id`, `group_letter`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 'teacher', 'Администратор Системы', 'admin@college.ru', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2025-10-21 14:43:45', '2025-10-21 14:43:45'),
(2, 'student', 'АРТУР', 'arthur@mail.ru', 'arthur', '$2y$10$BDkj9Xy7mTxcUVD1Acf8c.lytudAOych3qSFwbU1/ols9NJI9fbva', 2, 'А', 1, '2025-10-21 14:47:58', '2025-10-21 14:47:58'),
(3, 'teacher', 'Иванова Мария Петровна', 'teacher1@college.ru', 'teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2025-10-21 14:58:52', '2025-10-21 14:58:52'),
(4, 'teacher', 'Петров Алексей Владимирович', 'teacher2@college.ru', 'teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2025-10-21 14:58:52', '2025-10-21 14:58:52'),
(5, 'teacher', 'Сидорова Елена Михайловна', 'teacher3@college.ru', 'teacher3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2025-10-21 14:58:52', '2025-10-21 14:58:52'),
(6, 'teacher', 'Козлов Дмитрий Сергеевич', 'teacher4@college.ru', 'teacher4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2025-10-21 14:58:52', '2025-10-21 14:58:52'),
(7, 'student', 'Смирнов Алексей', 'student1@college.ru', 'student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'А', 1, '2025-10-21 14:58:52', '2025-10-21 14:58:52'),
(8, 'student', 'Кузнецова Мария', 'student2@college.ru', 'student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Б', 1, '2025-10-21 14:58:52', '2025-10-21 14:58:52'),
(9, 'teacher', 'Teacher Teacher', 'TeacherTeacher@mail.ru', 'Teacher_Hist', '$2y$10$y21wehXN0Z2oxj73NuhaoOeXNtuSK9u66MGT/hNwC2QjpaCq/QBWK', NULL, NULL, 1, '2025-10-21 15:21:46', '2025-10-21 15:41:25'),
(10, 'teacher', 'дАНИЯл ЗАгиТОв', 'daun@mail.ru', 'дАНИЯл ЗАгиТОв', '$2y$10$p156PAm5ih2kysIHK86V8.CNTmnbdlPeFEY/5kt5v.2ePiTT10CO.', NULL, NULL, 1, '2025-10-21 15:42:15', '2025-10-21 15:42:39'),
(11, 'student', 'asjuidiopasudiop', '2@gmail.com', 'asdasdsadsa', '$2y$10$uVBuZfshLsMIqI0nguHIuejzXw0i2xe6PINe5c9Ee.1P2sjsM/5/e', 2, 'Б', 1, '2025-10-21 15:58:05', '2025-10-21 15:58:05');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_classroom_time` (`date`,`classroom`,`lesson_number`),
  ADD UNIQUE KEY `unique_teacher_time` (`date`,`teacher_id`,`lesson_number`),
  ADD UNIQUE KEY `unique_faculty_time` (`date`,`faculty_id`,`subgroup`,`lesson_number`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_faculty_id` (`faculty_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_classroom` (`classroom`);

--
-- Индексы таблицы `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_login` (`login`),
  ADD KEY `idx_faculty_id` (`faculty_id`),
  ADD KEY `idx_is_approved` (`is_approved`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `room`
--
ALTER TABLE `room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT для таблицы `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT для таблицы `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
