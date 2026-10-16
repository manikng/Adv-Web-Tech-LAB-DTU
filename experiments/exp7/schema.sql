-- =====================================================================
-- Experiment 07: User Authentication using Sessions and Cookies
-- Student: Manish Singh (Roll No: 23/CS/244)
-- Subject: Advanced Web Technologies
-- Target Database: MySQL / MariaDB 8.0+
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `exp7_auth_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `exp7_auth_db`;

DROP TABLE IF EXISTS `auth_logs`;
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(64) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(128) NOT NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'student',
  `department` VARCHAR(64) NOT NULL DEFAULT 'Computer Science & Engineering',
  `avatar_color` VARCHAR(32) NOT NULL DEFAULT '#6366f1',
  `failed_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Persistent Remember-Me Tokens (Split-Token Pattern)
CREATE TABLE `remember_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `selector` VARCHAR(32) NOT NULL,
  `validator_hash` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_selector` (`selector`),
  KEY `idx_user_token` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Authentication & Security Audit Logs
CREATE TABLE `auth_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(64) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Initial Demo Users (Default password: "Password@123")
-- Hash generated using password_hash('Password@123', PASSWORD_BCRYPT)
INSERT INTO `users`
  (`username`, `email`, `password_hash`, `full_name`, `role`, `department`, `avatar_color`)
VALUES
  ('manish', 'manish.singh@lumina.edu', '$2y$10$6J3s9R54g2fBf5sQZfN4/uP1J3z1jA/C9o4wGqY0vR5s0xHqP6iOa', 'Manish Singh', 'admin', 'Computer Science & Engineering', '#6366f1'),
  ('aarav', 'aarav.sharma@lumina.edu', '$2y$10$6J3s9R54g2fBf5sQZfN4/uP1J3z1jA/C9o4wGqY0vR5s0xHqP6iOa', 'Aarav Sharma', 'student', 'Computer Science & Engineering', '#10b981'),
  ('sneha', 'prof.sneha@lumina.edu', '$2y$10$6J3s9R54g2fBf5sQZfN4/uP1J3z1jA/C9o4wGqY0vR5s0xHqP6iOa', 'Prof. Sneha Sen', 'faculty', 'Artificial Intelligence', '#ec4899');
