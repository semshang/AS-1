-- Adminer 5.4.2 MariaDB 12.2.2-MariaDB-ubu2404 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `auction`;
CREATE TABLE `auction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `categoryId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `endDate` datetime NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`),
  KEY `userId` (`userId`),
  CONSTRAINT `1` FOREIGN KEY (`categoryId`) REFERENCES `category` (`id`),
  CONSTRAINT `2` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `auction` (`id`, `title`, `description`, `categoryId`, `userId`, `endDate`, `image`, `createdAt`) VALUES
(1,	'2025 Chevrolet Corvette ZR1',	'BEST FOR RACING HIGHLY MODIFIES 200KM',	5,	4,	'2026-05-31 10:54:00',	'car_6a13d999e634a7.09569608.jpg',	'2026-05-25 05:09:45'),
(2,	'jack monster truck',	'best truck in the world',	1,	5,	'2026-06-20 10:59:00',	'car_6a13dab51cb7d7.76985181.jpg',	'2026-05-25 05:14:29'),
(3,	'tesla',	'best electric car',	4,	4,	'2026-06-04 11:02:00',	'car_6a13db822e1595.70974114.jpg',	'2026-05-25 05:17:54'),
(4,	'Toyota C-HR',	'best hybrid',	7,	4,	'2026-06-07 11:03:00',	'car_6a13dbc82b9d67.76605603.jpg',	'2026-05-25 05:19:04'),
(5,	'2020 Ford Mustang Shelby GT500',	'best car',	2,	4,	'2026-06-07 11:05:00',	'car_6a13dc0799fba2.38275858.jpg',	'2026-05-25 05:20:07');

DROP TABLE IF EXISTS `bid`;
CREATE TABLE `bid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` decimal(10,2) NOT NULL,
  `auctionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `auctionId` (`auctionId`),
  KEY `userId` (`userId`),
  CONSTRAINT `1` FOREIGN KEY (`auctionId`) REFERENCES `auction` (`id`),
  CONSTRAINT `2` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `bid` (`id`, `bid`, `auctionId`, `userId`, `createdAt`) VALUES
(1,	100000.00,	1,	5,	'2026-05-25 05:11:29'),
(2,	10000000.00,	2,	4,	'2026-05-25 05:16:06'),
(3,	5000.00,	3,	5,	'2026-05-25 05:20:46'),
(4,	10000.00,	4,	5,	'2026-05-25 05:21:03');

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `category` (`id`, `name`) VALUES
(1,	'4x4'),
(2,	'Sports'),
(3,	'Estate'),
(4,	'Electric'),
(5,	'Coupe'),
(6,	'Saloon'),
(7,	'Hybrid');

DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reviewText` text NOT NULL,
  `reviewerId` int(11) NOT NULL,
  `targetId` int(11) NOT NULL,
  `auctionId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reviewerId` (`reviewerId`),
  KEY `targetId` (`targetId`),
  KEY `auctionId` (`auctionId`),
  CONSTRAINT `1` FOREIGN KEY (`reviewerId`) REFERENCES `user` (`id`),
  CONSTRAINT `2` FOREIGN KEY (`targetId`) REFERENCES `user` (`id`),
  CONSTRAINT `3` FOREIGN KEY (`auctionId`) REFERENCES `auction` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `review` (`id`, `reviewText`, `reviewerId`, `targetId`, `auctionId`, `createdAt`) VALUES
(1,	'super fast',	5,	4,	1,	'2026-05-25 05:11:43'),
(2,	'best hill truck',	4,	5,	2,	'2026-05-25 05:16:17'),
(3,	'ok',	5,	4,	3,	'2026-05-25 05:20:52'),
(4,	'best',	5,	4,	4,	'2026-05-25 05:21:09'),
(5,	'asdwqed',	5,	4,	5,	'2026-05-25 05:21:24');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `user` (`id`, `name`, `email`, `password`, `isAdmin`) VALUES
(1,	'Admin',	'admin@carbuy.com',	'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',	1),
(2,	'sem',	'sem@carbuy.com',	'$2y$10$a41u4V/hIT/L1RVGbxsz9upURZbqzIPd4ABLJNKX8VNzjQSXGkAde',	0),
(4,	'samsung',	'samsung@carbuy.com',	'$2y$10$yDf5FbeLwYelvMn1gDPjKezbcbGff.osxJaj8wkA4X.y3dqh5MYv2',	0),
(5,	'tenzing',	'tenzing@carbuy.com',	'$2y$10$QWMqJDxDo0aK/wpSx96ZVuKYNYuXTbQgCluDceWqHIGjG/L20kT/u',	0);

-- 2026-05-25 05:22:23 UTC