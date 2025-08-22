-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2025 at 04:27 PM
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
-- Table structure for table `dietitians`
--

CREATE TABLE `dietitians` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `contact` varchar(20) DEFAULT '',
  `specialization` varchar(100) DEFAULT '',
  `years_experience` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dietitians`
--

INSERT INTO `dietitians` (`id`, `user_id`, `contact`, `specialization`, `years_experience`) VALUES
(1, 7, '018345678', 'Diet Plan', 3),
(2, 9, '017654321', '', NULL);

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
(3, 4, 5, 'best', '2025-08-21 21:16:35'),
(5, 3, 3, 'need improvement', '2025-08-22 12:05:13');

-- --------------------------------------------------------

--
-- Table structure for table `grocery_lists`
--

CREATE TABLE `grocery_lists` (
  `id` int(11) NOT NULL,
  `goal` varchar(50) NOT NULL,
  `items` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `grocery_lists`
--

INSERT INTO `grocery_lists` (`id`, `goal`, `items`, `created_at`) VALUES
(1, 'Muscle Gain', 'Oats, suji, whole wheat atta/roti, brown rice or boiled white rice, chickpeas, kidney beans, black beans, soybeans, peanuts, eggs, chicken breast, beef (lean cuts), fish (especially hilsa, tuna, tilapia), milk, yogurt, paneer (or homemade cottage cheese from milk), powdered milk, bananas, dates, raisins, honey, seasonal fruits (mango in moderation, papaya, guava, jackfruit), leafy greens (palong, lal shak), vegetables like sweet potato, lau, beans, and healthy oils like mustard oil, olive oil, or ghee (small amount).', '2025-08-22 12:30:24'),
(2, 'Weight Loss', 'Oats, brown rice, red rice (atop chal), whole wheat atta/roti, masoor dal (red lentils), moong dal, chhola (chickpeas), soybeans, eggs, skinless chicken, fish (tilapia, ruhi, pangas, ilish in moderation), plain yogurt (doi), skimmed/powdered milk, seasonal vegetables (lau, begun, palong shak, beans, broccoli if available), cucumber, tomato, carrots, lemon, green chili, garlic, onion, and fruits like papaya, guava, apple, orange, and watermelon. Olive oil or mustard oil in small quantity, and green tea.', '2025-08-22 12:31:23'),
(3, 'Healthy Living', 'Atop chal (red rice) or parboiled rice, atta/whole wheat flour, oats, pulses (masoor dal, moong dal), nuts (peanuts, almonds if affordable), soybeans, eggs, chicken/fish (ruhi, katla, tilapia), milk, yogurt, cottage cheese/paneer, seasonal vegetables (lau, begun, palong, beans, carrots, broccoli), fruits (papaya, guava, apple, orange, watermelon, banana), mustard oil/olive oil, garlic, ginger, green chili, and light snacks like muri (puffed rice) or chira (flattened rice) with milk/yogurt.', '2025-08-22 12:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender` enum('user','dietitian') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `sender`, `message`, `created_at`) VALUES
(3, 4, 'user', 'allergic to beef', '2025-08-22 13:19:09');

-- --------------------------------------------------------

--
-- Table structure for table `suggested_plans`
--

CREATE TABLE `suggested_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `calories` varchar(50) DEFAULT NULL,
  `breakfast` text DEFAULT NULL,
  `lunch` text DEFAULT NULL,
  `dinner` text DEFAULT NULL,
  `snacks` text DEFAULT NULL,
  `exercise` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `suggested_plans`
--

INSERT INTO `suggested_plans` (`id`, `user_id`, `calories`, `breakfast`, `lunch`, `dinner`, `snacks`, `exercise`, `created_at`) VALUES
(1, 4, '2000', 'bread,butter,2 eggs,oats with milk', '2 cups of rice, 1 cup of beef/chicken/fish curry, lentils,salad', 'oats with different kind of fruits', 'raw tea,nuts,fruits,cucumber,milk', 'daily 15 minutes weight lifting', '2025-08-22 09:50:27');

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
(4, 'upoma', 'upoma@gmail.com', '$2y$10$ZItEses3a0Duot.2pyZse.9u.T2MxCTnECUnakHv//95qNjrdiOAi', 'user', '2025-08-21 21:13:43', '', 160, 50, 26, 60, 'Muscle Gain'),
(5, 'Chuti', 'chuti@gmail.com', '$2y$10$4bEIrK5QoHwScuq7.lS.3emqhx5vdL/9mamz2tstnIL178LYdooO2', 'user', '2025-08-21 21:33:09', 'Female', 189, 78, 26, 65, 'Weight Loss'),
(7, 'nasima', 'nasima@gmail.com', '$2y$10$lUGhqR7VpJi5W0pOprMyieAOO1trloxfm9ZuEv0MQVu354F/jSBNG', 'dietitian', '2025-08-22 05:03:15', '', NULL, NULL, NULL, NULL, ''),
(9, 'shahina', 'shahina@gmail.com', '$2y$10$kRIDBnM8zojl8z7rCEKXreQKMNumrYh62mCIRrS4JLJ6lqLf2rat6', 'dietitian', '2025-08-22 05:18:20', '', NULL, NULL, NULL, NULL, ''),
(10, 'amin', 'amin@gmail.com', '$2y$10$9L055xD131LYlO8no1BKs.B/Z3.EyOMil.21K1gf52y23xdbphdfm', 'admin', '2025-08-22 13:46:25', '', NULL, NULL, NULL, NULL, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dietitians`
--
ALTER TABLE `dietitians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `idx_feedback_created` (`created_at`);

--
-- Indexes for table `grocery_lists`
--
ALTER TABLE `grocery_lists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `suggested_plans`
--
ALTER TABLE `suggested_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_name` (`role`,`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dietitians`
--
ALTER TABLE `dietitians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `grocery_lists`
--
ALTER TABLE `grocery_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suggested_plans`
--
ALTER TABLE `suggested_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dietitians`
--
ALTER TABLE `dietitians`
  ADD CONSTRAINT `dietitians_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suggested_plans`
--
ALTER TABLE `suggested_plans`
  ADD CONSTRAINT `suggested_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
