SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admins` (`id`, `username`, `name`, `profile_picture`, `password`, `created_at`) VALUES
(1, 'admin', 'Admin', '../uploads/1757900182_logo.png', '$2y$10$l8bKGmOxEimNgmRjo1761ertkscY.HPAHexlwrKC7YKcb68YJ.jMi', '2025-09-14 13:24:59');

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `price_general` decimal(10,2) DEFAULT NULL,
  `price_student` decimal(10,2) DEFAULT NULL,
  `price_vip` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `events` (`id`, `title`, `description`, `category`, `event_date`, `location`, `image_url`, `price_general`, `price_student`, `price_vip`, `created_at`, `updated_at`) VALUES
(1, 'Tech Summit 2025', 'The Tech Summit 2025 is the premier gathering for technology professionals, innovators, and thought leaders.', 'Technology', '2025-09-15', 'Lahore, Pakistan', 'uploads/events/event_68c76ab1c6daf3.94785454.jpg', 299.00, 149.00, 499.00, '2025-09-14 13:24:59', '2025-09-15 01:24:01'),
(7, 'Global Music Festival 2025', 'The premier celebration of artists, creators, and cultural soundscapes shaping the future of music.', 'Music', '2025-09-20', 'Islamabad, Pakistan', 'uploads/events/event_68c76b0c892241.94304723.jpg', 200.00, 20.00, 500.00, '2025-09-14 15:21:55', '2025-09-15 01:25:32'),
(8, 'World Business Forum 2025', 'The premier gathering for entrepreneurs, executives, and visionaries redefining global commerce.', 'Business', '2025-10-01', 'Karachi, Pakistan', 'uploads/events/event_68c76b3f88c442.41252293.jpg', 199.00, 129.00, 399.00, '2025-09-15 01:26:23', '2025-09-15 01:26:23');

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `ticket_type` enum('general','student','vip') DEFAULT NULL,
  `promo_code` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `registrations` (`id`, `user_id`, `event_id`, `ticket_type`, `promo_code`, `registration_date`) VALUES
(1, 1, 1, 'general', '', '2025-09-14 13:47:50'),
(3, 1, 7, 'general', '', '2025-09-14 16:32:25');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `name`, `email`, `profile_picture`, `password`, `created_at`) VALUES
(1, 'user', 'user@gmail.com', 'uploads/', '$2y$10$l8bKGmOxEimNgmRjo1761ertkscY.HPAHexlwrKC7YKcb68YJ.jMi', '2025-09-14 13:47:20');

ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;