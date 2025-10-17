-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.2
-- Время создания: Окт 17 2025 г., 20:50
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
-- Структура таблицы `schedule`
--

CREATE TABLE `schedule` (
  `id` int NOT NULL,
  `date` date NOT NULL,
  `class_info` varchar(10) NOT NULL,
  `lesson_number` int NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject` varchar(50) NOT NULL,
  `teacher_id` int NOT NULL,
  `classroom` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `schedule`
--

INSERT INTO `schedule` (`id`, `date`, `class_info`, `lesson_number`, `start_time`, `end_time`, `subject`, `teacher_id`, `classroom`, `created_at`) VALUES
(25, '2025-10-20', '9А', 1, '08:30:00', '09:15:00', 'Математика', 7, '101', '2025-10-16 21:47:43'),
(26, '2025-10-20', '9А', 2, '09:20:00', '10:05:00', 'Русский язык', 5, '102', '2025-10-16 21:47:43'),
(27, '2025-10-20', '9А', 3, '10:10:00', '10:55:00', 'Физика', 12, '201', '2025-10-16 21:47:43'),
(28, '2025-10-20', '9А', 4, '11:00:00', '11:45:00', 'История', 9, '103', '2025-10-16 21:47:43'),
(29, '2025-10-20', '9А', 5, '12:25:00', '13:10:00', 'Английский язык', 6, '104', '2025-10-16 21:47:43'),
(30, '2025-10-20', '9А', 6, '13:15:00', '14:00:00', 'Информатика', 8, '301', '2025-10-16 21:47:43'),
(31, '2025-10-20', '9А', 7, '14:05:00', '14:50:00', 'Физкультура', 11, 'спортзал', '2025-10-16 21:47:43'),
(32, '2025-10-20', '9А', 8, '14:55:00', '15:40:00', 'Литература', 16, '105', '2025-10-16 21:47:43'),
(33, '2025-10-20', '10Б', 1, '08:30:00', '09:15:00', 'Химия', 13, '202', '2025-10-16 21:47:43'),
(34, '2025-10-20', '10Б', 2, '09:20:00', '10:05:00', 'Биология', 14, '203', '2025-10-16 21:47:43'),
(35, '2025-10-20', '10Б', 3, '10:10:00', '10:55:00', 'Математика', 7, '204', '2025-10-16 21:47:43'),
(36, '2025-10-20', '10Б', 4, '11:00:00', '11:45:00', 'География', 15, '205', '2025-10-16 21:47:43'),
(37, '2025-10-20', '10Б', 5, '12:25:00', '13:10:00', 'Русский язык', 5, '206', '2025-10-16 21:47:43'),
(38, '2025-10-20', '10Б', 6, '13:15:00', '14:00:00', 'Обществознание', 10, '207', '2025-10-16 21:47:43'),
(39, '2025-10-20', '10Б', 7, '14:05:00', '14:50:00', 'Английский язык', 6, '208', '2025-10-16 21:47:43'),
(40, '2025-10-20', '10Б', 8, '14:55:00', '15:40:00', 'Физкультура', 11, 'спортзал', '2025-10-16 21:47:43'),
(56, '2025-10-21', '10Б', 1, '08:30:00', '09:15:00', 'Химия', 13, '200', '2025-10-16 21:53:42'),
(57, '2025-10-17', '11В', 1, '08:30:00', '09:15:00', 'Биология', 14, '200', '2025-10-16 21:54:01'),
(58, '2025-10-17', '9А', 1, '08:30:00', '09:15:00', 'Математика', 23, '100', '2025-10-16 22:30:48'),
(59, '2025-10-17', '9А', 2, '09:20:00', '10:05:00', 'Информатика', 36, '107', '2025-10-16 22:30:58'),
(60, '2025-11-20', '10Б', 1, '08:30:00', '09:15:00', 'Математика', 23, '100', '2025-10-17 17:09:43'),
(61, '2025-10-20', '8Г', 1, '08:30:00', '09:15:00', 'Русский язык', 33, '100', '2025-10-17 17:10:02');

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
  `class_info` varchar(10) DEFAULT NULL,
  `subject` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `role`, `fullname`, `email`, `login`, `password`, `class_info`, `subject`, `created_at`) VALUES
(5, 'teacher', 'Иванова Анна Сергеевна', 'russian_teacher@school.ru', 'teacher_rus', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Русский язык', '2025-10-16 21:22:41'),
(6, 'teacher', 'Петрова Елена Владимировна', 'english_teacher@school.ru', 'teacher_eng', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Английский язык', '2025-10-16 21:22:41'),
(7, 'teacher', 'Сидоров Михаил Александрович', 'math_teacher@school.ru', 'teacher_math', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Математика', '2025-10-16 21:22:41'),
(8, 'teacher', 'Козлов Дмитрий Игоревич', 'informatics_teacher@school.ru', 'teacher_info', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Информатика', '2025-10-16 21:22:41'),
(9, 'teacher', 'Николаева Ольга Петровна', 'history_teacher@school.ru', 'teacher_hist', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'История', '2025-10-16 21:22:41'),
(10, 'teacher', 'Федоров Сергей Викторович', 'society_teacher@school.ru', 'teacher_soc', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Обществознание', '2025-10-16 21:22:41'),
(11, 'teacher', 'Волков Алексей Николаевич', 'pe_teacher@school.ru', 'teacher_pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Физическая культура', '2025-10-16 21:22:41'),
(12, 'teacher', 'Орлова Марина Дмитриевна', 'physics_teacher@school.ru', 'teacher_phys', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Физика', '2025-10-16 21:22:41'),
(13, 'teacher', 'Лебедев Андрей Сергеевич', 'chemistry_teacher@school.ru', 'teacher_chem', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Химия', '2025-10-16 21:22:41'),
(14, 'teacher', 'Громова Ирина Алексеевна', 'biology_teacher@school.ru', 'teacher_bio', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Биология', '2025-10-16 21:22:41'),
(15, 'teacher', 'Тихонов Павел Олегович', 'geography_teacher@school.ru', 'teacher_geo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'География', '2025-10-16 21:22:41'),
(16, 'teacher', 'Семенова Татьяна Владимировна', 'literature_teacher@school.ru', 'teacher_lit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Литература', '2025-10-16 21:22:41'),
(17, 'student', 'Смирнов Алексей Иванович', 'student1@school.ru', 'student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9А', NULL, '2025-10-16 21:22:42'),
(18, 'student', 'Кузнецова Мария Петровна', 'student2@school.ru', 'student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '10Б', NULL, '2025-10-16 21:22:42'),
(19, 'student', 'Попов Иван Сергеевич', 'student3@school.ru', 'student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '11В', NULL, '2025-10-16 21:22:42'),
(20, 'student', 'Васильева Екатерина Андреевна', 'student4@school.ru', 'student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8Г', NULL, '2025-10-16 21:22:42'),
(21, 'teacher', 'Смирнова Ольга Васильевна', 'russian_teacher2@school.ru', 'teacher_rus2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Русский язык', '2025-10-16 22:03:59'),
(22, 'teacher', 'Джонсон Роберт Вильямс', 'english_teacher2@school.ru', 'teacher_eng2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Английский язык', '2025-10-16 22:03:59'),
(23, 'teacher', 'Ковалева Татьяна Михайловна', 'math_teacher2@school.ru', 'teacher_math2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Математика', '2025-10-16 22:03:59'),
(24, 'teacher', 'Новиков Артем Сергеевич', 'informatics_teacher2@school.ru', 'teacher_info2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Информатика', '2025-10-16 22:03:59'),
(25, 'teacher', 'Белова Людмила Николаевна', 'history_teacher2@school.ru', 'teacher_hist2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'История', '2025-10-16 22:03:59'),
(26, 'teacher', 'Григорьева Елена Анатольевна', 'society_teacher2@school.ru', 'teacher_soc2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Обществознание', '2025-10-16 22:03:59'),
(27, 'teacher', 'Тарасов Виктор Иванович', 'pe_teacher2@school.ru', 'teacher_pe2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Физическая культура', '2025-10-16 22:03:59'),
(28, 'teacher', 'Жукова Надежда Павловна', 'physics_teacher2@school.ru', 'teacher_phys2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Физика', '2025-10-16 22:03:59'),
(29, 'teacher', 'Морозов Денис Олегович', 'chemistry_teacher2@school.ru', 'teacher_chem2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Химия', '2025-10-16 22:03:59'),
(30, 'teacher', 'Зайцева Вероника Игоревна', 'biology_teacher2@school.ru', 'teacher_bio2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Биология', '2025-10-16 22:03:59'),
(31, 'teacher', 'Данилова Светлана Викторовна', 'geography_teacher2@school.ru', 'teacher_geo2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'География', '2025-10-16 22:03:59'),
(32, 'teacher', 'Фролов Александр Дмитриевич', 'literature_teacher2@school.ru', 'teacher_lit2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Литература', '2025-10-16 22:03:59'),
(33, 'teacher', 'Громов Алексей Владимирович', 'russian_teacher3@school.ru', 'teacher_rus3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Русский язык', '2025-10-16 22:03:59'),
(34, 'teacher', 'Браун Эмили Джейн', 'english_teacher3@school.ru', 'teacher_eng3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Английский язык', '2025-10-16 22:03:59'),
(35, 'teacher', 'Орлов Сергей Петрович', 'math_teacher3@school.ru', 'teacher_math3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Математика', '2025-10-16 22:03:59'),
(36, 'teacher', 'Мельников Павел Андреевич', 'informatics_teacher3@school.ru', 'teacher_info3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Информатика', '2025-10-16 22:03:59'),
(37, 'teacher', '123', '123@mail.ru', '123', '$2y$10$XD52cCK1Xpgutr8gBSLx3euPEfOynhRsUpjjmcpYOlh5EJglHd/L6', NULL, 'Информатика', '2025-10-16 22:38:40'),
(38, 'student', 'дАНИЯл ЗАгиТОв', 'ZAGITOV@MAIL.RU', 'ZAGITOV', '$2y$10$xARjbaWx64aADJzJ0JKICusL8.2lYFqDGp4vh1feTamzsffqUDJFG', '1А', NULL, '2025-10-17 17:11:56');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_class_time` (`date`,`class_info`,`lesson_number`),
  ADD UNIQUE KEY `unique_classroom_time` (`date`,`classroom`,`lesson_number`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
