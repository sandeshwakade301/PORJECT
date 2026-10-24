-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2026 at 05:51 PM
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
-- Database: `sujay_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$Hg45CehwraiTZgab95wx1eTPsnXJVy6CtZJrLS8qx1W2egxOFVvm6', '2026-10-17 15:11:58');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `address` text NOT NULL,
  `payment` varchar(50) NOT NULL,
  `token` varchar(50) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(30) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `quantity`, `address`, `payment`, `token`, `order_date`, `status`) VALUES
(5, 2, 16, 1, 'shrirampur', 'Cash on Delivery', 'SW-3394C9F7', '2026-10-23 15:22:07', 'Pending'),
(6, 2, 69, 2, 'pune', 'Cash on Delivery', 'SW-B899C9FF', '2026-10-23 15:22:36', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ml` int(11) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `ml`, `stock`, `image`, `created_at`) VALUES
(16, 'Kingfisher Premium', 'beer', 160.00, 650, 9, 'https://kingfisherdrinks.com/wp-content/uploads/2024/01/KINGFISHER_650ml_BOTTLE-no-shadow-1.png%E2%80%9D', '2026-10-19 06:02:05'),
(17, 'Kingfisher Strong', 'beer', 180.00, 650, 10, 'photo\\LCIN01184.png', '2026-10-19 17:13:12'),
(18, 'Budweiser Premium', 'beer', 200.00, 650, 10, 'photo\\Budweiser-Premium.png', '2026-10-19 17:14:43'),
(19, 'Budweiser Magnum', 'beer', 230.00, 650, 10, 'photo\\LCIN00362.png', '2026-10-19 17:41:53'),
(20, 'Tuborg Strong', 'beer', 165.00, 650, 10, 'https://pune.tonique.in/uploads/products/sm/1712915182-TUBORG-STRONG-BEER--2-.png', '2026-10-19 17:44:47'),
(21, 'Carlsberg Elephant', 'beer', 180.00, 650, 10, 'photo\\Carlsberg Elephant.png', '2026-10-19 17:47:09'),
(22, 'Heineken', 'beer', 200.00, 650, 10, 'photo\\Heineken.png', '2026-10-19 17:47:35'),
(23, 'Corona Extra', 'beer', 200.00, 330, 3, 'photo\\Corona Extra.png', '2026-10-19 17:48:55'),
(24, 'Bira 91 White', 'beer', 140.00, 330, 8, 'https://www.totalwine.com/dynamic/x1000,sq/images/207236126/207236126-2-fr.png', '2026-10-19 17:50:00'),
(25, 'Haywards 5000', 'beer', 160.00, 650, 5, 'https://halftimebeverage.com/media/catalog/product/cache/f3020b7489dcfc4d1d147cf4dad07b7f/rdi/rdi/haywards-5000-indian-premium-beer-36210_1.png', '2026-10-19 17:51:19'),
(26, 'London Pilsner (LP Strong)', 'beer', 140.00, 650, 6, 'https://pune.tonique.in/uploads/products/sm/1712914564-LONDON-PILSONER-STRONG.png', '2026-10-19 17:54:11'),
(27, 'Royal Challenge Strong', 'beer', 170.00, 650, 2, 'photo\\Royal Challenge Strong.png', '2026-10-19 17:56:14'),
(28, 'Grover Zampa Chêne Grande Reserve', 'wine', 2400.00, 750, 4, 'photo\\Grover Chêne Grande Réserve (4).png', '2026-10-20 07:25:30'),
(29, 'Sula Rasa Cabernet Sauvignon', 'wine', 2000.00, 750, 4, 'https://dreyfusashby.com/wp-content/uploads/2024/04/BS-Sula_Rasa-Cab-22.png', '2026-10-20 07:28:02'),
(30, 'Fratelli M2M Reserve Cabernet', 'wine', 2200.00, 750, 3, 'photo\\Fratelli-Cabernet-Sauvignon-Wine-750mL_front.png', '2026-10-20 07:29:43'),
(31, 'KRSMA Cabernet Sauvignon Reserve', 'wine', 2600.00, 750, 8, 'http://www.chopine.in/images/images/431-2019-10-23-06:17:06-krsmacabsau.png', '2026-10-20 07:30:43'),
(32, 'Jacob’s Creek Reserve Shiraz', 'wine', 2100.00, 750, 4, 'https://beveragewarehousemi.com/cdn/shop/products/Jacob_sCreekReserveShiraz_368x_45c7d41c-8fc4-4c72-a316-e87c383dacb5.webp?v=1675130247&width=368', '2026-10-20 07:32:03'),
(33, 'Sula Dindori Reserve Viognier', 'wine', 2300.00, 750, 6, 'https://pune.tonique.in/uploads/products/1714986839-SULA-DINDORI-VIOGNIER.png', '2026-10-20 07:33:03'),
(34, 'Sula Port 1000', 'wine', 295.00, 750, 4, 'https://pune.tonique.in/uploads/products/sm/1740301098-Port-1000.webp', '2026-10-20 07:34:52'),
(35, 'Fratelli Sidus Red', 'wine', 400.00, 750, 19, 'photo\\Fratelli Sidus Red.png', '2026-10-20 07:36:34'),
(36, 'Fratelli Sette', 'wine', 1800.00, 750, 8, 'https://pune.tonique.in/uploads/products/1714648587-FRATELLI-SETTE.png', '2026-10-20 07:38:39'),
(37, 'Sula Madera', 'wine', 500.00, 750, 20, 'https://effingut.com/wp-content/uploads/2025/05/Sula-Madera-Red-Wine.png', '2026-10-20 07:40:56'),
(38, 'York Arros', 'wine', 1000.00, 750, 16, 'photo\\York Arros.png', '2026-10-20 07:42:13'),
(39, 'Virgin Hills', 'wine', 750.00, 750, 5, 'photo\\virgin hills.png', '2026-10-20 07:46:42'),
(40, 'McDowell’s No.1', 'whisky', 160.00, 180, 5, 'https://winepalacegoa.com/wp-content/uploads/2023/04/untitled-9-1.webp', '2026-10-20 08:30:39'),
(41, 'Imperial Blue', 'whisky', 320.00, 375, 8, 'photo\\Imperial Blue.png', '2026-10-20 08:32:43'),
(42, 'Royal Stag', 'whisky', 950.00, 750, 23, 'photo\\Royal Stag.png', '2026-10-20 08:36:17'),
(43, 'Royal Challenge', 'whisky', 380.00, 375, 32, 'https://www.remedyliquor.com/cdn/shop/files/royal-challenge-india-whiskey.png?v=1682605822&width=1445', '2026-10-20 08:37:51'),
(44, 'Oaksmith Gold', 'whisky', 800.00, 375, 7, 'photo\\Oaksmith Gold.png', '2026-10-20 09:10:10'),
(45, 'Antiquity Blue', 'whisky', 260.00, 180, 6, 'photo\\Antiquity Blue.png', '2026-10-20 09:12:23'),
(46, 'Blenders Pride', 'whisky', 300.00, 180, 22, 'photo\\Blenders Pride.png', '2026-10-20 09:14:19'),
(47, 'Black Dog Triple Gold Reserve', 'whisky', 450.00, 180, 32, 'photo\\Black Dog Triple Gold Reserve.png', '2026-10-20 09:16:04'),
(48, 'Johnnie Walker Black Label', 'whisky', 950.00, 180, 22, 'photo\\Johnnie Walker Black Label.png', '2026-10-20 09:17:06'),
(49, 'Officer\'s Choice Blue', 'whisky', 160.00, 180, 34, 'photo\\Officer\'s Choice Blue.png', '2026-10-20 09:18:38'),
(50, 'Bagpiper Whisky', 'whisky', 250.00, 750, 2, 'photo\\Bagpiper Whisky.png', '2026-10-20 09:19:37'),
(51, '8 PM Whisky', 'whisky', 860.00, 750, 6, 'https://media.monde-selection.com/2025/06/1045664-768x768.png', '2026-10-20 09:23:00'),
(52, 'Romanov Vodka', 'vodka', 780.00, 750, 3, 'photo\\Romanov Vodka.png', '2026-10-20 17:24:56'),
(53, 'Magic Moments', 'vodka', 1120.00, 750, 6, 'https://winepalacegoa.com/wp-content/uploads/2024/07/6.png', '2026-10-20 17:26:06'),
(54, 'White Mischief', 'vodka', 920.00, 750, 4, 'photo\\White Mischief.png', '2026-10-20 17:28:08'),
(55, 'Smirnoff Red', 'vodka', 1700.00, 750, 6, 'https://godrinks.com/cdn/shop/products/GD1096.png?v=1756882522', '2026-10-20 17:29:59'),
(56, 'Eristoff', 'vodka', 1950.00, 750, 8, 'https://d3qs129z4w7sjw.cloudfront.net/wp-content/uploads/2023/10/20145854/FY24_Global_ERISTOFF_ICE_IPX_Page_Product_NewIceCan_WithBottle.png', '2026-10-20 17:31:11'),
(57, 'Magic Moments Verve', 'vodka', 1365.00, 750, 8, 'https://drinksbuff.com/wp-content/uploads/2022/08/m2_magic_moments_verve_vodka-810x1024.png', '2026-10-20 17:32:21'),
(58, 'Absolut Vodka', 'vodka', 2400.00, 750, 5, 'https://sa.adanione.com/-/media/Foundation/Adani/Dutyfree/ProductImages/Liquor/Vodka/absolut/02F01002/absolut-blue-vodka-5cl-02F01002-1.png', '2026-10-20 17:34:09'),
(59, 'Ketel One', 'vodka', 3900.00, 750, 3, 'https://images.ctfassets.net/nhvvc9v3qesf/4gkrjU4wRTv8znUzFxHW9Y/d78b8192bc1346b99a1bfb84eb571cfd/ketel-one-vodka-highlights.png', '2026-10-20 17:36:02'),
(60, 'Grey Goose', 'vodka', 6500.00, 750, 3, 'photo\\Grey Goose.png', '2026-10-20 17:37:25'),
(61, 'Belvedere Vodka', 'vodka', 7400.00, 750, 4, 'https://assets.liquidcommerce.co/catalog/images/10005_(1).png', '2026-10-20 17:38:31'),
(62, 'V21 Green Apple', 'vodka', 620.00, 750, 6, 'photo\\V21 Green Apple.png', '2026-10-20 17:40:00'),
(63, 'Magic Moments Remix Radiant Pink', 'vodka', 880.00, 750, 77, 'photo\\Magic Moments Remix Radiant Pink.png', '2026-10-20 17:42:09'),
(64, 'Moët & Chandon Brut Impérial', 'champagne', 7200.00, 750, 7, 'https://thefinestbubble.com/products/cut-out/1500/moet-and-chandon-brut-imperial-nv-75cl.png', '2026-10-20 17:55:37'),
(65, 'Veuve Clicquot Yellow Label', 'champagne', 8900.00, 750, 3, 'https://www.totalwine.com/dynamic/x220,sq/images/177478060/177478060-1-fr.png', '2026-10-20 17:58:03'),
(66, 'Moët & Chandon Rosé Impérial', 'champagne', 10000.00, 750, 7, 'https://www.totalwine.com/dynamic/x1000,sq/images/91683750/91683750-1-fr.png', '2026-10-20 17:58:57'),
(67, 'Bollinger Special Cuvée Brut', 'champagne', 9000.00, 750, 8, 'https://www.svinando.com/var/cache/img/srv/www/htdocs/giordanoweb/images/catalogo/H600_S4487-b.webp', '2026-10-20 18:00:08'),
(68, 'Dom Pérignon Vintage Brut', 'champagne', 25000.00, 750, 5, 'https://www.totalwine.com/dynamic/x220,sq/images/226518750/226518750-1-fr.png', '2026-10-20 18:01:01'),
(69, 'Louis Roederer Cristal', 'champagne', 35000.00, 750, 5, 'https://thefinestbubble.com/products/cut-out/250/louis-roederer-cristal-2012_2.png', '2026-10-20 18:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 'sandesh wakade', 'sandesh123@gmail.com', '$2y$10$v3bdu3toJWXuAKOHGMCt/.sVTZl5G7Vit2.vJcP1XsYEaGI2iVA8G', '9370520709', '2026-10-17 15:03:32'),
(2, 'harvir varma', 'harvi123@gmail.com', '$2y$10$P8YFY4xYO6EfXyLu/12DEe7.du1oqb6BHpt9l4.yqSEbbUtcBrhCu', '87660111803', '2026-10-18 17:48:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `fk_orders_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
