-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2019 at 12:58 PM
-- Server version: 10.3.16-MariaDB
-- PHP Version: 7.3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user16DB`
--
CREATE DATABASE IF NOT EXISTS `user16DB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `user16DB`;

-- --------------------------------------------------------

--
-- Table structure for table `add_to_company_requests`
--

CREATE TABLE `add_to_company_requests` (
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `company_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `advices`
--

CREATE TABLE `advices` (
  `adv_id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` varchar(200) NOT NULL,
  `tech_data` varbinary(2048) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `advice_question`
--

CREATE TABLE `advice_question` (
  `que_id` mediumint(8) UNSIGNED NOT NULL,
  `adv_id` mediumint(8) UNSIGNED NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `is_auto_suggested` tinyint(1) NOT NULL,
  `date_given` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` mediumint(8) UNSIGNED NOT NULL,
  `manager_id` mediumint(8) UNSIGNED NOT NULL,
  `company_name` varchar(50) NOT NULL,
  `company_description` varchar(400) NOT NULL,
  `company_specialties` varchar(100) NOT NULL,
  `score` tinyint(3) UNSIGNED NOT NULL,
  `logo` varchar(20) NOT NULL,
  `notify_members_on_new_questions` tinyint(1) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL,
  `is_approved` tinyint(1) NOT NULL,
  `date_approved` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `company_questions`
--

CREATE TABLE `company_questions` (
  `company_id` mediumint(8) UNSIGNED NOT NULL,
  `que_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `company_users`
--

CREATE TABLE `company_users` (
  `company_id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `date_joined` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `messages_in`
--

CREATE TABLE `messages_in` (
  `msg_id` mediumint(8) UNSIGNED NOT NULL,
  `sender_id` mediumint(8) UNSIGNED NOT NULL,
  `receiver_id` mediumint(8) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` varchar(2000) NOT NULL,
  `msg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_new` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `messages_out`
--

CREATE TABLE `messages_out` (
  `msg_id` mediumint(8) UNSIGNED NOT NULL,
  `sender_id` mediumint(8) UNSIGNED NOT NULL,
  `receiver_id` mediumint(8) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` varchar(2000) NOT NULL,
  `msg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_sent` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `que_id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `type` varchar(40) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` varchar(350) NOT NULL,
  `tech_data` varbinary(3072) NOT NULL,
  `date_opened` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_closed` timestamp NULL DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL,
  `is_visible` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `question_advice_comments`
--

CREATE TABLE `question_advice_comments` (
  `adv_id` mediumint(8) UNSIGNED NOT NULL,
  `que_id` mediumint(8) UNSIGNED NOT NULL,
  `comments` varchar(3072) NOT NULL,
  `last_update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `que_id` mediumint(8) UNSIGNED NOT NULL,
  `adv_id` mediumint(8) UNSIGNED NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `title` varchar(30) NOT NULL,
  `description` varchar(200) NOT NULL,
  `score` tinyint(3) UNSIGNED NOT NULL,
  `is_best_advice` tinyint(1) NOT NULL,
  `is_new` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `daily_questions_limit` tinyint(3) UNSIGNED NOT NULL,
  `statistics_interval` tinyint(4) NOT NULL,
  `inbox_limit` smallint(5) UNSIGNED NOT NULL,
  `outbox_limit` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`daily_questions_limit`, `statistics_interval`, `inbox_limit`, `outbox_limit`) VALUES
(10, 0, 1000, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `user_psw` varchar(255) NOT NULL,
  `user_type` varchar(8) NOT NULL,
  `user_fname` varchar(30) NOT NULL,
  `user_lname` varchar(30) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL,
  `is_approved` tinyint(1) NOT NULL,
  `u_email` varchar(254) NOT NULL,
  `picture` varchar(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `duplicate_to_mail` tinyint(1) NOT NULL,
  `allow_newsletters` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `user_psw`, `user_type`, `user_fname`, `user_lname`, `is_blocked`, `is_approved`, `u_email`, `picture`, `date_created`, `duplicate_to_mail`, `allow_newsletters`) VALUES
(31, 'admin_100', '$2y$10$fCSabggqlQWFjyhX0eweQ.ePb3PTioUjNEnZA2wg7ZGWkamnclIwy', 'admin', 'Israel', 'Israeli', 0, 1, 'wisercut.it@gmail.com', 'default.png', '2019-08-16 15:10:47', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_blocked_companies`
--

CREATE TABLE `user_blocked_companies` (
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `company_id` mediumint(8) UNSIGNED NOT NULL,
  `date_blocked` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_to_company_requests`
--
ALTER TABLE `add_to_company_requests`
  ADD KEY `add_to_company_request_ibfk_1` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `advices`
--
ALTER TABLE `advices`
  ADD PRIMARY KEY (`adv_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `advice_question`
--
ALTER TABLE `advice_question`
  ADD KEY `que_id` (`que_id`),
  ADD KEY `adv_id` (`adv_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `company_questions`
--
ALTER TABLE `company_questions`
  ADD KEY `company_id` (`company_id`),
  ADD KEY `que_id` (`que_id`);

--
-- Indexes for table `company_users`
--
ALTER TABLE `company_users`
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages_in`
--
ALTER TABLE `messages_in`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `messages_out`
--
ALTER TABLE `messages_out`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `from_user_id` (`sender_id`),
  ADD KEY `to_user_id` (`receiver_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`que_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `question_advice_comments`
--
ALTER TABLE `question_advice_comments`
  ADD KEY `adv_id` (`adv_id`),
  ADD KEY `que_id` (`que_id`);

--
-- Indexes for table `responses`
--
ALTER TABLE `responses`
  ADD KEY `adv_id` (`adv_id`),
  ADD KEY `que_id` (`que_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_blocked_companies`
--
ALTER TABLE `user_blocked_companies`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advices`
--
ALTER TABLE `advices`
  MODIFY `adv_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `messages_in`
--
ALTER TABLE `messages_in`
  MODIFY `msg_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `messages_out`
--
ALTER TABLE `messages_out`
  MODIFY `msg_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `que_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `add_to_company_requests`
--
ALTER TABLE `add_to_company_requests`
  ADD CONSTRAINT `add_to_company_requests_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`),
  ADD CONSTRAINT `add_to_company_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `advices`
--
ALTER TABLE `advices`
  ADD CONSTRAINT `advices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `advice_question`
--
ALTER TABLE `advice_question`
  ADD CONSTRAINT `advice_question_ibfk_2` FOREIGN KEY (`que_id`) REFERENCES `questions` (`que_id`),
  ADD CONSTRAINT `advice_question_ibfk_3` FOREIGN KEY (`adv_id`) REFERENCES `advices` (`adv_id`);

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `company_questions`
--
ALTER TABLE `company_questions`
  ADD CONSTRAINT `company_questions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`),
  ADD CONSTRAINT `company_questions_ibfk_2` FOREIGN KEY (`que_id`) REFERENCES `questions` (`que_id`);

--
-- Constraints for table `company_users`
--
ALTER TABLE `company_users`
  ADD CONSTRAINT `company_users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`),
  ADD CONSTRAINT `company_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `messages_in`
--
ALTER TABLE `messages_in`
  ADD CONSTRAINT `messages_in_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_in_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `messages_out`
--
ALTER TABLE `messages_out`
  ADD CONSTRAINT `messages_out_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_out_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `question_advice_comments`
--
ALTER TABLE `question_advice_comments`
  ADD CONSTRAINT `question_advice_comments_ibfk_1` FOREIGN KEY (`adv_id`) REFERENCES `advices` (`adv_id`),
  ADD CONSTRAINT `question_advice_comments_ibfk_2` FOREIGN KEY (`que_id`) REFERENCES `questions` (`que_id`);

--
-- Constraints for table `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`adv_id`) REFERENCES `advices` (`adv_id`),
  ADD CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`que_id`) REFERENCES `questions` (`que_id`);

--
-- Constraints for table `user_blocked_companies`
--
ALTER TABLE `user_blocked_companies`
  ADD CONSTRAINT `user_blocked_companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_blocked_companies_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
