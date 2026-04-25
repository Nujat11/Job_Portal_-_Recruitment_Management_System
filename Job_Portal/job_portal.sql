-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 07:49 AM
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
-- Database: `job_portal`
--

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `application_tracking`, `company`, `employer`, `interview`, `job_posting`, `job_seeker`, `resume`, `notification`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application_tracking` (
  `application_id` int(11) NOT NULL,
  `job_seeker_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `resume_id` int(11) DEFAULT NULL,
  `status` enum('Applied','Shortlisted','Rejected','Hired') DEFAULT 'Applied',
  `apply_date` timestamp DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application_tracking` (`application_id`, `job_seeker_id`, `job_id`, `resume_id`, `status`, `apply_date`) VALUES
(6, 1, 1, 1, 'Applied', '2026-03-20'),
(7, 2, 2, 2, 'Shortlisted', '2026-03-21'),
(8, 3, 3, 3, 'Applied', '2026-03-22'),
(9, 4, 1, 4, 'Rejected', '2026-03-23'),
(10, 5, 3, 5, 'Hired', '2026-03-24');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `name`, `city`, `street`, `industry`) VALUES
(1, 'Brain Station 23', 'Dhaka', 'Mohakhali', 'Software Development'),
(2, 'Tiger IT', 'Dhaka', 'Gulshan', 'Information Technology'),
(3, 'Grameenphone', 'Dhaka', 'Bashundhara', 'Telecommunications'),
(4, 'Square Group', 'Gazipur', 'Salna', 'Manufacturing'),
(5, 'Pathao', 'Dhaka', 'Banani', 'Logistics & Tech');

-- --------------------------------------------------------

--
-- Table structure for table `employer`
--

CREATE TABLE `employer` (
  `employer_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer`
--

INSERT INTO `employer` (`employer_id`, `name`, `email`, `password`, `company_id`) VALUES
(1, 'Anisur Rahman', 'anis@brainstation.com', 'pass_anis_123', 1),
(2, 'Sajid Hossain', 'sajid@tigerit.com', 'tiger_secure_456', 2),
(3, 'Farhana Yasmin', 'farhana@gp.com.bd', 'gp_admin_789', 3),
(4, 'Kamrul Islam', 'kamrul@square.com', 'square_pass_321', 4),
(5, 'Nusrat Jahan', 'nusrat@pathao.com', 'pathao_hr_654', 5);

-- --------------------------------------------------------

--
-- Table structure for table `interview`
--

CREATE TABLE `interview` (
  `interview_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `interview_date` datetime DEFAULT NULL,
  `interview_type` enum('Online','Offline') DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled','Selected','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interview`
--

INSERT INTO `interview` (`interview_id`, `application_id`, `interview_date`, `interview_type`, `status`) VALUES
(1, 6, '2026-04-01 10:00:00', 'Online', 'Scheduled'),
(2, 7, '2026-04-02 11:30:00', 'Offline', 'Scheduled'),
(3, 8, '2026-04-03 14:00:00', 'Online', 'Completed'),
(4, 9, '2026-04-04 09:00:00', 'Online', 'Cancelled'),
(5, 10, '2026-04-05 16:00:00', 'Offline', 'Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `job_posting`
--

CREATE TABLE `job_posting` (
  `job_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `employer_id` int(11) DEFAULT NULL,
  `posted_date` timestamp DEFAULT current_timestamp(),
  `deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_posting`
--

INSERT INTO `job_posting` (`job_id`, `title`, `description`, `salary`, `city`, `street`, `employer_id`) VALUES
(1, 'Senior Full Stack Developer', 'We are looking for an expert in React.js and Node.js with 5+ years of experience.', 95000.00, 'Dhaka', 'Mohakhali', 1),
(2, 'Software Quality Assurance', 'Responsible for manual and automated testing of web and mobile applications.', 45000.00, 'Remote', 'N/A', 2),
(3, 'Network Engineer', 'Maintain and optimize company network infrastructure and security protocols.', 60000.00, 'Dhaka', 'Bashundhara', 3),
(4, 'Project Manager', 'Lead software development teams and manage client requirements effectively.', 85000.00, 'Gazipur', 'Salna', 4),
(5, 'UI/UX Designer', 'Create visually appealing designs and improve user experience for our platform.', 55000.00, 'Dhaka', 'Banani', 5);

-- --------------------------------------------------------

--
-- Table structure for table `job_seeker`
--

CREATE TABLE `job_seeker` (
  `job_seeker_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_seeker`
--

INSERT INTO `job_seeker` (`job_seeker_id`, `name`, `email`, `password`, `phone`, `skills`, `experience`) VALUES
(1, 'Kamrul Hasan', 'kamrul.dev@gmail.com', 'hashed_pass_1', '01711223344', 'Java, Spring Boot, MySQL, Docker', '3 years as Backend Developer at SoftTech'),
(2, 'Nusrat Jahan', 'nusrat.hr@yahoo.com', 'hashed_pass_2', '01811223344', 'Recruitment, MS Office, Public Speaking', '2 years as HR Executive at Local Bank'),
(3, 'Abir Chowdhury', 'abir.design@outlook.com', 'hashed_pass_3', '01911223344', 'Figma, Adobe XD, UI/UX Design, CSS', 'Fresh Graduate with 3 internship projects'),
(4, 'Sultana Razia', 'razia.data@gmail.com', 'hashed_pass_4', '01611223344', 'Python, Pandas, Power BI, Tableau', '4 years as Data Analyst at Telecom Co.'),
(5, 'Tanvir Ahmed', 'tanvir.mkt@gmail.com', 'hashed_pass_5', '01511223344', 'SEO, Google Ads, Content Writing', '5 years of experience in Digital Marketing');

-- --------------------------------------------------------

--
-- Table structure for table `resume`
--

CREATE TABLE `resume` (
  `resume_id` int(11) NOT NULL,
  `job_seeker_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `upload_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resume`
--

INSERT INTO `resume` (`resume_id`, `job_seeker_id`, `file_path`, `upload_date`) VALUES
(1, 1, '/uploads/resumes/kamrul_hasan_dev.pdf', '2026-03-01'),
(2, 2, '/uploads/resumes/nusrat_jahan_hr.pdf', '2026-03-05'),
(3, 3, '/uploads/resumes/abir_chowdhury_ux.pdf', '2026-03-10'),
(4, 4, '/uploads/resumes/sultana_razia_data.pdf', '2026-03-12'),
(5, 5, '/uploads/resumes/tanvir_ahmed_mkt.pdf', '2026-03-15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_tracking`
--
ALTER TABLE `application_tracking`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `unique_application` (`job_seeker_id`,`job_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `resume_id` (`resume_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `employer`
--
ALTER TABLE `employer`
  ADD PRIMARY KEY (`employer_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `interview`
--
ALTER TABLE `interview`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `job_posting`
--
ALTER TABLE `job_posting`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- Indexes for table `job_seeker`
--
ALTER TABLE `job_seeker`
  ADD PRIMARY KEY (`job_seeker_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `resume`
--
ALTER TABLE `resume`
  ADD PRIMARY KEY (`resume_id`),
  ADD KEY `job_seeker_id` (`job_seeker_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_tracking`
--
ALTER TABLE `application_tracking`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employer`
--
ALTER TABLE `employer`
  MODIFY `employer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `interview`
--
ALTER TABLE `interview`
  MODIFY `interview_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `job_posting`
--
ALTER TABLE `job_posting`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `job_seeker`
--
ALTER TABLE `job_seeker`
  MODIFY `job_seeker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resume`
--
ALTER TABLE `resume`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_tracking`
--
ALTER TABLE `application_tracking`
  ADD CONSTRAINT `application_tracking_ibfk_1` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seeker` (`job_seeker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_tracking_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job_posting` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_tracking_ibfk_3` FOREIGN KEY (`resume_id`) REFERENCES `resume` (`resume_id`) ON DELETE SET NULL;

--
-- Constraints for table `employer`
--
ALTER TABLE `employer`
  ADD CONSTRAINT `employer_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `interview`
--
ALTER TABLE `interview`
  ADD CONSTRAINT `interview_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `application_tracking` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_posting`
--
ALTER TABLE `job_posting`
  ADD CONSTRAINT `job_posting_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employer` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `resume`
--
ALTER TABLE `resume`
  ADD CONSTRAINT `resume_ibfk_1` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seeker` (`job_seeker_id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `job_seeker_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `job_seeker_id` (`job_seeker_id`);

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seeker` (`job_seeker_id`) ON DELETE CASCADE;

-- --------------------------------------------------------

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

