<?php
// Database configuration

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Use a strong password in production
define('DB_NAME', 'centralautogy');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting for development
// Comment these lines in production environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the character set to utf8mb4
if (!$conn->set_charset("utf8mb4")) {
  printf("Error loading character set utf8mb4: %s\n", $conn->error);
  exit();
}

// Database creation script (first-time setup)
function createDatabaseIfNotExists()
{
  global $conn;

  // Create users table if it doesn't exist
  $sql = "CREATE TABLE IF NOT EXISTS `users` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `first_name` VARCHAR(50) NOT NULL,
      `last_name` VARCHAR(50) NOT NULL,
      `email` VARCHAR(100) NOT NULL UNIQUE,
      `phone` VARCHAR(20) NOT NULL,
      `password` VARCHAR(255) NOT NULL,
      `marketing_consent` TINYINT(1) DEFAULT 0,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `last_login` DATETIME DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

  if (!$conn->query($sql)) {
    echo "Error creating table: " . $conn->error;
  }

  // Create saved_vehicles table if it doesn't exist
  $sql = "CREATE TABLE IF NOT EXISTS `saved_vehicles` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) NOT NULL,
      `vehicle_id` INT(11) NOT NULL,
      `saved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `saved_vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

  if (!$conn->query($sql)) {
    echo "Error creating table: " . $conn->error;
  }

  // Create user_sessions table if it doesn't exist
  $sql = "CREATE TABLE IF NOT EXISTS `user_sessions` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) NOT NULL,
      `session_id` VARCHAR(255) NOT NULL,
      `ip_address` VARCHAR(45) NOT NULL,
      `user_agent` TEXT NOT NULL,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `expires_at` DATETIME NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

  if (!$conn->query($sql)) {
    echo "Error creating table: " . $conn->error;
  }
}

// Uncomment the line below to create database tables on first run
// createDatabaseIfNotExists();
