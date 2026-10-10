-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 04, 2026 at 09:07 AM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payroll_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `accessrights`
--

DROP TABLE IF EXISTS `accessrights`;
CREATE TABLE IF NOT EXISTS `accessrights` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `access_right_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accessrights`
--

INSERT INTO `accessrights` (`id`, `access_right_name`, `record_status`, `created_at`, `updated_at`) VALUES
(2, 'role-user', 'active', '2026-09-24 03:29:31', '2026-09-24 03:29:31'),
(1, 'role-masteradmin', 'active', '2026-09-23 19:10:58', '2026-09-23 19:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `database_backups`
--

DROP TABLE IF EXISTS `database_backups`;
CREATE TABLE IF NOT EXISTS `database_backups` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint UNSIGNED NOT NULL DEFAULT '0',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'backup',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
CREATE TABLE IF NOT EXISTS `licenses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `license_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `licenses_license_key_unique` (`license_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `licenses`
--

INSERT INTO `licenses` (`id`, `license_key`, `company_name`, `email`, `activated_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'NEXSUZ-LIC-2026-ABCD-1234', NULL, NULL, '2026-10-03 13:53:57', '2027-10-03 13:53:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE IF NOT EXISTS `menus` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_routes` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_sort` int DEFAULT '1',
  `record_status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `enterprise` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `standard` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `express` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_enterprise` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_standard` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_express` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `menu_name`, `menu_routes`, `menu_sort`, `record_status`, `enterprise`, `standard`, `express`, `project_enterprise`, `project_standard`, `project_express`, `created_at`, `updated_at`) VALUES
(4, 'System', 'system', 7, 'active', 'yes', 'no', 'no', 'no', 'no', 'no', '2026-09-23 01:01:46', '2026-09-27 21:51:15'),
(9, 'Files', 'files', 1, 'active', 'yes', 'no', 'no', 'no', 'no', 'no', '2026-10-01 07:06:32', '2026-10-02 17:20:29'),
(15, 'Attendance', 'attendance', 2, 'active', 'yes', 'no', 'no', 'no', 'no', 'no', '2026-10-02 07:13:58', '2026-10-02 17:16:16'),
(16, 'OtherIncome', 'otherincome', 3, 'active', 'yes', 'no', 'no', 'no', 'no', 'no', '2026-10-02 07:14:24', '2026-10-02 17:16:34'),
(17, 'OtherDeduction', 'otherdeductions', 4, 'active', 'yes', 'no', 'no', 'no', 'no', 'no', '2026-10-02 07:14:44', '2026-10-02 17:16:53'),
(18, 'Payroll', 'payroll', 6, 'active', 'yes', 'no', 'no', 'no', 'no', 'no', '2026-10-02 07:14:59', '2026-10-02 17:17:22'),
(19, 'Contribution Loans', 'contributionloan', 5, 'active', 'yes', 'no', 'no', 'no', 'no', 'no', '2026-10-02 07:15:27', '2026-10-02 17:17:27');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2026_07_31_052724_create_personal_access_tokens_table', 1),
(3, '2026_07_31_084208_create_profiles_table', 1),
(4, '2026_08_01_030759_create_role_permissions_table', 1),
(5, '2026_08_01_034853_create_menus_table', 1),
(6, '2026_08_01_035103_create_submenus_table', 1),
(7, '2026_08_04_011424_create_todos_table', 1),
(8, '2026_09_24_013438_create_jobs_table', 2),
(9, '2026_09_24_014028_create_accessrights_table', 3),
(10, '2026_09_24_023101_create_rolepermissions_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(48, 'App\\Models\\User', 17, 'auth_token', 'c787b6fc57cbba79ae101665d4ee3da85e5189304f707f8c6c584bb9b94bca12', '[\"*\"]', '2026-09-23 21:23:58', NULL, '2026-09-23 21:23:07', '2026-09-23 21:23:58'),
(47, 'App\\Models\\User', 4, 'auth_token', '58ff73dff4bbe5662dce3b543527a1456a3814002c1b354cd5fb9b96fa92d04b', '[\"*\"]', NULL, NULL, '2026-09-23 19:52:25', '2026-09-23 19:52:25');

-- --------------------------------------------------------

--
-- Table structure for table `rolepermissions`
--

DROP TABLE IF EXISTS `rolepermissions`;
CREATE TABLE IF NOT EXISTS `rolepermissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `access_right_id` bigint UNSIGNED NOT NULL,
  `access_right_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_id` bigint UNSIGNED NOT NULL,
  `menu_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_routes` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_sort` int UNSIGNED NOT NULL DEFAULT '0',
  `sub_menu_id` bigint UNSIGNED DEFAULT NULL,
  `sub_menu_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_menu_sort` int UNSIGNED DEFAULT '1',
  `record_status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=567 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rolepermissions`
--

INSERT INTO `rolepermissions` (`id`, `access_right_id`, `access_right_name`, `menu_id`, `menu_name`, `menu_routes`, `menu_sort`, `sub_menu_id`, `sub_menu_name`, `sub_menu_sort`, `record_status`, `created_at`, `updated_at`) VALUES
(49, 16, 'role-staffs', 9, 'Files', NULL, 1, 12, 'Employees', 1, 'active', '2026-10-02 07:06:01', '2026-10-02 07:06:01'),
(467, 2, 'role-user', 17, 'OtherDeduction', NULL, 4, NULL, NULL, NULL, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(566, 1, 'role-masteradmin', 4, 'System', NULL, 7, 18, 'Database', 5, 'active', '2026-10-03 05:00:06', '2026-10-03 05:00:06'),
(565, 1, 'role-masteradmin', 4, 'System', NULL, 7, 1, 'Menus', 3, 'active', '2026-10-03 05:00:06', '2026-10-03 05:00:06'),
(564, 1, 'role-masteradmin', 4, 'System', NULL, 7, 5, 'Submenus', 2, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(563, 1, 'role-masteradmin', 4, 'System', NULL, 7, 15, 'Users', 1, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(562, 1, 'role-masteradmin', 4, 'System', NULL, 7, 6, 'Manage Permission', 1, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(561, 1, 'role-masteradmin', 9, 'Files', NULL, 6, 12, 'Employees', 2, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(560, 1, 'role-masteradmin', 9, 'Files', NULL, 6, 13, 'Shifts', 1, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(559, 1, 'role-masteradmin', 18, 'Payroll', NULL, 6, NULL, NULL, NULL, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(473, 2, 'role-user', 4, 'System', NULL, 7, 5, 'Submenus', 2, 'inactive', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(472, 2, 'role-user', 4, 'System', NULL, 7, 6, 'Manage Permission', 1, 'inactive', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(471, 2, 'role-user', 9, 'Files', NULL, 6, 12, 'Employees', 2, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(470, 2, 'role-user', 9, 'Files', NULL, 6, 13, 'Shifts', 1, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(558, 1, 'role-masteradmin', 19, 'Contribution Loans', NULL, 5, NULL, NULL, NULL, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(557, 1, 'role-masteradmin', 17, 'OtherDeduction', NULL, 4, NULL, NULL, NULL, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(468, 2, 'role-user', 19, 'Contribution Loans', NULL, 5, NULL, NULL, NULL, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(469, 2, 'role-user', 18, 'Payroll', NULL, 6, NULL, NULL, NULL, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(466, 2, 'role-user', 16, 'OtherIncome', NULL, 3, NULL, NULL, NULL, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(465, 2, 'role-user', 15, 'Attendance', NULL, 2, 14, 'No TimeIn', 1, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(464, 2, 'role-user', 15, 'Attendance', NULL, 2, 17, 'Upload Biometrics', 1, 'active', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(556, 1, 'role-masteradmin', 16, 'OtherIncome', NULL, 3, NULL, NULL, NULL, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(554, 1, 'role-masteradmin', 15, 'Attendance', NULL, 2, 17, 'Upload Biometrics', 1, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05'),
(474, 2, 'role-user', 4, 'System', NULL, 7, 1, 'Menus', 3, 'inactive', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(475, 2, 'role-user', 4, 'System', NULL, 7, 15, 'Users', 5, 'inactive', '2026-10-02 23:32:54', '2026-10-02 23:32:54'),
(555, 1, 'role-masteradmin', 15, 'Attendance', NULL, 2, 14, 'No TimeIn', 1, 'active', '2026-10-03 05:00:05', '2026-10-03 05:00:05');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('iONbymRaOr8N0MPOO63HstAd9M6QzF8bLxEsVc9R', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaWp5c0wzd2QyNWltRU93T1hXcVJMaEpIZDRyQVlmcjMzYUJvcjYxUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1790233266),
('HC4NiPq3XG5WLCpMs4coWUuR7aorNH9dyGO8Qayy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.138.0 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUlUyOWhvYmkyM2xjRVpENVllazNKZFBVZlJ0ejhYdzhVT2YxdkRYciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1790233331);

-- --------------------------------------------------------

--
-- Table structure for table `submenus`
--

DROP TABLE IF EXISTS `submenus`;
CREATE TABLE IF NOT EXISTS `submenus` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `sub_menu_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_menu_routes` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_menu_sort` int DEFAULT NULL,
  `menu_id` bigint UNSIGNED NOT NULL,
  `menu_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_routes` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_menus_sub_menu_name_menu_id_unique` (`sub_menu_name`,`menu_id`),
  KEY `sub_menus_menu_id_foreign` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `submenus`
--

INSERT INTO `submenus` (`id`, `sub_menu_name`, `icon`, `sub_menu_routes`, `sub_menu_sort`, `menu_id`, `menu_name`, `menu_routes`, `record_status`, `created_at`, `updated_at`) VALUES
(1, 'Menus', '-', 'menus', 1, 4, 'System', NULL, 'active', '2026-09-23 02:18:10', '2026-10-02 22:43:47'),
(5, 'Submenus', NULL, 'submenus', 2, 4, 'System', NULL, 'active', '2026-09-23 19:07:10', '2026-10-02 22:43:54'),
(6, 'Manage Permission', '-', 'accessrights', 4, 4, 'System', NULL, 'active', '2026-09-23 19:19:32', '2026-10-02 22:44:14'),
(12, 'Employees', '-', 'employees', 1, 9, 'Files', NULL, 'active', '2026-10-02 06:12:22', '2026-10-02 23:52:10'),
(13, 'Shifts', '-', 'shifts', 2, 9, 'Files', NULL, 'active', '2026-10-02 07:07:02', '2026-10-02 23:52:20'),
(14, 'No TimeIn', '-', 'notimein', 2, 15, 'Attendance', NULL, 'active', '2026-10-02 19:37:37', '2026-10-02 23:29:43'),
(15, 'Users', '-', 'users', 5, 4, 'System', NULL, 'active', '2026-10-02 22:03:06', '2026-10-02 22:44:06'),
(17, 'Upload Biometrics', '-', 'uploadbiometrics', 1, 15, 'Attendance', NULL, 'active', '2026-10-02 23:28:28', '2026-10-02 23:28:28'),
(18, 'Database', '-', 'importdatabase', 6, 4, 'System', NULL, 'active', '2026-10-02 23:50:22', '2026-10-02 23:51:54');

-- --------------------------------------------------------

--
-- Table structure for table `todos`
--

DROP TABLE IF EXISTS `todos`;
CREATE TABLE IF NOT EXISTS `todos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `image_url` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `todos_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `todos`
--

INSERT INTO `todos` (`id`, `user_id`, `title`, `description`, `completed`, `image_url`, `created_at`, `updated_at`) VALUES
(26, 3, 'sdvd', 'svdsv', 0, '/storage/todos/avatar26.jpg', '2026-08-07 17:58:00', '2026-08-07 17:58:00'),
(25, 4, 'sdvs', 'dvsdv', 0, '/storage/todos/avatar25.jpg', '2026-08-07 17:55:35', '2026-08-07 17:55:44'),
(27, 3, 'saca', 'scasc', 0, NULL, '2026-08-07 18:49:12', '2026-08-07 18:49:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_right_id` bigint DEFAULT NULL,
  `access_right_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_code`, `access_right_id`, `access_right_name`, `username`, `email`, `contact`, `address`, `avatar`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, '101', 1, 'role-masteradmin', 'masteradmin', 'codewarrior05.mariosoft@gmail.com', '7499381513', 'Ibabao,Cordova,Cebu CIty', 'users/2/avatar/08e1761d-d352-4353-b986-52d109a2b9f8.png', NULL, '$2y$12$qscafFbln4mDWvdGHkpNIuysMKlgw7XJYeEbf6deRzCbryXq5jsni', NULL, '2026-08-04 01:12:13', '2026-10-03 07:03:48'),
(18, '103', 2, 'role-user', 'staff', 'pedroyorpo22@gmail.com', '09938151373', 'IBABAO, PUROK CENTRO', NULL, NULL, '$2y$12$hNRAMlNmm.csXShw9tjlh.JvErZffWERZjRZDzNsEfo99wLzcIikC', NULL, '2026-09-23 21:21:48', '2026-10-04 00:51:31');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `submenus`
--
ALTER TABLE `submenus`
  ADD CONSTRAINT `sub_menus_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
