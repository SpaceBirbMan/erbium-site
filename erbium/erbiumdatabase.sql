-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Янв 12 2025 г., 13:13
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `erbiumdatabase`
--

-- --------------------------------------------------------

--
-- Структура таблицы `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint(20) NOT NULL,
  `userId` bigint(20) NOT NULL,
  `studioId` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `favorites`
--

INSERT INTO `favorites` (`id`, `userId`, `studioId`) VALUES
(1, 7, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL,
  `senderId` bigint(20) NOT NULL,
  `receiverId` bigint(20) NOT NULL,
  `message` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`id`, `senderId`, `receiverId`, `message`, `createdAt`) VALUES
(1, 3, 1, '1', '2024-12-13 21:07:10'),
(2, 3, 1, '1', '2024-12-13 21:08:29'),
(3, 3, 1, '1', '2024-12-13 21:10:37'),
(4, 3, 1, '2', '2024-12-13 21:15:01'),
(5, 3, 1, '2', '2024-12-13 21:17:14'),
(6, 3, 1, '2', '2024-12-13 21:17:55'),
(7, 3, 1, '23', '2024-12-13 21:20:35'),
(8, 3, 1, 'Запрос от БигБосс', '2024-12-13 21:26:26'),
(9, 7, 1, 'Ответ от левого чела', '2024-12-13 21:27:24'),
(10, 7, 1, 'Айоу', '2024-12-13 21:33:21'),
(14, 7, 4, 'Хелп', '2024-12-13 22:03:19'),
(15, 7, 4, '1', '2024-12-13 22:03:43'),
(16, 7, 4, '1', '2024-12-13 22:06:39');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `studio_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `isActive` binary(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `studio_id`, `user_id`, `rating`, `comment`, `created_at`, `isActive`) VALUES
(2, 1, 7, 1, 'U ful >:(', '2024-12-25 14:55:33', 0x31),
(3, 1, 7, 2, 'gfus', '2024-12-25 14:55:53', 0x31),
(4, 17, 7, 1, 'че за сонце ты че де бил ?', '2024-12-25 14:56:35', 0x31),
(5, 14, 4, 5, 'Who designed this site? Feels like i felt into the sewer.', '2024-12-28 12:32:14', 0x30),
(6, 14, 4, 5, 'Who designed this site? Feels like i felt into the sewer.', '2024-12-28 12:39:30', 0x30),
(7, 14, 4, 5, 'Who designed this site? Feels like i felt into the sewer.', '2024-12-28 12:44:58', 0x30),
(8, 14, 4, 2, 'NWM', '2024-12-28 13:06:44', 0x31),
(9, 14, 4, 3, 'YF', '2024-12-28 13:13:35', 0x31),
(10, 1, 15, 4, 'test', '2025-01-11 09:49:24', 0x31),
(11, 9, 15, 3, 'Отзыв', '2025-01-12 08:31:25', 0x30),
(12, 9, 15, 3, 'Отзыв', '2025-01-12 08:31:50', 0x31);

--
-- Триггеры `reviews`
--
DELIMITER $$
CREATE TRIGGER `prevent_duplicate_reviews` BEFORE INSERT ON `reviews` FOR EACH ROW BEGIN
    -- Проверяем наличие записи с тем же user_id и created_at
    IF EXISTS (
        SELECT 1
        FROM reviews
        WHERE user_id = NEW.user_id AND created_at = NEW.created_at
    ) THEN
        -- Генерируем ошибку, если запись уже существует
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Duplicate review detected: same user_id and created_at';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

CREATE TABLE `sessions` (
  `id` bigint(20) NOT NULL,
  `userId` bigint(20) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiresAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `sessions`
--

INSERT INTO `sessions` (`id`, `userId`, `token`, `expiresAt`) VALUES
(19, 4, '41be839fa686f346ad95dc86a880264c', '2024-11-24 00:57:33'),
(20, 5, 'a2e11068887f714aa8e337eb1eaf91f1', '2024-11-23 13:00:39'),
(21, 3, '0c50d4ebeb22293b4c36730bdb330c4f', '2024-11-23 13:01:54'),
(22, 1, 'dc0dd734b3ac4aae39d1f435da9f74ee', '2024-11-23 13:35:43'),
(23, 3, '9c15c02de0dee5b763715ca5c82f0aa2', '2024-11-30 13:26:18'),
(24, 6, '3d664f63fae076eaf0cdc9f79f430e6f', '2024-11-30 14:00:53'),
(25, 3, 'd4a91beafa0fdd731ca9fb6fcf5f778e', '2024-12-06 23:56:48'),
(26, 7, '06ecea5a6265b6c87b3e6e40398ceb97', '2024-12-07 00:06:58'),
(27, 4, '650e93fccc8bcc72fb419a3dc723cc8d', '2024-12-07 12:55:13'),
(28, 4, 'e0570a1a4b5ed637439ed1054d3f3ad4', '2024-12-07 13:01:41'),
(29, 3, '7f89e4a1e716ff2b69ee4ab72e191412', '2024-12-14 00:23:22'),
(30, 7, '0d4379c453e275a390ae3711bc01207c', '2024-12-14 01:27:07'),
(31, 4, 'd0fe39f083a3cb3e1dc10b9b05cde523', '2024-12-14 02:35:05'),
(32, 7, '1a0e7d2aedd90f09d2e6258c8a22f51f', '2024-12-14 02:36:13'),
(33, 4, 'a4580dc6642094b86dbbf78af72c2f97', '2024-12-14 02:36:57'),
(34, 7, '38f029383ea5c0269885ec94c6a08dfb', '2024-12-14 02:37:03'),
(36, 4, '81b933b6123108e61336138da86362b9', '2024-12-23 20:38:35'),
(37, 4, '5f1d3972b422173c80a36b0d2f49249e9499a2c5e249421f7496d2c08124d25f', '2024-12-24 09:34:33'),
(38, 4, 'a78fd27e49581ec2dde15204efe2df02abc45b4787a8725e296d8355aea2e15b', '2024-12-24 09:34:40'),
(39, 4, 'e50e14410c76ddc73f05394735f0f99556a70f0fec0ca3091b2bb35df099888c', '2024-12-24 09:40:59'),
(40, 4, '14eabb263a5bacea1de37cc94be964c33d693ed70f89b037809ec29747848909', '2024-12-24 09:42:07'),
(41, 4, 'ed61acd20f0aa42b23bcbb55b6745ff833886f7f03170667966c39a4d761c64f', '2024-12-24 09:42:11'),
(42, 4, '4da61ce01fa3069ed1a9c1f164804ec913234f7ed67d2210fb0f3cf7479d3bcd', '2024-12-24 09:43:18'),
(43, 4, '2eb5bba21cfb05f906eb477387a9fc6c93d21bb3d48f55cd96bb0ef1dfd8c77c', '2024-12-24 09:43:51'),
(45, 4, '61911b03b8675ecd090c87b1a36547fbffc2704bd8b93c1c1f1ab51c285745c5', '2024-12-24 16:39:36'),
(46, 4, '2b3ed4fcf4af716b35f9cd656c88fd705ac1493c5f62fc2e4e8a50847647d1aa', '2025-01-22 17:15:51'),
(47, 4, '04f8c7a80a02081689d92caf2b6a81ef978a36f214eabbd890c2d969684a9804', '2025-01-22 17:16:14'),
(48, 4, 'ac45f580aa063209727e2d52c1a46bbf23ce379b6c7af722bdeb62bdcd31793c', '2025-01-22 17:16:24'),
(49, 4, 'bb4ef0284d355fd4304c228d9f6d2a5803e4a4308e7e94638dede97e20e544e1', '2025-01-22 17:16:58'),
(50, 4, '87900d32e71b3ba17f1c1af6e5ee729268592f3a4b76cd2fa9ca718af06265af', '2025-01-22 17:21:05'),
(51, 4, 'ad675b47554a41196ac43da35805ad46f330c2d45a193c0f444c081a552c3aed', '2025-01-22 17:22:22'),
(52, 11, '3eb2726b92a9d5a46cb300d9900821d6429a20776c10d6abe37aef02de5e1f9a', '2025-01-22 17:34:38'),
(53, 12, '20ababc1dc947cdab1a67feb56260aca36169dc0a5d87606474124f151cf4e3c', '2025-01-22 17:35:02'),
(54, 11, '10df6c1fed054514403ae32d4324e5142b5c4917b8351610d4b05d6acbb6f093', '2025-01-22 17:40:26'),
(55, 11, '7a739dd2e7cefa6f5fc714d96e8923beb8117ff48735506b83d946d6032d395a', '2025-01-22 17:40:55'),
(56, 11, '605b2e2eb78ceaad21d286fba2d8d12c5869d74a0b4457db9cb10d82f3124417', '2025-01-22 17:41:20'),
(57, 11, '32c7a8f11b4175f69686033eb43e6e61e59628147cf6fa75582d34baf1a9ab46', '2025-01-22 18:35:33'),
(58, 11, '04e6d79d37f62105fbb626e794a79f9f49353f4d7ac1179f3d39fe48868c3ad5', '2025-01-22 18:35:38'),
(59, 11, 'd3518ce17b9201864c85ca8923c028ccd4aeb7d4d838af711cad9ac670939507', '2025-01-23 03:02:29'),
(61, 4, '8f4642c22c3bb2c7e6406d1a836525e7375beb630f3aba4a72855d86ed9368e1', '2024-12-24 21:22:02'),
(62, 4, 'b33a4e49ac4ff35a2acbb534d84d4729324160d9bc7396c7cc10affd5a186270', '2024-12-24 21:22:11'),
(63, 13, '11898cf8709a0d4b83cd0177aaf18fd67f3c60f025acde76411c9128a69d7e6a', '2024-12-24 21:43:11'),
(64, 11, '08d9ddd3f2eb81d5a48ab92cc9d8b80f6518baa93c1dc573d3b5b3e760922d78', '2024-12-24 21:43:29'),
(65, 11, 'a13b0900a7cfc2548b74b43c24558daf84897230cbf255433edad3bc4f612cba', '2024-12-24 21:43:55'),
(66, 11, 'c5c0ae58c8241fc24b3393452ed19872f7d9fcb50a17edc4293d23766f2cf146', '2024-12-24 21:47:48'),
(67, 11, '2af2d7838bc60fde7b56ddba785838b65b1aeb913c26050b14c1df3b170a5d9b', '2024-12-24 21:47:54'),
(68, 11, '752eb2faea278ac9c4716d7b77311b3fe82b1fb8ea8e50d2c83652e2c100f64b', '2024-12-24 21:49:04'),
(69, 11, '6dce0537d1b0f97939dcf6935d6f059bbb9acb20b80e23d0ee799a4d13466401', '2024-12-24 22:04:56'),
(70, 11, '733291ae5bebc5b2862ae5fa7e23d2afe674339b55b3a72b77af99bae2e777b4', '2024-12-24 22:06:23'),
(71, 8, '14f0e87d59cd046f97be22a83e2f2efe17a471091afe0e67b8069a18e42114af', '2024-12-25 14:06:39'),
(72, 8, 'c1ab37213850a28d117e3332ef9636485dc947df1058592bc9c7dea7fa655545', '2024-12-25 14:47:37'),
(73, 8, 'd48a3ffa2b6667fc20f9da095083ce9c19ddb8a4fe1170f7ac68a957f06a03c4', '2024-12-25 14:49:00'),
(74, 8, '9c2a0fbf27dc397089f1d2ae8e2f50ff08d47e35d07f898467851b5da24a8daa', '2024-12-25 14:50:13'),
(75, 8, '804862ce039aff7a28cf2873f682b3892f5654ddd3f1e6bcfb6802693f3671f0', '2024-12-25 14:52:17'),
(76, 5, '897de20419a682b3f97a7dcf2ab2188b405de94e9a2d20cc5bdfd5549f4e940d', '2024-12-25 14:56:19'),
(77, 11, '1345979a5c07b2dd6d77e1580f3e50cfdbeec645da2feb86a575e610a3dbb244', '2024-12-25 14:56:50'),
(78, 11, '29d803b7dc37c8d256e20fcd4b11bf03457835f6b7d8fbcaed85bbd282afa04d', '2024-12-25 15:02:26'),
(79, 11, '79b0f75620c9bb281a7cfd6a5d5c123c7b935235ab2e86e52c765969073e1dcb', '2024-12-25 15:02:40'),
(80, 4, '7d2d0df5a75d2eb643486d42e79c1042bf216769be58b6b0ab11a70959fe9f17', '2024-12-25 15:02:47'),
(91, 11, 'ea2debfccf63219e70e9b6c61af87fad236e7ff4cc1c5336926d67393abc0e5b', '2024-12-25 15:15:12'),
(92, 11, 'c87297c0e0c053ddd799dfb09167cc2e8747eea4c52a3aa936b8c4832987e45b', '2024-12-25 15:34:20'),
(93, 11, 'aa4e0a854583f37456f8b4e10bdc6329d76b444034d2fd3280c094c8a969bc56', '2024-12-25 15:34:57'),
(94, 11, 'e551ec18760eb279c27b3c976b2afecff61bee80b7a1ebb00ecbea17a46a06e2', '2024-12-25 15:36:29'),
(96, 11, 'd5284350368da6a4c2af989273dada4003324297af2169d6707d6bf0a764bd71', '2024-12-25 15:37:07'),
(97, 11, 'dfd9ce26b838c32068309defa30a122b0b99a391b92e113d3bed76507a575540', '2024-12-24 23:47:30'),
(98, 11, 'd24ca133698d174d35b5fc4cf039496278e6dc299e4684c0e997961ff7d54434', '2024-12-24 23:50:25'),
(99, 11, '060d325a13bbebab48df08e9782356007a4d59f8aed2602034f3138596dc36fb', '2024-12-25 00:03:35'),
(100, 11, 'aa71abfd8cb56f86b8d072c16f10188370e820bb571639dd838722904cae8039', '2024-12-25 00:05:03'),
(101, 11, 'aada3297c07876b728a78a6fde27d85aa672680927285442cefc414b3beee944', '2024-12-25 00:05:52'),
(102, 11, '2c8b7239256151a98f889ad4c4cd214671a24450c6f673e91795dc6ca8d928e5', '2024-12-25 00:08:48'),
(103, 11, '36b09a59d076683069205104f4a83eec8d6654350b2dc61751590849d38ee4d1', '2024-12-25 00:10:12'),
(104, 11, '19cc91c0cd08fca5b4b2cf9d0bcdb7bae02d733a3b71257d78bd3d0400e1e649', '2024-12-25 00:11:52'),
(108, 8, '274e4adfcf1ce3eb01c37878d2594c209847f187d0fce5d1cbbf5be4f2dc1c18', '2024-12-25 00:57:25'),
(109, 8, '77d9aa867193f89f25b8dcd5927e0ac9dcae4a34479b92c029a9f64d26c7f4ed', '2024-12-25 00:57:36'),
(110, 3, '8a86de96fd418e5853dc71cf1c384dea443015fcd41fb37012d2b7ae6e3088be', '2024-12-25 17:02:46'),
(111, 7, 'e542eb1bffb330ecf6813b359a23b7c86eda947cff17c64ac370ae6ffd86fd21', '2024-12-25 23:57:00'),
(112, 7, '00b7bb3a51454b516f82ecfb97e82b4c929998b044c766da47bc6242c563ab02', '2024-12-26 00:28:37'),
(113, 7, '3bec439cb7afdaace1a6d01cdf91dc011bd919b4f25fc30531621e2816de75e6', '2024-12-26 16:28:50'),
(114, 4, '15ad5e67f89dc84b1484abe63c01e6d767a6b6345e54c37006243540387b6775', '2024-12-27 06:10:41'),
(115, 4, 'addcffe06b16186b9da98a6000ddff134d0479c0d502eb44c118d32cb5ca55dc', '2024-12-28 21:18:47'),
(116, 4, '16050374c79e743e36f0700ef18d30c99c1c59115d325a9f83fb77d86e4c0b65', '2024-12-28 22:14:15'),
(117, 1, 'bb240f963f8b5c9b0ff60f10014273dfcef036c8415db329df1d55f62597838f', '2024-12-29 14:25:38'),
(118, 15, '80efbf4e7754df0fc14a2d7ff4c3a38736dfa85e14f4257797da3eccfc298f66', '2025-01-01 18:16:39'),
(119, 7, 'bb71e139d94adb0dc8d8ce98d5a89e05826910e6d433a53a8798f8a13a6fc2a6', '2025-01-01 18:28:51'),
(120, 15, '07edef6c9eead80a1f12fbe68635dd1343cf2a1a0ba24e43f7c9f8c59fa39966', '2025-01-01 18:56:34'),
(121, 3, 'f74a98d7f1edcf942ce2bb727547049e88bad4c3e2a268837d6c3c5008690af7', '2025-01-02 10:56:39'),
(122, 15, '5b0395270530f286ce5f37f059c8d71d9aabc490e2357dfb7701f6e5cff931e1', '2025-01-02 19:09:22'),
(123, 3, '410964b087b2b71ee9d22cccb0a916d81a8b5842446119f2e4da03a9a55a7e53', '2025-01-02 19:10:17'),
(124, 4, 'c247555587dc2abdf83bce6a7ab6648eb69204bdba63d1f71c0b464a48c39f25', '2025-01-03 13:40:01'),
(125, 4, 'bbd3a68ed6a71857867a767dbabe0849499ac384a233ba218d70e6c599b92651', '2025-01-08 20:39:15'),
(126, 15, 'bc6a38385bd17a2bb0d9c3e7ebd2f3bd1aa192e022398241175ff2838b51d5ba', '2025-01-08 20:53:59'),
(127, 3, '3d07c2162a2f916285400da629e7dbb628e7eab4f289b15dec35f4ffa80d20f2', '2025-01-08 21:14:23'),
(128, 7, '758e682e5fd40a925dde179b6f2517c6203509ab8949681aad879a7c0c80c60a', '2025-01-09 13:14:41'),
(129, 15, 'fd50cc10501b8e3d323c0adfa9d3dd8fe4e73528b8f04dc18d451540e81c5f6f', '2025-01-11 19:50:17'),
(130, 3, 'b2613507d34fca0b2988a529ce31c3b16ff91ca4e2410f1168613ebf7f2935a5', '2025-01-11 19:50:39'),
(131, 4, '90164c76c545408ffe1b72f76826a63c2ba9d8f3f9efc51a813f7634f64b5885', '2025-01-11 19:51:34'),
(132, 15, '28f9f6b6ca1809fbc657aa8db213ce033336c676a3e5c95f94368489b3576b35', '2025-01-12 11:51:40'),
(133, 15, 'b25200fbcde5a99a04c6087d2dae4ebb34fa4f36285c80e1beac86fc614d8fcb', '2025-01-12 17:19:30'),
(134, 15, '2d4e7d0de4135917459444442b825dcdab078122e862bc07bf6a6f202df4c3d2', '2025-01-12 17:19:38'),
(135, 15, '46182ea1bb5babebc17319fc6f5cba69c94500cc88df6a3772f57ed4ebcbce33', '2025-01-12 17:20:45'),
(136, 15, '6c1227e04241d239a34bdbab6ae3233fb9a82a6f51c9eec9f22dd8c55732f676', '2025-01-12 19:39:50');

-- --------------------------------------------------------

--
-- Структура таблицы `studioprojects`
--

CREATE TABLE `studioprojects` (
  `id` bigint(20) NOT NULL,
  `studioId` bigint(20) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `studios`
--

CREATE TABLE `studios` (
  `id` bigint(20) NOT NULL,
  `name` text NOT NULL,
  `type` text NOT NULL,
  `contacts` text NOT NULL,
  `description` text NOT NULL,
  `ownerId` bigint(20) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT NULL,
  `isActive` binary(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `studios`
--

INSERT INTO `studios` (`id`, `name`, `type`, `contacts`, `description`, `ownerId`, `createdAt`, `image_path`, `average_rating`, `isActive`) VALUES
(1, 'ewq', 'test', '1', 'a', 3, '2024-11-22 23:48:50', 'uploads/studios/загружено.jpg', 2.33, 0x31),
(2, '2', '2', '2', '2', 3, '2024-11-22 23:50:06', '', NULL, 0x31),
(3, 'bebraHouse', 'indie', '123123', 'GFUS', 3, '2024-11-22 23:56:00', 'uploads/studios/photo_2024-11-06_20-33-25.jpg', NULL, 0x31),
(4, 's', 'indie', 's', 's', 3, '2024-11-22 23:57:06', 'uploads/studios/загружено.jpg', NULL, 0x31),
(5, 'JenyaAfanas\'ev', 'startup', 'Хз', 'Ыаыаыа', 3, '2024-11-23 00:25:26', 'uploads/studios/photo_2024-10-05_15-04-09.jpg', NULL, 0x30),
(9, '321', 'AAA', '4', '4', 4, '2024-11-23 00:41:01', 'uploads/studios/photo_6740a60d8fbd87.58487754.png', 3.00, 0x31),
(10, 'huh', 'AAA', '53', '52', 4, '2024-11-23 00:43:38', 'uploads/studios/photo_6740a6aa7e78e3.49325965.jpg', NULL, 0x31),
(11, 'Вфтнф', 'AAA', '123', '321', 4, '2024-11-23 08:57:52', 'uploads/studios/photo_67411a80cf4fb6.22366436.png', NULL, 0x31),
(12, 'TS', 'tt', 'tc', 'ы', 3, '2024-12-25 01:07:31', 'uploads/studios/Схема оптимизации.png', NULL, 0x31),
(13, 'TestS', 'IDK', 'netu', 'Зачем?', 7, '2024-12-25 23:07:29', 'uploads/studios/05e912fc-a066-45b6-99fb-693683f30518.webp', NULL, 0x31),
(14, 'swwwwwww', 's', 's', 's', 7, '2024-12-25 23:24:27', 'uploads/studios/изображение_2024-12-25_232251691.png', 3.80, 0x31),
(17, '47', '47', '42', '52', 7, '2024-12-25 23:28:35', 'uploads/studios/изображение_2024-12-25_232833668.png', 1.00, 0x31),
(19, 'E', 'A', 'Y', 'WTF', 4, '2024-12-28 22:14:09', 'uploads/studios/676ff9a13bae5_photo_2024-12-14_11-25-48 (2).jpg', NULL, 0x31),
(20, '21321344324324324324', '43243242', '4324324324', '43243243242', 15, '2025-01-01 18:36:40', 'uploads/studios/67750ca8508ab_photo_2024-09-19_11-29-05.jpg', NULL, 0x30);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `username` text NOT NULL,
  `email` text NOT NULL,
  `password` varbinary(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `isActive` binary(1) DEFAULT '1',
  `isAdmin` binary(1) DEFAULT NULL,
  `avatar` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `createdAt`, `isActive`, `isAdmin`, `avatar`) VALUES
(1, 'test', 'test@test.com', 0x2432792431302458667747663442386d5279734d346576676c7a3266652f3961434b6f74515968304e4c6d4a316e497558444f6b7077442f2e444461, '2024-11-22 21:30:22', 0x30, 0x30, NULL),
(2, 'bebra', 'b@b.b', 0x2432792431302468342e6154467a34584d2e4f5854487942574b47682e4d382e69424c32656f502f734f4e4965552e5352564839624b755162393932, '2024-11-22 23:09:56', 0x30, 0x30, NULL),
(3, 'bigboss', 'bb@ы.ru', 0x2432792431302470514a504c306b74333169534e634872526a636b73655a316b5a415730306655362f4e7a64776d4977504d3569666231687767692e, '2024-11-22 23:21:21', 0x31, 0x30, NULL),
(4, 'biggerboss', 'bb@google.ru', 0x2432792431302467352e677959786c56507a46787273445a56664d5a654a3277687a595a6469734a657874713664686f7753646f54504d766e79414f, '2024-11-23 00:33:01', 0x31, 0x30, NULL),
(5, 'test2', 'test2@2.2', 0x2432792431302442796d514d4661554e7a5342414d35654d5a7932777567776f3741534f53523165797855546d6351754b2e59394a71767a75384b61, '2024-11-23 01:07:19', 0x31, 0x30, NULL),
(6, 'Annal_200Rub', 'mxaprostodoxrena@mail.ru', 0x243279243130245a557538384b593545673562386764797a7630487865707a43316d546453424767556d6e6b50676955654d71375a79313651646569, '2024-11-30 10:00:42', 0x31, 0x30, NULL),
(7, 'Foutrh', 'kirillsisnov@gmail.com', 0x24327924313024456f53724d596e2e623636514779566e58672e35732e4a39466270505a3741464e7537794b61666a4c724d2f4562594d4a3150746d, '2024-12-06 20:06:53', 0x31, 0x31, NULL),
(8, 'fff', 'fff@fff.fff', 0x24327924313024444e33724f43427a58765a4b6e6334616c302e303865597667756a68524854653948313756675741637765316b63667a4e6743754f, '2024-12-24 01:29:56', 0x30, 0x30, NULL),
(9, 'ffff', 'ffff@fff.fff', 0x243279243130246b413936317963656f3631675479725773334a4e78653235427038626a50484e5a564c554e754f704a49495555365a616b74747671, '2024-12-24 01:30:26', 0x31, 0x30, NULL),
(10, 'fffff', 'exame@e.e', 0x243279243130246f464348534168364b316e767a736a4c6d53474642657073515864514c734166717559794166442f3845314a724d757364746e466d, '2024-12-24 01:30:49', 0x31, 0x30, NULL),
(11, '321321', '3213213@rewrewrwe.ru', 0x2432792431302468684472736351624e716872723966437546586841756a554d48616c5267722e51553664312e697a5a2e6e585543516d5933356565, '2024-12-24 01:34:38', 0x30, 0x30, NULL),
(12, '32132132132', '3213213@rewewrewrwe.ru', 0x243279243130246d4b766472524a58344b7532464249307862612f6a2e31417843556b7a6d734664706a64386667385a716857544462487a49707969, '2024-12-24 01:35:01', 0x31, 0x30, NULL),
(13, 'test3', 'tt@t.tt', 0x243279243130246d623234684f646a456f426365624651527a4364392e70395075542f596c476e7339344a6e6d437955535a562e50426c59624b4243, '2024-12-24 21:24:05', 0x31, 0x30, NULL),
(15, 'FAD', 'fad@mail.dom', 0x2432792431302466466958515041706f6c48694b627168746e36364175335573415376394139335568505032554e68347a2f4b744651506d34762e57, '2025-01-01 17:29:24', 0x31, 0x31, NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`userId`,`studioId`),
  ADD KEY `studioId` (`studioId`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `senderId` (`senderId`),
  ADD KEY `receiverId` (`receiverId`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studio_id` (`studio_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `userId` (`userId`);

--
-- Индексы таблицы `studioprojects`
--
ALTER TABLE `studioprojects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`) USING HASH,
  ADD KEY `studioId` (`studioId`);

--
-- Индексы таблицы `studios`
--
ALTER TABLE `studios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`) USING HASH,
  ADD KEY `ownerId` (`ownerId`);
ALTER TABLE `studios` ADD FULLTEXT KEY `description` (`description`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `password` (`password`),
  ADD UNIQUE KEY `username` (`username`) USING HASH,
  ADD UNIQUE KEY `email` (`email`) USING HASH;

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT для таблицы `studioprojects`
--
ALTER TABLE `studioprojects`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `studios`
--
ALTER TABLE `studios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`studioId`) REFERENCES `studios` (`id`);

--
-- Ограничения внешнего ключа таблицы `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`senderId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiverId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`studio_id`) REFERENCES `studios` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `studioprojects`
--
ALTER TABLE `studioprojects`
  ADD CONSTRAINT `studioprojects_ibfk_1` FOREIGN KEY (`studioId`) REFERENCES `studios` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `studios`
--
ALTER TABLE `studios`
  ADD CONSTRAINT `studios_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
