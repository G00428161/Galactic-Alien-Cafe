-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3304
-- Generation Time: Dec 17, 2025 at 07:02 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alien_cafe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `favorite_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `merch_id` int DEFAULT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`favorite_id`),
  KEY `fk_fav_user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE IF NOT EXISTS `menu_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `item_type` enum('drink','food','dessert') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `availability` enum('available','unavailable') DEFAULT 'available',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `item_name`, `item_type`, `price`, `availability`, `image_url`, `created_at`) VALUES
(1, 'Galactic Latte', 'drink', 4.99, 'available', 'galactic_latte.jpg', '2025-11-05 11:44:12'),
(2, 'Meteorite Muffin', 'food', 3.49, 'available', 'MeteoriteMuffin.jpg', '2025-11-05 11:44:12'),
(3, 'Dark Matter Mousse Cake', 'dessert', 5.99, 'available', 'Celestial Swirl Cake.png', '2025-11-05 11:44:12'),
(4, 'Celestial Swirl Smoothie', 'drink', 4.49, 'available', 'Celestial Swirl Smoothie.png', '2025-11-05 11:44:12'),
(5, 'Meteor Glow Donut', 'dessert', 2.99, 'available', 'MeteorGlow Donut.png', '2025-11-05 11:44:12'),
(6, 'Space Pizza', 'food', 6.99, 'available', 'space_pizza.webp', '2025-11-05 12:27:35'),
(7, 'Nedula Pasta', 'food', 6.50, 'available', 'NedulaPasta.jpg', '2025-11-05 12:46:38'),
(8, 'Alien Cake', 'dessert', 3.50, 'available', 'AlienCake.png', '2025-11-05 12:49:53'),
(9, 'The Alien Banquet Set', 'food', 27.50, 'available', 'TheAlienBanquetSet.png', '2025-12-10 11:36:08'),
(10, 'Galactic Waffle', 'food', 4.99, 'available', 'galacticwaffle.png', '2025-12-10 11:37:38'),
(11, 'Supernova Slice Bagel', 'food', 3.50, 'available', 'Supernova Slice Bagel.png', '2025-12-10 11:39:17'),
(12, 'The Xenobite Feast', 'food', 30.00, 'available', 'TheXenobiteFeast.png', '2025-12-10 11:39:55');

-- --------------------------------------------------------

--
-- Table structure for table `merchandise`
--

DROP TABLE IF EXISTS `merchandise`;
CREATE TABLE IF NOT EXISTS `merchandise` (
  `merch_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(6,2) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `availability` enum('available','unavailable') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`merch_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `merchandise`
--

INSERT INTO `merchandise` (`merch_id`, `name`, `price`, `image_url`, `availability`) VALUES
(1, 'Alien Suit', 49.99, 'aliensuit.jpg', 'available'),
(2, 'Alien Shirt', 19.99, 'alienshirt.jpg', 'available'),
(3, 'Female Alien Gown', 39.99, 'femalegown.avif', 'available'),
(4, 'AstroWave Café Hoodie', 17.99, 'AstroWave Café Hoodie.png', 'available'),
(5, 'Alien Oracle Cup', 6.00, 'Alien Oracle Cup.png', 'available'),
(6, 'Galactic Sticker Pack', 2.00, 'Galactic Sticker Pack.png', 'available'),
(7, 'Meteorite Chain', 7.99, 'meteoritechain.webp', 'available'),
(8, 'Midnight Galaxy Cap', 14.99, 'Midnight Galaxy Uplink Cap.png', 'available'),
(9, 'Mystery Posters', 3.50, 'mysteryposters.webp', 'available'),
(10, 'Voidwalker Socks', 5.99, 'Voidwalker Galaxy Socks.png', 'available'),
(11, 'Galactic 360 Ball', 12.99, 'GalaxyBall.avif', 'available'),
(12, 'Alien Abduction Lamp', 9.99, 'Alien Cow Abduction Lamp.jpg', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `num_people` int NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `special_requests` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `phone` varchar(20) NOT NULL,
  PRIMARY KEY (`reservation_id`),
  KEY `fk_res_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `num_people`, `reservation_date`, `reservation_time`, `special_requests`, `created_at`, `phone`) VALUES
(1, 1, 756, '5343-06-07', '05:06:00', '', '2025-12-10 20:16:39', ''),
(2, 1, 5, '2026-04-03', '02:02:00', '', '2025-12-10 20:47:49', '0899560082'),
(3, 1, 2, '2026-04-03', '20:00:00', 'NIGGER', '2025-12-16 18:21:25', '0899560082'),
(4, 3, 20, '2026-04-03', '13:00:00', 'Dont invite zain', '2025-12-17 12:44:37', '0899560082');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT 'Overall',
  `rating` int NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `fk_rev_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `username`, `category`, `rating`, `review_text`, `created_at`) VALUES
(5, 3, 'aaron', 'Drinks', 1, 'awful', '2025-12-17 12:49:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Zain', 'g00428161@Atu.ie', '$2y$10$.mXcHxajLNL3wf1NwK.c4.CFUScaLRd./zRqOW1S2TjrHQ7k7URuO', '2025-11-05 11:53:01'),
(2, 'icataim', 'Zain@gmail.com', '$2y$10$/hVlLYw7EMbUU1S06tNS2eG3ps9YPZ2CWNKsx3a16TpwYmL4wMNue', '2025-12-16 18:17:36'),
(3, 'aaron', 'aaron@gmail.com', '$2y$10$fUe1VKuNuHqQJSDrfxLq4O.0Kz.s/QNK5cdplMxn8rolrDqtIWt2K', '2025-12-17 12:43:48'),
(4, 'Conor', 'gconor12@atuu.ie', '$2y$10$r6.bWCEEHtBIljITnt3eW.jFSJGkFRigXFht3axVmgH5gtTXr0nOC', '2025-12-17 18:26:19');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
