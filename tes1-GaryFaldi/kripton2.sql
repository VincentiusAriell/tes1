-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 06, 2025 at 11:40 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kripton2`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int NOT NULL,
  `user_one` int NOT NULL,
  `user_two` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user_one`, `user_two`, `created_at`, `updated_at`) VALUES
(2, 2, 3, '2025-11-05 11:35:42', '2025-11-05 11:35:42'),
(3, 5, 2, '2025-11-05 17:29:34', '2025-11-05 17:29:34'),
(4, 7, 5, '2025-11-05 18:28:16', '2025-11-05 18:28:16'),
(5, 8, 7, '2025-11-05 18:30:59', '2025-11-05 18:30:59'),
(6, 9, 7, '2025-11-05 19:02:02', '2025-11-05 19:02:02'),
(7, 10, 9, '2025-11-06 17:02:51', '2025-11-06 17:02:51');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `conversation_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message_type` enum('text','image','file') COLLATE utf8mb4_general_ci DEFAULT 'text',
  `message_text` text COLLATE utf8mb4_general_ci,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `encryption_key` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `receiver_id`, `message_type`, `message_text`, `file_path`, `encryption_key`, `is_read`, `created_at`) VALUES
(7, 2, 2, 3, 'text', 'halo', NULL, NULL, 0, '2025-11-05 11:39:08'),
(8, 2, 3, 2, 'image', 'halo juga', 'uploads/1762317646_fc09710e83d5a5391ec237e0915703f2.jpg', NULL, 0, '2025-11-05 11:40:46'),
(9, 2, 2, 3, 'file', '', 'uploads/1762321053_Tugas SO-IF-A-2025.pdf', NULL, 0, '2025-11-05 12:37:33'),
(10, 3, 5, 2, 'text', '1zMjjVVXUkK+cbpe4zZLvbqvgysV+VL5HBB6J3zAPho=', NULL, '{\"iv\":\"AB\\/SH8aI8WnPD3Dohil+DQ==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 17:29:41'),
(11, 3, 5, 2, 'text', 'eAkUx3N66zRi45tpzaAsqQ==', NULL, '{\"iv\":\"dbh\\/qiDnQG3317mZpjJCIw==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 17:30:08'),
(12, 3, 5, 2, 'text', 'crE8X9GgWcgDaFwkwG02XI0qc9a0ehNuHiS2NtJym4k=', NULL, '{\"iv\":\"9JL75Ruftn+OrSsIZNg86g==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 18:25:09'),
(13, 4, 7, 5, 'text', 'bswmmje2QpHUcje19Qah3Q==', NULL, '{\"iv\":\"dmCLTbjUrRfn0MWpClJm7g==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 18:28:19'),
(14, 4, 7, 5, 'text', 'UR/eYv3y6K6PiSMfQ/cobQ==', NULL, '{\"iv\":\"Ig\\/EsuWb1gSzFGvb+RhUyA==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 18:28:25'),
(15, 5, 8, 7, 'text', 'kKlbF3LUe/Gd6343Ds7taQ==', NULL, '{\"iv\":\"OW+0uB9p6\\/3UXU3z46IR6g==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 18:31:08'),
(16, 5, 7, 8, 'text', 'm9FCMFVaz4T7r72TIL1q1Q==', NULL, '{\"iv\":\"ZcCJLRFHj2yKvt2Jy95uEQ==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 18:31:57'),
(17, 5, 7, 8, 'text', 'qGEtJJHIom3qBKhcKRtCzA==', NULL, '{\"iv\":\"mbXNsm59a+XdMGXCTnX35w==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 18:58:01'),
(18, 5, 7, 8, 'text', 'WlnT+PcSuQs3TRGqwyA7PQ==', NULL, '{\"iv\":\"7LpCXcmgi8xRzlZtV64WFw==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 18:58:14'),
(19, 6, 9, 7, 'text', 'xx4Bjse7EVK1M+K81lj7YVoThrsWhFa2qMLGnFG4/IA=', NULL, '{\"iv\":\"LzZHVs4DBkCyAbi5GhVFHQ==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 19:02:13'),
(20, 6, 7, 9, 'text', 'x4/lTqE8Ri2eKWnaVRPf+A==', NULL, '{\"iv\":\"H9g+vEQ8jXHQZRX29+JkuQ==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 19:04:49'),
(21, 6, 7, 9, 'text', '3DRbEaScuC5tdMPlJOwPzg==', NULL, '{\"iv\":\"axYQ7i1H3w5Pcu0Z4qDb+w==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 19:05:25'),
(22, 6, 7, 9, 'text', 'IRCqTmuhxHVuFsQfrWa2zQ==', NULL, '{\"iv\":\"jhv6U2V4JLn0mIPDDCrFmw==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-05 19:05:30'),
(23, 7, 10, 9, 'text', 'ISZJ8otwmxR71pNyfP6WD9aOJF+Zeenis3GDtkLSC8Q=', NULL, '{\"iv\":\"2aiXXBa30F6w5K7EDmvF6w==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-06 17:02:59'),
(24, 7, 10, 9, 'text', 'lsmV4mDeK92m5tJvr8QKjal443j5CEqnzgt8w55plOUc8dVy+0Nebh0kGMz82hG1', NULL, '{\"iv\":\"lE0N7EZ+Raz+01cdyKVUwg==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-06 17:06:21'),
(25, 7, 10, 9, 'text', 'sR03I+Nom6ThKOlpBJpyTQ==', NULL, '{\"iv\":\"JHLDO7JutKXV\\/0qpXO\\/n1A==\",\"alg\":\"caesar+rc4+aes256cbc\"}', 0, '2025-11-06 17:37:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `profile_picture`, `status`, `last_seen`, `created_at`) VALUES
(2, 'miguel2', 'miguel2@gmail.com', 'fd9d94340dbd72c11b37ebb0d2a19b4d05e00fd78e4e2ce8923b9ea3a54e900df181cfb112a8a73228d1f3551680e2ad9701a4fcfb248fa7fa77b95180628bb2', NULL, 'Online', '2025-11-05 10:12:47', '2025-11-04 23:44:08'),
(3, 'miguel3', 'miguel3@gmail.com', 'fd9d94340dbd72c11b37ebb0d2a19b4d05e00fd78e4e2ce8923b9ea3a54e900df181cfb112a8a73228d1f3551680e2ad9701a4fcfb248fa7fa77b95180628bb2', '1762282627_5bcd84b9b4ccce9a5358eea7868365f5.jpg', 'Online', '2025-11-05 11:40:15', '2025-11-05 00:02:01'),
(4, 'riellsorongan', 'abc@gmail.com', 'f81d32686c5161929acc4364ae69fab40ec11d94eda1bbda81eeb2ebeb85793cf2f81fa56dc3fe58407951a23cf088047f53721e0e919965c20272a4241fd853', NULL, NULL, NULL, '2025-11-05 16:04:10'),
(5, 'riell', 'aril12@gmail.com', '3393cddf19828f237733b512cc30ceebde80e29001bde78595475462735623f12827cf112be580dafbecc0471d8123f7daf3e6bd066da68de6ba09e6d49a8bfc', NULL, 'Online', '2025-11-05 16:05:08', '2025-11-05 16:05:00'),
(6, 'bartlett', 'jackbartlett@gmail.com', '08b388f68fd3eb51906ac3d3c699b8e9c3ac65d7ceb49d2e34f8a482cbc3082bc401cead90e85a97b8647c948bf35e448740b79659f3bee42145f0bd653d1f25', NULL, NULL, NULL, '2025-11-05 18:26:51'),
(7, 'kei nagase', 'nagase@gmail.com', 'fd9d94340dbd72c11b37ebb0d2a19b4d05e00fd78e4e2ce8923b9ea3a54e900df181cfb112a8a73228d1f3551680e2ad9701a4fcfb248fa7fa77b95180628bb2', NULL, 'Online', '2025-11-05 19:02:48', '2025-11-05 18:27:52'),
(8, 'huxian', 'huxian@gmail.com', '7aeea2eab115d7103a55aa002813daf052f40ecd443a920fafa3bc7ba223fdce4767a4a393cf961f9c4f872ebfc8cff501feac4b4576afacafb5163dad69da61', NULL, 'Online', '2025-11-05 18:30:48', '2025-11-05 18:30:40'),
(9, 'Larry Foulke', 'pixy@gmail.com', '611993386df3d26678af1d67fdcb8fc91a592359d0c7c44654c7dadb9e37ec278cba5917de330518cd1477fa12f32bee9b6ebd4bc2443ca64aac2ee3e8b6b399', NULL, 'Offline', '2025-11-05 19:02:40', '2025-11-05 19:01:41'),
(10, 'Km53ukMdSgQFQLQ4Gq2pf8ugC3s2ht2x2F7am/iBhow=', 'wizard@gmail.com', '3393cddf19828f237733b512cc30ceebde80e29001bde78595475462735623f12827cf112be580dafbecc0471d8123f7daf3e6bd066da68de6ba09e6d49a8bfc', NULL, 'Offline', '2025-11-06 17:37:42', '2025-11-06 17:01:36'),
(11, '3zgxiErFxU/ob5SiUJL65Fxj1k6Dafjzv5sKlZGNiZSboI58FdZ2/tknbfkgowtIqOhg1mC9TRKhGc9/1KJG40Q6XV38QJbxYY3Ieb0w29CJRK5/EWk1NoBy7G0R1ghv6YxOZGRQ+MVQANI4Hk1uAVOhki6EtdAz9c/q1ERVkD0=', 'jack@gmail.com', '08b388f68fd3eb51906ac3d3c699b8e9c3ac65d7ceb49d2e34f8a482cbc3082bc401cead90e85a97b8647c948bf35e448740b79659f3bee42145f0bd653d1f25', NULL, NULL, NULL, '2025-11-06 18:35:46'),
(12, 'xami62qgBM7WVNvn7bwLzaD/eDlsHLnOHs+6NC7FD7w6ENMFvoFH5GDKdzg5ClRNqga2JPAk1KYj3KjAXTDIPTwlGsffCEvQ6zXuoVkrp1XATpw4o1ejrvqLnyMiPm+wwABlPZMEP1OGvHiBOEnC/l0aavV2oLjmTba54Fx+OIU=', 'sorcerer@gmail.com', '86ace6cd1c1a8e29640bbeca1709b1447df98a9f838a8ffc7810ab1b02eff6a65245ae2d469d1bac769c8865560b6391d1d87d0611347d39a01a4cdf7285e115', NULL, NULL, NULL, '2025-11-06 18:36:13'),
(13, 'V6nKoNaXPJE53dN/h0lk9isYQAvTio78BHqQ1gtV1G+VKX63K3NBVlH+A8srjsALmdbuhwJCj7I22IM4q2OvzoPXc0hbI1jaO8/yIfKimPvZurv1BwQU9qa+BBA/dTWjlF8FO5KIWf5BpUgmU5pnScv3jfMdPyr6Th/pZuNZKpo=', 'pj@gmail.com', 'fd9d94340dbd72c11b37ebb0d2a19b4d05e00fd78e4e2ce8923b9ea3a54e900df181cfb112a8a73228d1f3551680e2ad9701a4fcfb248fa7fa77b95180628bb2', NULL, 'Online', '2025-11-06 18:37:22', '2025-11-06 18:37:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_one` (`user_one`),
  ADD KEY `user_two` (`user_two`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `fk_messages_receiver` (`receiver_id`);

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
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_one`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`user_two`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
