-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 11:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fitlife_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `users_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 3, 'need improve', '2025-08-21 21:02:15'),
(3, 4, 5, 'best', '2025-08-21 21:16:35'),
(4, 5, 4, 'keep working', '2025-08-21 21:33:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','dietitian','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` varchar(10) DEFAULT '',
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `goal_weight` int(11) DEFAULT NULL,
  `fitness_goal` varchar(50) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `gender`, `height`, `weight`, `age`, `goal_weight`, `fitness_goal`) VALUES
(3, 'Israt', 'israt@gmail.com', '$2y$10$zm2OUMFFugBIaEZ81vrwXeYfRx7ya91MX3dgY5O5Ni6KKXKuu6.qa', 'user', '2025-08-21 13:39:24', 'Female', 152, 69, 25, 60, 'Weight Loss'),
(4, 'upoma', 'upoma@gmail.com', '$2y$10$ZItEses3a0Duot.2pyZse.9u.T2MxCTnECUnakHv//95qNjrdiOAi', 'user', '2025-08-21 21:13:43', '', NULL, NULL, NULL, NULL, ''),
(5, 'Chuti', 'chuti@gmail.com', '$2y$10$4bEIrK5QoHwScuq7.lS.3emqhx5vdL/9mamz2tstnIL178LYdooO2', 'user', '2025-08-21 21:33:09', 'Female', 189, 78, 26, 65, 'Weight Loss');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `idx_feedback_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
