-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2025 at 08:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `back7`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmark`
--

CREATE TABLE `bookmark` (
  `user_id` varchar(20) NOT NULL,
  `playlist_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookmark`
--

INSERT INTO `bookmark` (`user_id`, `playlist_id`) VALUES
('4KtPRVwnRqdYgHktCob1', 'destRnUBv4JaOZJpqLjX');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` varchar(20) NOT NULL,
  `content_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `comment` varchar(1000) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content_id`, `user_id`, `tutor_id`, `comment`, `date`) VALUES
('OSudM55CEvokxan1DzWP', 'sEBvcr2lynSwO8wMVPaA', 'JLL2u80LrLRJGo3maI3d', 'gH34TD1CnzJxJm4pn97n', 'Informative lecture ', '2025-07-29');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `number` int(10) NOT NULL,
  `message` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `playlist_id` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `video` varchar(100) NOT NULL,
  `thumb` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'deactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`id`, `tutor_id`, `playlist_id`, `title`, `description`, `video`, `thumb`, `date`, `status`) VALUES
('sEBvcr2lynSwO8wMVPaA', 'gH34TD1CnzJxJm4pn97n', 'destRnUBv4JaOZJpqLjX', 'Digital Logic Design', 'Introduction of DIgital Logic Design', 'bFcGU1zEyavthSzUpKy0.pptx', 'nSXGrrtgb14TK0Kn3dlG.gif', '2025-07-23', 'active'),
('v0GesG4S6ZXGauL3LvVz', 'gH34TD1CnzJxJm4pn97n', 'destRnUBv4JaOZJpqLjX', 'ai', 'hhh', 'mCW99wgH07laADxGg6t2.docx', 'vb5OydAsIRexQhJ31WMK.png', '2025-07-24', 'active'),
('RGYM0Nrgsw0NZyVRox5Z', 'BruwnR3NEh4jMJPyZOqn', 'OyJI1MBLbXpZmsdOvdIO', 'ICT', 'introduction to computing', '6kUHOq8urrcwHpLcGuM3.pdf', 'GcVp3zudCWt5igawdXbB.jfif', '2025-07-28', 'active'),
('Lh850Klv6TVtWHH6hGON', 'gH34TD1CnzJxJm4pn97n', 'hbPqJz6TnamlcbsK6liA', 'Python Lecture 1', 'This is the basic Introduction to Python Lecture 1 \r\nfor the students \r\nin the last page of the slide \r\nthere is exercise must do it ', 'IlMnwbBeLaiWX4Empuxm.pdf', 'gbBoI0RhQ3jT4g6tiJqg.jfif', '2025-07-30', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `forum_comment_likes`
--

CREATE TABLE `forum_comment_likes` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  `content_type` enum('text','image','pdf','ppt','doc') NOT NULL DEFAULT 'text',
  `content` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_post_comments`
--

CREATE TABLE `forum_post_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_post_likes`
--

CREATE TABLE `forum_post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `user_id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `content_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`user_id`, `tutor_id`, `content_id`) VALUES
('4KtPRVwnRqdYgHktCob1', 'gH34TD1CnzJxJm4pn97n', 'v0GesG4S6ZXGauL3LvVz'),
('JLL2u80LrLRJGo3maI3d', 'gH34TD1CnzJxJm4pn97n', 'sEBvcr2lynSwO8wMVPaA'),
('JLL2u80LrLRJGo3maI3d', 'gH34TD1CnzJxJm4pn97n', 'v0GesG4S6ZXGauL3LvVz');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` varchar(50) NOT NULL,
  `receiver_id` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `date`, `is_read`, `read_at`) VALUES
(0, 'gH34TD1CnzJxJm4pn97n', 'BruwnR3NEh4jMJPyZOqn', 'hello', '2025-07-22 19:03:29', 0, NULL),
(0, 'BruwnR3NEh4jMJPyZOqn', 'gH34TD1CnzJxJm4pn97n', 'hey', '2025-07-22 21:24:18', 1, '2025-07-30 06:48:58'),
(0, '2ROTOY1lD2hGm1Sb9yPC', 'gH34TD1CnzJxJm4pn97n', 'hello', '2025-07-29 05:48:39', 1, '2025-07-30 06:48:40'),
(0, '2ROTOY1lD2hGm1Sb9yPC', 'aTc8tOCa6nEpD89oCahq', 'hey', '2025-07-29 07:13:34', 1, '2025-08-18 22:23:50'),
(0, 'gH34TD1CnzJxJm4pn97n', '2ROTOY1lD2hGm1Sb9yPC', 'hello', '2025-07-30 11:50:02', 1, '2025-07-31 06:16:09'),
(0, 'gH34TD1CnzJxJm4pn97n', '2ROTOY1lD2hGm1Sb9yPC', 'hello', '2025-07-31 06:15:29', 1, '2025-07-31 06:16:09'),
(0, 'gH34TD1CnzJxJm4pn97n', '2ROTOY1lD2hGm1Sb9yPC', 'HH', '2025-07-31 08:58:40', 1, '2025-07-31 08:59:25'),
(0, 'gH34TD1CnzJxJm4pn97n', '2ROTOY1lD2hGm1Sb9yPC', 'hy', '2025-08-02 22:32:37', 1, '2025-08-02 22:48:27'),
(0, '2ROTOY1lD2hGm1Sb9yPC', 'gH34TD1CnzJxJm4pn97n', 'heelo', '2025-08-03 09:07:34', 1, '2025-08-03 09:27:14'),
(0, '2ROTOY1lD2hGm1Sb9yPC', 'gH34TD1CnzJxJm4pn97n', 'hell', '2025-08-03 09:07:37', 1, '2025-08-03 09:27:14'),
(0, 'gH34TD1CnzJxJm4pn97n', '68JUaTigVgDFmWxya5UG', 'hey', '2025-08-03 09:28:27', 1, '2025-08-15 06:02:54'),
(0, 'gH34TD1CnzJxJm4pn97n', '68JUaTigVgDFmWxya5UG', 'hey', '2025-08-15 05:53:16', 1, '2025-08-15 06:02:54'),
(0, 'gH34TD1CnzJxJm4pn97n', '8GQybofpzHC9H4qGlv3h', 'hey', '2025-08-18 21:52:30', 0, NULL),
(0, 'gH34TD1CnzJxJm4pn97n', '2ROTOY1lD2hGm1Sb9yPC', 'hello', '2025-08-18 21:53:05', 0, NULL),
(0, 'aTc8tOCa6nEpD89oCahq', '2ROTOY1lD2hGm1Sb9yPC', 'hey', '2025-08-18 22:24:24', 0, NULL),
(0, 'gH34TD1CnzJxJm4pn97n', 'yXYNwjIAofYgdAhwpuZT', 'Asalam Alaikum Ma&#39;am', '2025-08-22 12:52:01', 1, '2025-08-22 13:33:51'),
(0, 'gH34TD1CnzJxJm4pn97n', 'yXYNwjIAofYgdAhwpuZT', 'ma&#39;am*', '2025-08-22 12:52:33', 1, '2025-08-22 13:33:51'),
(0, 'gH34TD1CnzJxJm4pn97n', 'yXYNwjIAofYgdAhwpuZT', 'maam*', '2025-08-22 12:52:50', 1, '2025-08-22 13:33:51'),
(0, 'gH34TD1CnzJxJm4pn97n', '8eEKUPgjl86sr7htyL8O', 'Asalam Alaikum Sir ', '2025-08-22 12:54:26', 1, '2025-08-22 12:57:49'),
(0, 'gH34TD1CnzJxJm4pn97n', '8eEKUPgjl86sr7htyL8O', 'can I use Your lectures they are really informative?', '2025-08-22 12:55:27', 1, '2025-08-22 12:57:49'),
(0, 'yXYNwjIAofYgdAhwpuZT', 'gH34TD1CnzJxJm4pn97n', 'G haan meiny sign kar dea hai ', '2025-08-22 13:34:04', 0, NULL),
(0, 'yXYNwjIAofYgdAhwpuZT', 'gH34TD1CnzJxJm4pn97n', 'ap bhi kar lei sign', '2025-08-22 13:34:35', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--

CREATE TABLE `playlist` (
  `id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `thumb` varchar(100) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'deactive',
  `department` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlist`
--

INSERT INTO `playlist` (`id`, `tutor_id`, `title`, `description`, `thumb`, `date`, `status`, `department`) VALUES
('destRnUBv4JaOZJpqLjX', 'gH34TD1CnzJxJm4pn97n', 'DLD', 'Auto-created playlist', 'FxGhlYFmroD0hiqOFcwV.jpg', '2025-07-23', 'active', ''),
('OyJI1MBLbXpZmsdOvdIO', 'BruwnR3NEh4jMJPyZOqn', 'ICT', 'Auto-created playlist', 'eFz6orT9nyLlPhB1EF0h.png', '2025-07-28', 'active', ''),
('sHFqo29aBhzgZFyysVh2', '2ROTOY1lD2hGm1Sb9yPC', 'Data Science', 'Intro to Data Science', 'RM6ef8Q0ZtGBaOHDWDiU.jpg', '2025-07-29', 'active', ''),
('snfxKY63LVKsWqwlQtyp', '68JUaTigVgDFmWxya5UG', 'software Engineering', 'intro', 'tGvGwAM5iL4AMwu7KIHA.jfif', '2025-08-02', 'active', '');

-- --------------------------------------------------------

--
-- Table structure for table `tutors`
--

CREATE TABLE `tutors` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `profession` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `faculty` varchar(255) DEFAULT NULL,
  `batch` varchar(255) DEFAULT NULL,
  `university` varchar(225) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `last_active` timestamp NULL DEFAULT NULL,
  `departments` varchar(254) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutors`
--

INSERT INTO `tutors` (`id`, `name`, `username`, `profession`, `email`, `password`, `image`, `gender`, `faculty`, `batch`, `university`, `status`, `last_active`, `departments`) VALUES
('gH34TD1CnzJxJm4pn97n', 'Bushra Khan', 'bisma', 'teacher', 'bushrakhan@gmail.com', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2', 'XbE09sFUKrpMboZjJlty.jfif', 'Female', 'BE', '2021', 'Quest Nawabshah', 'Approved', NULL, ''),
('BruwnR3NEh4jMJPyZOqn', 'zara', 'zara', '', 'zara@gmail.com', '8aefb06c426e07a0a671a1e2488b4858d694a730', 'Nc9mQzJ3UXPhg4kxbUma.jpg', 'Female', 'BE', '2021', 'quest nawabshah', 'Rejected', NULL, ''),
('2ROTOY1lD2hGm1Sb9yPC', 'sara', 'sara', '', 'sara@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'FC3vi6PTvhanMhYnUGYS.jpg', 'Female', 'BS', '2021', 'quest', 'Rejected', NULL, ''),
('aTc8tOCa6nEpD89oCahq', 'zehra', 'Zehra', '', 'zehra@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', '1EGfL541J5nUQZyzNUqs.webp', 'Female', 'BE', '2021', 'Quest ', 'Rejected', NULL, ''),
('BHgOl826fWeFMdStUsh5', 'Ali', 'Ali', '', 'ali@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'iuZfwY6tjQHiAmDSJltx.jpg', 'Male', '', '2021,2022', 'Quest', 'Rejected', NULL, 'Data Science,Software Engineering,Electrical Engineering'),
('68JUaTigVgDFmWxya5UG', 'Atlas', 'Atlas', '', 'atlas@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'qJIgF9btLfA9x3s7heOo.jpg', 'Male', 'BS,BE', '2021', 'Quest', 'Rejected', NULL, 'Information Technology,Computer Science,Artificial Intelligence,Software Engineering,Electrical Engineering'),
('8GQybofpzHC9H4qGlv3h', 'expert', 'expert', '', 'expert@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'U4QBQBB5nozHlntCSdb3.jpg', 'Female', 'BE', '2022', 'SBBU', 'Rejected', NULL, 'Electrical Engineering,Mechanical Engineering'),
('NCaZVVe85Ywapq3UiwFG', 'xyz', 'XYZ', '', 'xyz@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'KbGWSkENf6FjOOeL0VXT.jfif', 'Female', 'BS', '2021,2023', 'Mehran', 'Rejected', NULL, 'Computer Science'),
('8eEKUPgjl86sr7htyL8O', 'Dr. Aijaz Ahmed Arain', 'Dr Aijaz Ahmed Arain', '', 'Aijazahmed@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'SRsTtnrzC3El9YTyEIjY.jpg', 'Male', 'BS', '2021,2022,2023', 'Quest', 'Approved', NULL, 'Computer Science'),
('i76lkkthumTy4T9aOXoH', 'Mr. Nadeem Channa', 'Mr. Nadeem Channa', '', 'nadeemchanna@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'hKCWowG55snBwsqxRv9g.jpg', 'Male', 'BS', '2021,2022', 'Quest', 'Approved', NULL, 'Computer Science'),
('yXYNwjIAofYgdAhwpuZT', 'Dr Shamshad Lakho', 'Dr Shamshad Lakho ', '', 'shamshad@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'a7nXukUFLgIOGTG8fTkS.jfif', 'Female', 'BS', '2021,2022', 'Quest', 'Approved', NULL, 'Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(20) NOT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `batch_roll_no` varchar(50) DEFAULT NULL,
  `university` varchar(50) DEFAULT NULL,
  `program` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `image`, `username`, `gender`, `batch_roll_no`, `university`, `program`, `department`) VALUES
('4KtPRVwnRqdYgHktCob1', 'bisma', 'bisma@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'rrsZMiMaOqd9tfunoAiJ.png', 'bisma', 'Female', '21bcs79', 'Quest', 'BS', 'Software Engineering'),
('JLL2u80LrLRJGo3maI3d', 'ali', 'ali@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'kCmPSSqBcuNQO03lhaj4.png', 'ali', 'Male', '01bcs01', 'Quest', 'BS', 'Software Engineering'),
('rAON5d7RAG6dfPKTLZXI', 'xyz', 'xyz@gmail.com', '39dfa55283318d31afe5a3ff4a0e3253e2045e43', 'Dtl8LIpTXWdn9EViMfWn.png', 'xyz', 'Female', '21bcs01', 'SBBU', 'BS', 'Software Engineering');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `forum_comment_likes`
--
ALTER TABLE `forum_comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_comment_like` (`comment_id`,`user_id`,`user_type`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_active_created` (`is_active`,`created_at`);

--
-- Indexes for table `forum_post_comments`
--
ALTER TABLE `forum_post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_active_post` (`is_active`,`post_id`);

--
-- Indexes for table `forum_post_likes`
--
ALTER TABLE `forum_post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`,`user_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `forum_comment_likes`
--
ALTER TABLE `forum_comment_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_post_comments`
--
ALTER TABLE `forum_post_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_post_likes`
--
ALTER TABLE `forum_post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_comment_likes`
--
ALTER TABLE `forum_comment_likes`
  ADD CONSTRAINT `forum_comment_likes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `forum_post_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_post_comments`
--
ALTER TABLE `forum_post_comments`
  ADD CONSTRAINT `forum_post_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_post_likes`
--
ALTER TABLE `forum_post_likes`
  ADD CONSTRAINT `forum_post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
