-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 13, 2026 at 07:05 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quoteify_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `quote_cards`
--

CREATE TABLE `quote_cards` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `quote_text` varchar(1000) NOT NULL,
  `author` varchar(150) DEFAULT '',
  `background_color` char(7) NOT NULL DEFAULT '#f5e6d3',
  `font_family` varchar(60) NOT NULL DEFAULT 'Georgia',
  `font_size` int NOT NULL DEFAULT '28',
  `border_style` enum('none','solid','double','dashed','dotted') NOT NULL DEFAULT 'solid',
  `layout_style` enum('classic','minimal','framed') NOT NULL DEFAULT 'classic',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `font_color` char(7) NOT NULL DEFAULT '#3d3028',
  `visibility` enum('Public','Private') NOT NULL DEFAULT 'Private',
  `text_alignment` enum('left','center','right','justify') NOT NULL DEFAULT 'center'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quote_cards`
--

INSERT INTO `quote_cards` (`id`, `user_id`, `quote_text`, `author`, `background_color`, `font_family`, `font_size`, `border_style`, `layout_style`, `created_at`, `updated_at`, `font_color`, `visibility`, `text_alignment`) VALUES
(1, 3, 'mama mo blue', '', '#f5e6d3', 'Georgia', 28, 'solid', 'classic', '2026-11-13 17:23:14', '2026-11-13 17:23:14', '#a65d45', 'Private', 'center'),
(2, 3, 'asdadasa', '', '#f5e6d3', 'Georgia', 28, 'solid', 'classic', '2026-11-13 17:23:27', '2026-11-13 17:23:27', '#3d3028', 'Public', 'center'),
(5, 3, 'In youth we learn, in age we understand.', 'Marie Ebner-Eschenbach*', '#b95f55', 'Courier New', 28, 'solid', 'classic', '2026-11-13 18:45:22', '2026-11-13 18:45:22', '#ffffff', 'Public', 'center'),
(6, 3, 'Appreciation is a wonderful thing; i makes what is excellent in others belong to us as well.', 'Voltaire', '#b67520', 'Times New Roman', 36, 'dashed', 'framed', '2026-11-13 18:46:17', '2026-11-13 18:46:17', '#ffffff', 'Public', 'center'),
(7, 4, 'It’s never too late to be who you might have been', 'George Elliot', '#7b3d4f', 'Georgia', 32, 'double', 'classic', '2026-11-13 18:47:30', '2026-11-13 18:47:30', '#c35555', 'Public', 'center'),
(8, 4, 'Don’t expect to find life worth living; make it that way', '', '#f5e6d3', 'Georgia', 28, 'solid', 'classic', '2026-11-13 18:47:49', '2026-11-13 18:47:49', '#3d3028', 'Public', 'center'),
(9, 4, 'Life must be lived and curiosity kept alive. One must never, for whatever reason, turn one\'s back on life', 'Eleanor Roosevelt', '#aeaba7', 'Georgia', 28, 'solid', 'classic', '2026-11-13 18:48:31', '2026-11-13 18:48:31', '#8b5e83', 'Public', 'center');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Member') NOT NULL DEFAULT 'Member',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Genine Ashlee Pascual', 'quoteify@gmail.com', '$2y$10$x5vS4JEjuHAMjTbdpWc7JOF4BNACXstTycdeEte9ItvVGQOTx/N.a', 'Admin', '2026-11-13 17:07:33'),
(2, 'aileen may', 'aileenmay@gmai.com', '$2y$10$6Qz9bSGvkKAoEkgWCxbU8ub8vJ8TC8g5D9/0WzYwhRC24ObPK7OdG', 'Member', '2026-11-13 17:20:00'),
(3, 'Genine Ashlee Pascual', 'montefalcoskyaphrodite@gmail.com', '$2y$10$3IbnmwuoXDW/oRNakjCRmueoiSCxI5TjcafmiQqhrDb5qMB88gzj6', 'Member', '2026-11-13 17:22:57'),
(4, 'rosalinda', 'rosalinda@gmail.com', '$2y$10$bK.mS0YQa4NtMZLg3XJyjOP0CVUKJXv2aAmTyXfLyIZRxhXGoi58i', 'Member', '2026-11-13 17:51:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quote_cards`
--
ALTER TABLE `quote_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quote_user` (`user_id`);

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
-- AUTO_INCREMENT for table `quote_cards`
--
ALTER TABLE `quote_cards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quote_cards`
--
ALTER TABLE `quote_cards`
  ADD CONSTRAINT `fk_quote_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
