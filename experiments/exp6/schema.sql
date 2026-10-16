-- =====================================================================
-- Experiment 06: Database Connectivity and CRUD Operations
-- Student: Manish Singh (Roll No: 23/CS/244)
-- Subject: Advanced Web Technologies
-- Target Database: MySQL / MariaDB 8.0+
-- =====================================================================

-- Step 1: Create Database
CREATE DATABASE IF NOT EXISTS `exp6_db` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `exp6_db`;

-- Step 2: Drop table if already exists for clean migration
DROP TABLE IF EXISTS `students`;

-- Step 3: Create students table with strict types and academic metrics
CREATE TABLE `students` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `roll_no` VARCHAR(32) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `department` VARCHAR(64) NOT NULL DEFAULT 'Computer Science & Engineering',
  `semester` VARCHAR(32) NOT NULL DEFAULT 'Semester 5',
  `maths` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `physics` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `cs` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `web_tech` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `english` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `total_marks` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `gpa` DECIMAL(3,1) NOT NULL DEFAULT 0.0,
  `grade` VARCHAR(4) NOT NULL DEFAULT 'F',
  `status` VARCHAR(64) NOT NULL DEFAULT 'Enrolled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Constraints & Indexes
  UNIQUE KEY `uk_student_roll` (`roll_no`),
  KEY `idx_student_email` (`email`),
  KEY `idx_student_semester` (`semester`),
  KEY `idx_student_grade` (`grade`),
  KEY `idx_student_percentage` (`percentage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Seed Initial Benchmark Academic Data
INSERT INTO `students` 
  (`roll_no`, `name`, `email`, `department`, `semester`, `maths`, `physics`, `cs`, `web_tech`, `english`, `total_marks`, `percentage`, `gpa`, `grade`, `status`)
VALUES
  ('23/CS/244', 'Manish Singh', 'manish.singh@lumina.edu', 'Computer Science & Engineering', 'Semester 5', 98.00, 94.00, 99.00, 97.00, 92.00, 480.00, 96.00, 10.0, 'A+', 'Distinction'),
  ('23/CS/101', 'Aarav Sharma', 'aarav.sharma@lumina.edu', 'Computer Science & Engineering', 'Semester 5', 92.00, 88.00, 94.00, 91.00, 89.00, 454.00, 90.80, 10.0, 'A+', 'Distinction'),
  ('23/CS/102', 'Diya Patel', 'diya.patel@lumina.edu', 'Information Technology', 'Semester 5', 85.00, 89.00, 88.00, 92.00, 86.00, 440.00, 88.00, 9.0, 'A', 'First Class with Distinction'),
  ('23/CS/105', 'Rohan Gupta', 'rohan.gupta@lumina.edu', 'Computer Science & Engineering', 'Semester 5', 78.00, 74.00, 82.00, 80.00, 76.00, 390.00, 78.00, 8.0, 'B+', 'First Class'),
  ('23/CS/112', 'Ananya Iyer', 'ananya.iyer@lumina.edu', 'Artificial Intelligence', 'Semester 5', 95.00, 91.00, 96.00, 98.00, 90.00, 470.00, 94.00, 10.0, 'A+', 'Distinction'),
  ('23/CS/118', 'Vikram Malhotra', 'vikram.m@lumina.edu', 'Computer Science & Engineering', 'Semester 5', 68.00, 62.00, 71.00, 65.00, 64.00, 330.00, 66.00, 7.0, 'B', 'Second Class'),
  ('23/CS/124', 'Neha Kapoor', 'neha.k@lumina.edu', 'Information Technology', 'Semester 5', 54.00, 58.00, 52.00, 59.00, 55.00, 278.00, 55.60, 6.0, 'C', 'Pass Class'),
  ('23/CS/130', 'Kabir Sen', 'kabir.sen@lumina.edu', 'Computer Science & Engineering', 'Semester 5', 38.00, 42.00, 35.00, 39.00, 40.00, 194.00, 38.80, 0.0, 'F', 'Remedial Required');
