-- Admin table for Job Portal
-- Run this in phpMyAdmin to create the admin table

USE `job_portal`;

CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin account
INSERT INTO `admin` (`name`, `email`, `password`) VALUES
('Admin', 'admin@jobportal.com', 'admin123');
