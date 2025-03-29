-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2025 at 01:42 PM
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
-- Database: `centralautogy`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `subscribe_to_newsletter` (IN `email_address` VARCHAR(100))   BEGIN
    INSERT INTO newsletter_subscribers (email) 
    VALUES (email_address)
    ON DUPLICATE KEY UPDATE 
        status = 'active',
        updated_at = CURRENT_TIMESTAMP;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@centralautogy.com', '$2y$10$KLMPVe7QnVc35w9IlRdRp.6XST/ZrFKGKVXcwpXu0.z9VXM8xPKTC', 'System', 'Admin', 'admin', 1, NULL, '2025-03-27 22:28:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `activity_type`, `activity_details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-27 12:13:42'),
(2, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-27 22:03:14'),
(3, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 10:48:59'),
(4, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 21:23:47'),
(5, 1, 'delete_inquiry', 'Deleted inquiry #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 21:52:18'),
(6, 1, 'delete_inquiry', 'Deleted inquiry #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 21:52:36'),
(7, 1, 'logout', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 21:53:17'),
(8, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 22:02:37'),
(9, 1, 'logout', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 22:37:56'),
(10, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 22:53:24'),
(11, 1, 'logout', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 22:55:49'),
(12, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 23:10:48'),
(13, 1, 'login', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-29 13:57:31'),
(14, 1, 'delete_inquiry', 'Deleted inquiry #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-29 14:08:12');

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_inquiries_view`
-- (See below for the actual view)
--
CREATE TABLE `admin_inquiries_view` (
`id` int(11)
,`name` varchar(100)
,`email` varchar(100)
,`phone` varchar(20)
,`inquiry_type` enum('General Inquiry','Vehicle Information','Other')
,`status` enum('new','in_progress','completed')
,`message_preview` varchar(100)
,`message` text
,`created_at` datetime
,`user_id` int(11)
,`user_name` varchar(101)
);

-- --------------------------------------------------------

--
-- Table structure for table `admin_tokens`
--

CREATE TABLE `admin_tokens` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_tokens`
--

INSERT INTO `admin_tokens` (`id`, `admin_id`, `token`, `created_at`, `expires_at`) VALUES
(1, 1, 'db00202fd544c6c7da0f50597574eee8aabf7766466a5b887db67ad5f00332ba', '2025-03-27 12:13:42', '2025-04-26 12:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','manager','sales_agent') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `first_name`, `last_name`, `role`, `is_active`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$z6rrxes9nwurSZ7PWYtRF.LOnwZ7MSxgRSxgTlGj9JJsXG.IzvoWK', 'centralautogy@admin.com', 'System', 'Administrator', 'admin', 1, '2025-03-27 11:46:18', '2025-03-29 13:57:31', '2025-03-29 13:57:31');

-- --------------------------------------------------------

--
-- Table structure for table `body_types`
--

CREATE TABLE `body_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `body_types`
--

INSERT INTO `body_types` (`id`, `name`, `display_order`, `created_at`) VALUES
(10, 'Van', 1, '2025-03-29 14:51:55'),
(11, 'Wagon', 2, '2025-03-29 14:52:00'),
(12, 'Sedan', 3, '2025-03-29 14:52:07'),
(13, 'Hatchback', 4, '2025-03-29 14:52:13'),
(14, 'SUV', 5, '2025-03-29 14:52:19');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `hex_code` varchar(7) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `hex_code`, `display_order`, `created_at`) VALUES
(15, 'Pearl White', '#F8F8FF', 1, '2025-03-29 14:24:58'),
(16, 'Silver', '#C0C0C0', 2, '2025-03-29 14:25:27'),
(17, 'Blue', '#0000FF', 3, '2025-03-29 14:25:46'),
(18, 'White', '#FFFFFF', 4, '2025-03-29 14:26:06'),
(19, 'Black', '#000000', 5, '2025-03-29 14:26:34'),
(20, 'Grey', '#808080', 6, '2025-03-29 14:27:02');

-- --------------------------------------------------------

--
-- Table structure for table `drive_types`
--

CREATE TABLE `drive_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drive_types`
--

INSERT INTO `drive_types` (`id`, `name`, `display_order`, `created_at`) VALUES
(6, 'FWD', 1, '2025-03-29 14:23:00');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('Safety','Comfort','Technology','Performance','Other') DEFAULT 'Other',
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`id`, `name`, `category`, `display_order`, `created_at`) VALUES
(21, 'Advanced safety features', 'Safety', 1, '2025-03-29 14:47:46'),
(22, 'Spacious cargo area, ideal for commercial use', 'Comfort', 2, '2025-03-29 14:47:59'),
(23, 'Spacious interior', 'Comfort', 3, '2025-03-29 14:48:08'),
(24, 'Comfortable seating', 'Comfort', 4, '2025-03-29 14:48:16'),
(25, 'Comfortable interior', 'Comfort', 5, '2025-03-29 14:48:25'),
(26, 'Suitable for family use', 'Comfort', 6, '2025-03-29 14:48:33'),
(27, 'Elegant design', 'Comfort', 7, '2025-03-29 14:48:43'),
(28, 'Hybrid efficiency', 'Technology', 8, '2025-03-29 14:48:53'),
(29, 'Reliable performance', 'Performance', 9, '2025-03-29 14:49:06'),
(30, 'Fuel-efficient', 'Performance', 10, '2025-03-29 14:49:14'),
(31, 'Perfect for city driving', 'Performance', 11, '2025-03-29 14:49:38'),
(32, 'Stylish design', 'Performance', 12, '2025-03-29 14:49:50'),
(33, 'Compact design', 'Other', 11, '2025-03-29 14:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `financing_applications`
--

CREATE TABLE `financing_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `application_date` datetime DEFAULT current_timestamp(),
  `employment_status` varchar(50) NOT NULL,
  `annual_income` decimal(12,2) NOT NULL,
  `credit_score_range` varchar(20) NOT NULL,
  `down_payment` decimal(10,2) NOT NULL,
  `loan_term` int(3) NOT NULL,
  `status` enum('submitted','under_review','approved','rejected') DEFAULT 'submitted',
  `notes` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fuel_types`
--

CREATE TABLE `fuel_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_types`
--

INSERT INTO `fuel_types` (`id`, `name`, `display_order`, `created_at`) VALUES
(8, 'Regular Gas', 1, '2025-03-29 14:22:14'),
(9, 'Gasoline', 2, '2025-03-29 14:22:25');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `inquiry_type` enum('General Inquiry','Vehicle Information','Other') DEFAULT 'General Inquiry',
  `status` enum('new','in_progress','completed') DEFAULT 'new',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `makes`
--

CREATE TABLE `makes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `makes`
--

INSERT INTO `makes` (`id`, `name`, `display_order`, `created_at`) VALUES
(43, 'Toyota', 1, '2025-03-29 14:51:14'),
(44, 'Honda', 2, '2025-03-29 14:51:24');

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

CREATE TABLE `models` (
  `id` int(11) NOT NULL,
  `make_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`id`, `make_id`, `name`, `display_order`, `created_at`) VALUES
(21, 44, 'Vezel', 1, '2025-03-29 14:52:59'),
(22, 43, 'HiAce Van', 2, '2025-03-29 14:53:10'),
(23, 43, 'Fielder', 3, '2025-03-29 14:53:17'),
(24, 43, 'Corolla Rumion', 4, '2025-03-29 14:53:23'),
(25, 43, 'Allion', 5, '2025-03-29 14:53:30'),
(26, 43, 'Vitz', 6, '2025-03-29 14:53:39'),
(27, 43, 'Axio', 7, '2025-03-29 14:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_vehicles`
--

CREATE TABLE `saved_vehicles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `saved_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_assets`
--

CREATE TABLE `site_assets` (
  `id` int(11) NOT NULL,
  `asset_key` varchar(100) NOT NULL,
  `asset_path` varchar(255) NOT NULL,
  `asset_type` varchar(20) DEFAULT 'image',
  `mime_type` varchar(100) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_assets`
--

INSERT INTO `site_assets` (`id`, `asset_key`, `asset_path`, `asset_type`, `mime_type`, `original_filename`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'footer_logo', 'assets/images/logos/67e6f1fd2a54e.png', 'image', 'image/png', 'svgviewer-png-output (1).png', 'footer_logo', '', '2025-03-28 23:46:55', '2025-03-29 00:01:17'),
(4, 'navbar_logo', 'assets/images/logos/67e6f1f8435cd.png', 'image', 'image/png', 'svgviewer-png-output (1).png', 'navbar_logo', '', '2025-03-28 23:48:15', '2025-03-29 00:01:12'),
(5, 'favicon', 'assets/images/67e6f57908d31.png', 'image', 'image/png', 'blue-favicon.png', 'favicon', '', '2025-03-28 23:48:37', '2025-03-29 00:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `setting_type` varchar(20) DEFAULT 'text',
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `setting_type`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'CentralAutogy', 'general', 'text', 'Site Name', 'The name of the website', '2025-03-28 23:04:17', '2025-03-28 23:16:42'),
(2, 'site_tagline', 'Find Your Perfect Car', 'general', 'text', 'Site Tagline', 'A short description of the website', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(3, 'contact_email', 'info@centralautogy.com', 'contact', 'email', 'Contact Email', 'Primary contact email address', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(4, 'contact_phone', '(800) 123-4567', 'contact', 'text', 'Contact Phone', 'Primary contact phone number', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(5, 'contact_address', '123 Central Avenue, Autogy City, CA 90210', 'contact', 'textarea', 'Contact Address', 'Business address', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(6, 'social_facebook', '', 'social', 'url', 'Facebook URL', 'Facebook page URL', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(7, 'social_twitter', '', 'social', 'url', 'Twitter URL', 'Twitter account URL', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(8, 'social_instagram', '', 'social', 'url', 'Instagram URL', 'Instagram account URL', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(9, 'social_linkedin', '', 'social', 'url', 'LinkedIn URL', 'LinkedIn page URL', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(10, 'footer_text', '© 2025 CentralAutogy. All rights reserved. Developed By AGR SOFT', 'general', 'textarea', 'Footer Text', 'Text to display in the footer', '2025-03-28 23:04:17', '2025-03-29 00:10:51'),
(11, 'privacy_policy', '', 'legal', 'textarea', 'Privacy Policy', 'Privacy policy content', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(12, 'terms_conditions', '', 'legal', 'textarea', 'Terms and Conditions', 'Terms and conditions content', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(13, 'google_analytics_id', '', 'integrations', 'text', 'Google Analytics ID', 'Google Analytics tracking ID', '2025-03-28 23:04:17', '2025-03-28 23:04:17'),
(14, 'site_title', 'CentralAutogy', 'general', 'text', 'Site Title', 'The name of the website', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(15, 'site_description', 'Your trusted auto inventory management system', 'general', 'textarea', 'Site Description', 'A brief description of the website', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(18, 'business_address', '123 Auto Street, Car City, CC 12345', 'general', 'textarea', 'Business Address', 'Physical address of the business', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(19, 'facebook_url', 'https://facebook.com/centralautogy', 'social', 'url', 'Facebook URL', 'Facebook page URL', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(20, 'twitter_url', 'https://twitter.com/centralautogy', 'social', 'url', 'Twitter URL', 'Twitter profile URL', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(21, 'instagram_url', 'https://instagram.com/centralautogy', 'social', 'url', 'Instagram URL', 'Instagram profile URL', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(22, 'linkedin_url', 'https://linkedin.com/company/centralautogy', 'social', 'url', 'LinkedIn URL', 'LinkedIn company page URL', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(23, 'meta_keywords', 'auto inventory, car dealers, vehicle management', 'seo', 'textarea', 'Meta Keywords', 'Keywords for SEO', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(24, 'meta_description', 'CentralAutogy - The complete auto inventory management system for car dealers', 'seo', 'textarea', 'Meta Description', 'Description for SEO', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(26, 'business_hours', 'Monday-Friday: 9am-6pm, Saturday: 10am-4pm, Sunday: Closed', 'business', 'textarea', 'Business Hours', 'Regular business hours', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(27, 'currency_symbol', '$', 'business', 'text', 'Currency Symbol', 'Symbol used for prices', '2025-03-28 23:08:56', '2025-03-28 23:08:56'),
(28, 'tax_rate', '7.5', 'business', 'number', 'Tax Rate (%)', 'Default tax rate percentage', '2025-03-28 23:08:56', '2025-03-28 23:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `test_drives`
--

CREATE TABLE `test_drives` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `status` enum('requested','confirmed','completed','canceled') DEFAULT 'requested',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transmission_types`
--

CREATE TABLE `transmission_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transmission_types`
--

INSERT INTO `transmission_types` (`id`, `name`, `display_order`, `created_at`) VALUES
(6, 'Automatic', 1, '2025-03-29 14:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `marketing_consent` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `marketing_consent`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'test', 'test', 'test@test.com', '03196977218', '$2y$10$Nk1yRTLrtGxgfauNuWeyau6U1CLDk/WWv0GqhqamZYD41yr8LiJ72', 1, '2025-03-27 06:18:48', '2025-03-29 14:08:46', '2025-03-29 14:08:46');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `ip_address`, `user_agent`, `created_at`, `expires_at`) VALUES
(1, 1, 'kch22teplal9bq18m0499bl2c7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 14:17:52', '2025-03-28 16:17:52'),
(2, 1, 'dh9iqij2g6pdc8d12hb1e6rohi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 18:48:43', '2025-03-28 20:48:43'),
(3, 1, 'dh9iqij2g6pdc8d12hb1e6rohi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 21:53:45', '2025-03-28 23:53:45'),
(4, 1, 'dh9iqij2g6pdc8d12hb1e6rohi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-28 22:47:03', '2025-03-29 00:47:03'),
(5, 1, 'dh9iqij2g6pdc8d12hb1e6rohi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-29 00:06:58', '2025-03-29 02:06:58'),
(6, 1, 'lnobc32ddnbd05s1ith4kk1ag1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-29 14:08:46', '2025-03-29 16:08:46');

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tokens`
--

INSERT INTO `user_tokens` (`id`, `user_id`, `token`, `created_at`, `expires_at`) VALUES
(1, 1, 'fa1f10622de806878fcae37b87d4aba7d4daa6ca6d4213975e4e81d82fcb482b', '2025-03-28 14:17:52', '2025-04-27 14:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `mileage` int(11) NOT NULL,
  `color` varchar(30) NOT NULL,
  `vin` varchar(17) NOT NULL,
  `condition` varchar(20) NOT NULL,
  `body_style` varchar(30) NOT NULL,
  `transmission` varchar(20) NOT NULL,
  `fuel_type` varchar(20) NOT NULL,
  `engine` varchar(50) NOT NULL,
  `drivetrain` varchar(20) NOT NULL,
  `exterior_color` varchar(30) NOT NULL,
  `interior_color` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('available','sold','pending','reserved','in transit') DEFAULT 'available',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `make`, `model`, `year`, `price`, `mileage`, `color`, `vin`, `condition`, `body_style`, `transmission`, `fuel_type`, `engine`, `drivetrain`, `exterior_color`, `interior_color`, `description`, `featured`, `status`, `created_at`, `updated_at`) VALUES
(20, 'Toyota', 'HiAce Van', 2015, NULL, 135000, 'Pearl White', '0', 'New', 'Van', 'Automatic', 'Regular Gas', '1,990 cc', 'FWD', 'Pearl White', 'Grey', '', 0, 'available', '2025-03-29 16:25:45', '2025-03-29 16:25:45'),
(23, 'Toyota', 'Fielder', 2018, NULL, 36000, 'Grey', '1', 'New', 'Wagon', 'Automatic', 'Gasoline', '1,500 cc', 'FWD', 'Black', 'Silver', '', 0, 'available', '2025-03-29 16:32:30', '2025-03-29 16:35:23'),
(24, 'Toyota', 'Corolla Rumion', 2015, NULL, 90000, 'Blue', '2', 'New', 'Hatchback', 'Automatic', 'Gasoline', '1,500 cc', 'FWD', 'Blue', 'Grey', '', 0, 'available', '2025-03-29 16:38:53', '2025-03-29 16:38:53'),
(25, 'Toyota', 'Fielder', 2015, NULL, 90000, 'White', '3', 'New', 'Wagon', 'Automatic', 'Gasoline', '1,500 cc', 'FWD', 'White', 'Black', '', 0, 'available', '2025-03-29 16:44:17', '2025-03-29 16:44:17'),
(26, 'Toyota', 'Allion', 2017, NULL, 19000, 'Black', '4', 'New', 'Sedan', 'Automatic', 'Gasoline', '1,500 cc', 'FWD', 'Black', 'Grey', '', 0, 'available', '2025-03-29 16:49:07', '2025-03-29 16:49:07'),
(27, 'Toyota', 'Vitz', 2015, NULL, 93000, 'Pearl White', '5', 'New', 'Hatchback', 'Automatic', 'Gasoline', '1,300 cc', 'FWD', 'Pearl White', 'Grey', '', 0, 'available', '2025-03-29 16:55:41', '2025-03-29 16:55:41'),
(28, 'Honda', 'Vezel', 2016, NULL, 77000, 'Black', '6', 'New', 'SUV', 'Automatic', 'Gasoline', '1,500 cc', 'FWD', 'Black', 'Black', '', 0, 'available', '2025-03-29 17:00:42', '2025-03-29 17:05:51'),
(29, 'Honda', 'Vezel', 2016, NULL, 77000, 'Pearl White', '7', 'New', 'SUV', 'Automatic', 'Regular Gas', '1,490 cc', 'FWD', 'Pearl White', 'Grey', '', 0, 'available', '2025-03-29 17:16:14', '2025-03-29 17:16:14'),
(30, 'Toyota', 'Axio', 2014, NULL, 28000, 'White', '8', 'New', 'Sedan', 'Automatic', 'Gasoline', '1,500 cc', 'FWD', 'White', 'Grey', '', 0, 'available', '2025-03-29 17:19:37', '2025-03-29 17:19:37');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_features`
--

CREATE TABLE `vehicle_features` (
  `vehicle_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_features`
--

INSERT INTO `vehicle_features` (`vehicle_id`, `feature_id`) VALUES
(20, 22),
(20, 29),
(23, 21),
(23, 22),
(23, 30),
(24, 29),
(24, 33),
(25, 22),
(25, 26),
(26, 21),
(26, 25),
(26, 27),
(27, 30),
(27, 31),
(28, 22),
(28, 28),
(29, 21),
(29, 22),
(29, 23),
(29, 24),
(29, 25),
(29, 26),
(29, 27),
(29, 28),
(29, 29),
(29, 30),
(29, 31),
(29, 32),
(29, 33),
(30, 21),
(30, 24),
(30, 30);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_images`
--

CREATE TABLE `vehicle_images` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_images`
--

INSERT INTO `vehicle_images` (`id`, `vehicle_id`, `image_path`, `is_primary`, `created_at`) VALUES
(35, 20, 'uploads/vehicles/20/vehicle_67e7d8b92e955.jpeg', 1, '2025-03-29 16:25:45'),
(36, 20, 'uploads/vehicles/20/vehicle_67e7d8b9341a1.jpeg', 0, '2025-03-29 16:25:45'),
(37, 20, 'uploads/vehicles/20/vehicle_67e7d8b935946.jpeg', 0, '2025-03-29 16:25:45'),
(38, 20, 'uploads/vehicles/20/vehicle_67e7d8b9371c1.jpeg', 0, '2025-03-29 16:25:45'),
(39, 20, 'uploads/vehicles/20/vehicle_67e7d8b938a53.jpeg', 0, '2025-03-29 16:25:45'),
(40, 20, 'uploads/vehicles/20/vehicle_67e7d8b939fda.jpeg', 0, '2025-03-29 16:25:45'),
(41, 20, 'uploads/vehicles/20/vehicle_67e7d8b93b837.jpeg', 0, '2025-03-29 16:25:45'),
(42, 20, 'uploads/vehicles/20/vehicle_67e7d8b93d027.jpeg', 0, '2025-03-29 16:25:45'),
(43, 20, 'uploads/vehicles/20/vehicle_67e7d8b93e725.jpeg', 0, '2025-03-29 16:25:45'),
(44, 20, 'uploads/vehicles/20/vehicle_67e7d8b9404e6.jpeg', 0, '2025-03-29 16:25:45'),
(45, 20, 'uploads/vehicles/20/vehicle_67e7d8b941e4e.jpeg', 0, '2025-03-29 16:25:45'),
(46, 20, 'uploads/vehicles/20/vehicle_67e7d8b94379e.jpeg', 0, '2025-03-29 16:25:45'),
(47, 20, 'uploads/vehicles/20/vehicle_67e7d8b9452af.jpeg', 0, '2025-03-29 16:25:45'),
(48, 20, 'uploads/vehicles/20/vehicle_67e7d8b9469b0.jpeg', 0, '2025-03-29 16:25:45'),
(49, 20, 'uploads/vehicles/20/vehicle_67e7d8b948a2d.jpeg', 0, '2025-03-29 16:25:45'),
(50, 20, 'uploads/vehicles/20/vehicle_67e7d8b94a1f9.jpeg', 0, '2025-03-29 16:25:45'),
(51, 20, 'uploads/vehicles/20/vehicle_67e7d8b94bb8a.jpeg', 0, '2025-03-29 16:25:45'),
(52, 20, 'uploads/vehicles/20/vehicle_67e7d8b94d4c6.jpeg', 0, '2025-03-29 16:25:45'),
(53, 23, 'uploads/vehicles/23/vehicle_67e7da7ea15c1.jpeg', 0, '2025-03-29 16:33:18'),
(54, 23, 'uploads/vehicles/23/vehicle_67e7da7ea33ad.jpeg', 0, '2025-03-29 16:33:18'),
(55, 23, 'uploads/vehicles/23/vehicle_67e7da7ea4c68.jpeg', 0, '2025-03-29 16:33:18'),
(56, 23, 'uploads/vehicles/23/vehicle_67e7da7ea65a5.jpeg', 0, '2025-03-29 16:33:18'),
(57, 23, 'uploads/vehicles/23/vehicle_67e7da7ea8022.jpeg', 0, '2025-03-29 16:33:18'),
(58, 23, 'uploads/vehicles/23/vehicle_67e7da7ea99b5.jpeg', 0, '2025-03-29 16:33:18'),
(59, 23, 'uploads/vehicles/23/vehicle_67e7da7eab6c4.jpeg', 0, '2025-03-29 16:33:18'),
(60, 23, 'uploads/vehicles/23/vehicle_67e7da7ead66c.jpeg', 0, '2025-03-29 16:33:18'),
(61, 23, 'uploads/vehicles/23/vehicle_67e7da7eaeff8.jpeg', 0, '2025-03-29 16:33:18'),
(62, 23, 'uploads/vehicles/23/vehicle_67e7da7eb0733.jpeg', 0, '2025-03-29 16:33:18'),
(63, 24, 'uploads/vehicles/24/vehicle_67e7dbcdac907.jpeg', 1, '2025-03-29 16:38:53'),
(64, 24, 'uploads/vehicles/24/vehicle_67e7dbcdb36fc.jpeg', 0, '2025-03-29 16:38:53'),
(65, 24, 'uploads/vehicles/24/vehicle_67e7dbcdb5583.jpeg', 0, '2025-03-29 16:38:53'),
(66, 25, 'uploads/vehicles/25/vehicle_67e7dd114f4d9.jpeg', 1, '2025-03-29 16:44:17'),
(67, 25, 'uploads/vehicles/25/vehicle_67e7dd115525a.jpeg', 0, '2025-03-29 16:44:17'),
(68, 25, 'uploads/vehicles/25/vehicle_67e7dd115a161.jpeg', 0, '2025-03-29 16:44:17'),
(69, 25, 'uploads/vehicles/25/vehicle_67e7dd115c473.jpeg', 0, '2025-03-29 16:44:17'),
(70, 25, 'uploads/vehicles/25/vehicle_67e7dd115eab3.jpeg', 0, '2025-03-29 16:44:17'),
(71, 25, 'uploads/vehicles/25/vehicle_67e7dd1160bf4.jpeg', 0, '2025-03-29 16:44:17'),
(72, 25, 'uploads/vehicles/25/vehicle_67e7dd116377e.jpeg', 0, '2025-03-29 16:44:17'),
(73, 25, 'uploads/vehicles/25/vehicle_67e7dd11657c6.jpeg', 0, '2025-03-29 16:44:17'),
(74, 25, 'uploads/vehicles/25/vehicle_67e7dd1167d93.jpeg', 0, '2025-03-29 16:44:17'),
(75, 25, 'uploads/vehicles/25/vehicle_67e7dd116a109.jpeg', 0, '2025-03-29 16:44:17'),
(76, 25, 'uploads/vehicles/25/vehicle_67e7dd116c89e.jpeg', 0, '2025-03-29 16:44:17'),
(77, 25, 'uploads/vehicles/25/vehicle_67e7dd116ef06.jpeg', 0, '2025-03-29 16:44:17'),
(78, 25, 'uploads/vehicles/25/vehicle_67e7dd1171289.jpeg', 0, '2025-03-29 16:44:17'),
(79, 25, 'uploads/vehicles/25/vehicle_67e7dd11737f0.jpeg', 0, '2025-03-29 16:44:17'),
(80, 25, 'uploads/vehicles/25/vehicle_67e7dd1175838.jpeg', 0, '2025-03-29 16:44:17'),
(81, 26, 'uploads/vehicles/26/vehicle_67e7de3317143.jpeg', 1, '2025-03-29 16:49:07'),
(82, 26, 'uploads/vehicles/26/vehicle_67e7de331de47.jpeg', 0, '2025-03-29 16:49:07'),
(83, 26, 'uploads/vehicles/26/vehicle_67e7de3324e63.jpeg', 0, '2025-03-29 16:49:07'),
(84, 26, 'uploads/vehicles/26/vehicle_67e7de3326e4c.jpeg', 0, '2025-03-29 16:49:07'),
(85, 26, 'uploads/vehicles/26/vehicle_67e7de332934b.jpeg', 0, '2025-03-29 16:49:07'),
(86, 26, 'uploads/vehicles/26/vehicle_67e7de332b3ea.jpeg', 0, '2025-03-29 16:49:07'),
(87, 26, 'uploads/vehicles/26/vehicle_67e7de332ddb9.jpeg', 0, '2025-03-29 16:49:07'),
(88, 26, 'uploads/vehicles/26/vehicle_67e7de332ff6b.jpeg', 0, '2025-03-29 16:49:07'),
(89, 26, 'uploads/vehicles/26/vehicle_67e7de333252c.jpeg', 0, '2025-03-29 16:49:07'),
(90, 26, 'uploads/vehicles/26/vehicle_67e7de33345cb.jpeg', 0, '2025-03-29 16:49:07'),
(91, 26, 'uploads/vehicles/26/vehicle_67e7de3336b83.jpeg', 0, '2025-03-29 16:49:07'),
(92, 26, 'uploads/vehicles/26/vehicle_67e7de3339208.jpeg', 0, '2025-03-29 16:49:07'),
(93, 26, 'uploads/vehicles/26/vehicle_67e7de333b5fb.jpeg', 0, '2025-03-29 16:49:07'),
(94, 26, 'uploads/vehicles/26/vehicle_67e7de333d9da.jpeg', 0, '2025-03-29 16:49:07'),
(95, 26, 'uploads/vehicles/26/vehicle_67e7de333fbb4.jpeg', 0, '2025-03-29 16:49:07'),
(96, 26, 'uploads/vehicles/26/vehicle_67e7de334255f.jpeg', 0, '2025-03-29 16:49:07'),
(97, 26, 'uploads/vehicles/26/vehicle_67e7de3344643.jpeg', 0, '2025-03-29 16:49:07'),
(98, 26, 'uploads/vehicles/26/vehicle_67e7de3346d7d.jpeg', 0, '2025-03-29 16:49:07'),
(99, 27, 'uploads/vehicles/27/vehicle_67e7dfbdb113e.jpeg', 1, '2025-03-29 16:55:41'),
(100, 27, 'uploads/vehicles/27/vehicle_67e7dfbdb8b38.jpeg', 0, '2025-03-29 16:55:41'),
(101, 27, 'uploads/vehicles/27/vehicle_67e7dfbdbb18a.jpeg', 0, '2025-03-29 16:55:41'),
(106, 28, 'uploads/vehicles/28/vehicle_67e7e21f88191.jpg', 0, '2025-03-29 17:05:51'),
(107, 28, 'uploads/vehicles/28/vehicle_67e7e21f8c26d.jpg', 0, '2025-03-29 17:05:51'),
(108, 28, 'uploads/vehicles/28/vehicle_67e7e21f8ed28.jpg', 0, '2025-03-29 17:05:51'),
(109, 28, 'uploads/vehicles/28/vehicle_67e7e21f90be4.jpg', 0, '2025-03-29 17:05:51'),
(110, 28, 'uploads/vehicles/28/vehicle_67e7e21f9320e.jpg', 0, '2025-03-29 17:05:51'),
(111, 28, 'uploads/vehicles/28/vehicle_67e7e21f951cb.jpg', 0, '2025-03-29 17:05:51'),
(112, 28, 'uploads/vehicles/28/vehicle_67e7e21f97491.jpg', 0, '2025-03-29 17:05:51'),
(113, 28, 'uploads/vehicles/28/vehicle_67e7e21f993f5.jpg', 0, '2025-03-29 17:05:51'),
(114, 28, 'uploads/vehicles/28/vehicle_67e7e21f9b543.jpg', 0, '2025-03-29 17:05:51'),
(115, 28, 'uploads/vehicles/28/vehicle_67e7e21f9d4e8.jpg', 0, '2025-03-29 17:05:51'),
(116, 28, 'uploads/vehicles/28/vehicle_67e7e21f9f91b.jpg', 0, '2025-03-29 17:05:51'),
(117, 29, 'uploads/vehicles/29/vehicle_67e7e48e0a8f4.jpeg', 1, '2025-03-29 17:16:14'),
(118, 29, 'uploads/vehicles/29/vehicle_67e7e48e12281.jpeg', 0, '2025-03-29 17:16:14'),
(119, 29, 'uploads/vehicles/29/vehicle_67e7e48e1809d.jpeg', 0, '2025-03-29 17:16:14'),
(120, 29, 'uploads/vehicles/29/vehicle_67e7e48e1a92c.jpeg', 0, '2025-03-29 17:16:14'),
(121, 29, 'uploads/vehicles/29/vehicle_67e7e48e1d28f.jpeg', 0, '2025-03-29 17:16:14'),
(122, 29, 'uploads/vehicles/29/vehicle_67e7e48e1f569.jpeg', 0, '2025-03-29 17:16:14'),
(123, 29, 'uploads/vehicles/29/vehicle_67e7e48e21e10.jpeg', 0, '2025-03-29 17:16:14'),
(124, 29, 'uploads/vehicles/29/vehicle_67e7e48e24110.jpeg', 0, '2025-03-29 17:16:14'),
(125, 29, 'uploads/vehicles/29/vehicle_67e7e48e2660f.jpeg', 0, '2025-03-29 17:16:14'),
(126, 29, 'uploads/vehicles/29/vehicle_67e7e48e2940e.jpeg', 0, '2025-03-29 17:16:14'),
(127, 29, 'uploads/vehicles/29/vehicle_67e7e48e2b3b7.jpeg', 0, '2025-03-29 17:16:14'),
(128, 29, 'uploads/vehicles/29/vehicle_67e7e48e2d9a7.jpeg', 0, '2025-03-29 17:16:14'),
(129, 29, 'uploads/vehicles/29/vehicle_67e7e48e2fa48.jpeg', 0, '2025-03-29 17:16:14'),
(130, 29, 'uploads/vehicles/29/vehicle_67e7e48e31da1.jpeg', 0, '2025-03-29 17:16:14'),
(131, 30, 'uploads/vehicles/30/vehicle_67e7e5596a712.jpeg', 1, '2025-03-29 17:19:37'),
(132, 30, 'uploads/vehicles/30/vehicle_67e7e5596fd23.jpeg', 0, '2025-03-29 17:19:37'),
(133, 30, 'uploads/vehicles/30/vehicle_67e7e559726e4.jpeg', 0, '2025-03-29 17:19:37'),
(134, 30, 'uploads/vehicles/30/vehicle_67e7e55977972.jpeg', 0, '2025-03-29 17:19:37'),
(135, 30, 'uploads/vehicles/30/vehicle_67e7e55979c49.jpeg', 0, '2025-03-29 17:19:37'),
(136, 30, 'uploads/vehicles/30/vehicle_67e7e5597b7c2.jpeg', 0, '2025-03-29 17:19:37'),
(137, 30, 'uploads/vehicles/30/vehicle_67e7e5597dda6.jpeg', 0, '2025-03-29 17:19:37'),
(138, 30, 'uploads/vehicles/30/vehicle_67e7e5597fcbf.jpeg', 0, '2025-03-29 17:19:37'),
(139, 30, 'uploads/vehicles/30/vehicle_67e7e55982032.jpeg', 0, '2025-03-29 17:19:37'),
(140, 30, 'uploads/vehicles/30/vehicle_67e7e55984158.jpeg', 0, '2025-03-29 17:19:37'),
(141, 30, 'uploads/vehicles/30/vehicle_67e7e55986344.jpeg', 0, '2025-03-29 17:19:37'),
(142, 30, 'uploads/vehicles/30/vehicle_67e7e5598827b.jpeg', 0, '2025-03-29 17:19:37');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_inquiries`
--

CREATE TABLE `vehicle_inquiries` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `contact_method` enum('email','phone','text') NOT NULL,
  `message` text DEFAULT NULL,
  `terms_agreed` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('New','In Progress','Contacted','Closed') NOT NULL DEFAULT 'New',
  `submitted_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_status`
--

CREATE TABLE `vehicle_status` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `css_class` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_status`
--

INSERT INTO `vehicle_status` (`id`, `name`, `css_class`, `display_order`, `created_at`) VALUES
(1, 'Available', 'bg-green-100 text-green-800', 1, '2025-03-27 12:42:02'),
(2, 'In Transit', 'bg-amber-100 text-amber-800', 2, '2025-03-27 12:42:02'),
(3, 'Sold', 'bg-purple-100 text-purple-800', 3, '2025-03-27 12:42:02'),
(4, 'Reserved', 'bg-blue-100 text-blue-800', 4, '2025-03-27 12:42:02'),
(5, 'Pending', 'bg-gray-100 text-gray-800', 5, '2025-03-27 12:42:02');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_recent_inquiries`
-- (See below for the actual view)
--
CREATE TABLE `view_recent_inquiries` (
`id` int(11)
,`name` varchar(100)
,`email` varchar(100)
,`phone` varchar(20)
,`inquiry_type` enum('General Inquiry','Vehicle Information','Other')
,`status` enum('new','in_progress','completed')
,`message_preview` varchar(100)
,`created_at` datetime
,`user_id` int(11)
,`user_name` varchar(101)
);

-- --------------------------------------------------------

--
-- Structure for view `admin_inquiries_view`
--
DROP TABLE IF EXISTS `admin_inquiries_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_inquiries_view`  AS SELECT `i`.`id` AS `id`, `i`.`name` AS `name`, `i`.`email` AS `email`, `i`.`phone` AS `phone`, `i`.`inquiry_type` AS `inquiry_type`, `i`.`status` AS `status`, substr(`i`.`message`,1,100) AS `message_preview`, `i`.`message` AS `message`, `i`.`created_at` AS `created_at`, `u`.`id` AS `user_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `user_name` FROM (`inquiries` `i` left join `users` `u` on(`i`.`user_id` = `u`.`id`)) ORDER BY `i`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `view_recent_inquiries`
--
DROP TABLE IF EXISTS `view_recent_inquiries`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_recent_inquiries`  AS SELECT `i`.`id` AS `id`, `i`.`name` AS `name`, `i`.`email` AS `email`, `i`.`phone` AS `phone`, `i`.`inquiry_type` AS `inquiry_type`, `i`.`status` AS `status`, left(`i`.`message`,100) AS `message_preview`, `i`.`created_at` AS `created_at`, `u`.`id` AS `user_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `user_name` FROM (`inquiries` `i` left join `users` `u` on(`i`.`user_id` = `u`.`id`)) ORDER BY `i`.`created_at` DESC LIMIT 0, 100 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `activity_type` (`activity_type`);

--
-- Indexes for table `admin_tokens`
--
ALTER TABLE `admin_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `body_types`
--
ALTER TABLE `body_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `drive_types`
--
ALTER TABLE `drive_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `financing_applications`
--
ALTER TABLE `financing_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `fuel_types`
--
ALTER TABLE `fuel_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `makes`
--
ALTER TABLE `makes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `make_id` (`make_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `saved_vehicles`
--
ALTER TABLE `saved_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_vehicle` (`user_id`,`vehicle_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `site_assets`
--
ALTER TABLE `site_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_key` (`asset_key`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `test_drives`
--
ALTER TABLE `test_drives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `transmission_types`
--
ALTER TABLE `transmission_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vin` (`vin`),
  ADD KEY `idx_make_model` (`make`,`model`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`);

--
-- Indexes for table `vehicle_features`
--
ALTER TABLE `vehicle_features`
  ADD PRIMARY KEY (`vehicle_id`,`feature_id`),
  ADD KEY `feature_id` (`feature_id`);

--
-- Indexes for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `vehicle_inquiries`
--
ALTER TABLE `vehicle_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vehicle_status`
--
ALTER TABLE `vehicle_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `admin_tokens`
--
ALTER TABLE `admin_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `body_types`
--
ALTER TABLE `body_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `drive_types`
--
ALTER TABLE `drive_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `financing_applications`
--
ALTER TABLE `financing_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fuel_types`
--
ALTER TABLE `fuel_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `makes`
--
ALTER TABLE `makes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_vehicles`
--
ALTER TABLE `saved_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `site_assets`
--
ALTER TABLE `site_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `test_drives`
--
ALTER TABLE `test_drives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transmission_types`
--
ALTER TABLE `transmission_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `vehicle_inquiries`
--
ALTER TABLE `vehicle_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vehicle_status`
--
ALTER TABLE `vehicle_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_tokens`
--
ALTER TABLE `admin_tokens`
  ADD CONSTRAINT `admin_tokens_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financing_applications`
--
ALTER TABLE `financing_applications`
  ADD CONSTRAINT `financing_app_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `financing_app_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inquiries_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `models`
--
ALTER TABLE `models`
  ADD CONSTRAINT `models_ibfk_1` FOREIGN KEY (`make_id`) REFERENCES `makes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_vehicles`
--
ALTER TABLE `saved_vehicles`
  ADD CONSTRAINT `saved_vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_vehicles_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_drives`
--
ALTER TABLE `test_drives`
  ADD CONSTRAINT `test_drives_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_drives_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_features`
--
ALTER TABLE `vehicle_features`
  ADD CONSTRAINT `vehicle_features_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_features_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD CONSTRAINT `vehicle_images_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_inquiries`
--
ALTER TABLE `vehicle_inquiries`
  ADD CONSTRAINT `vehicle_inquiries_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `vehicle_inquiries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
