-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 25 Ara 2025, 11:41:16
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `managex`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `datetime`, `status`, `created_at`) VALUES
(11, 4, '2025-12-25 16:00:00', 'rejected', '2025-12-23 13:08:34'),
(12, 4, '2025-12-25 15:00:00', 'approved', '2025-12-23 13:14:10'),
(13, 4, '2025-12-27 13:15:00', 'rejected', '2025-12-23 13:15:40');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `employees`
--

INSERT INTO `employees` (`id`, `name`, `position`, `department`, `email`, `phone`, `photo`, `created_at`) VALUES
(5, 'Yiğit Alp Sürmeneli', 'Makine Operatörü', 'Üretim Departmanı', 'alplersurmeneli@gmail.com', '05397489961', 'adem abi vol2.png', '2025-12-08 11:20:26'),
(6, 'Kartal Bulut', 'Pazarlama Müdürü', 'Yönetim Departmanı', 'kartalbulut@gmail.com', '01234567890', 'kartal.png', '2025-12-08 11:21:17'),
(7, 'Umudum Furkan Sancak', 'Manifest Bağımlısı', 'Manifest Departmanı', 'umudumesin@gmail.com', '01234567890', 'Ucan_Kopek_Balg.jpeg', '2025-12-23 11:22:55');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `assigned_to` int(11) NOT NULL,
  `status` enum('pending','done') NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `done_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `priority`, `assigned_to`, `status`, `due_date`, `created_by`, `created_at`, `completed_at`, `done_at`) VALUES
(2, 'Acil Ekipman Takviyesi', 'Çok seri Afganistan\'a gitmen lazım.', 'high', 11, 'done', '2026-01-15', 6, '2025-12-23 17:10:03', '2025-12-25 13:29:58', NULL),
(3, 'Deneme Görevi', 'deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme deneme', 'medium', 4, 'pending', '2025-12-30', 6, '2025-12-25 13:21:10', NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(4, 'ahmet', 'ahmet@gmail.com', '$2y$10$.MTrXuqOG5/4lA1t4n6qC.L5xU3BnvyZwzfIxtXx4WQr6mzMKo9BC', 'user', '2025-12-08 10:46:59'),
(6, 'Yağız Alp Sürmeneli', 'yagizalpyonetici@gmail.com', '$2y$10$ZmEcHRjqmenBRCyQyvHeYuJwjf2zSDkA6LyGyselIWLFwhlgUqnPi', 'admin', '2025-12-08 11:06:28'),
(9, 'murat', 'murat@gmail.com', '$2y$10$Fd3H53Iq7B6RIrdaDFiuuOTcZFcBZ9jIitkFuNZGD8016ZK9MgkyC', 'manager', '2025-12-23 11:03:24'),
(11, 'mehmet', 'mehmet@gmail.com', 'mehmet123', 'user', '2025-12-23 13:36:51');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tasks_created_by` (`created_by`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_priority` (`priority`),
  ADD KEY `idx_tasks_due` (`due_date`),
  ADD KEY `idx_tasks_assigned` (`assigned_to`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
