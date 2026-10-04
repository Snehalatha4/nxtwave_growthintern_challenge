-- BUILD IN 60: Growth Engine Relational Database Schema
-- Designed for WAMP / MySQL 8.x
-- Project: NxtWave Growth Intern Challenge

CREATE DATABASE IF NOT EXISTS `build_in_60` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `build_in_60`;

-- 1. Users Table (Core Registration & Attribution)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `college` VARCHAR(150) NOT NULL,
    `branch` VARCHAR(100) NOT NULL,
    `graduation_year` INT NOT NULL,
    `referral_code` VARCHAR(20) NOT NULL UNIQUE,
    `referred_by` VARCHAR(20) DEFAULT NULL,
    `source` VARCHAR(50) DEFAULT 'direct',
    `utm_campaign` VARCHAR(50) DEFAULT '7day_sprint',
    `is_demo` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_referral_code` (`referral_code`),
    INDEX `idx_referred_by` (`referred_by`),
    INDEX `idx_source` (`source`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Referrals Table (Explicit Referral Graph & Attribution)
CREATE TABLE IF NOT EXISTS `referrals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `referrer_id` INT NOT NULL,
    `referred_user_id` INT NOT NULL UNIQUE,
    `referral_code_used` VARCHAR(20) NOT NULL,
    `status` ENUM('registered', 'attended', 'converted') DEFAULT 'registered',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`referred_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_referrer` (`referrer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Campus Ambassadors Table (Special Distribution Channels)
CREATE TABLE IF NOT EXISTS `ambassadors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `college` VARCHAR(150) NOT NULL,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `target_registrations` INT DEFAULT 50,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Growth Experiments Table (A/B Test Definitions)
CREATE TABLE IF NOT EXISTS `experiments` (
    `id` VARCHAR(50) PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `hypothesis` TEXT NOT NULL,
    `variant_a` VARCHAR(255) NOT NULL,
    `variant_b` VARCHAR(255) NOT NULL,
    `metric` VARCHAR(100) NOT NULL,
    `status` ENUM('running', 'concluded', 'draft') DEFAULT 'running',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Experiment Events Table (Impressions and Conversions per variant)
CREATE TABLE IF NOT EXISTS `experiment_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `experiment_id` VARCHAR(50) NOT NULL,
    `variant` ENUM('A', 'B') NOT NULL,
    `session_id` VARCHAR(100) NOT NULL,
    `user_id` INT DEFAULT NULL,
    `event_type` ENUM('impression', 'conversion') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_exp_variant` (`experiment_id`, `variant`),
    INDEX `idx_exp_event` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Campaign Analytics Events (Granular Growth Funnel & Action Tracking)
CREATE TABLE IF NOT EXISTS `campaign_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(100) DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `source` VARCHAR(50) DEFAULT 'direct',
    `event_type` VARCHAR(50) NOT NULL,
    `metadata` TEXT DEFAULT NULL,
    `is_demo` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_event_source` (`source`),
    INDEX `idx_event_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Seeds for Ambassadors
INSERT INTO `ambassadors` (`name`, `college`, `code`, `target_registrations`) VALUES
('Amrita AI Club', 'Amrita Vishwa Vidyapeetham', 'AMRITA_AI_CLUB', 50),
('Vellore Tech Lead', 'VIT Vellore', 'VIT_INNOVATE', 50),
('SRM Developer Circle', 'SRM University', 'SRM_CODERS', 50),
('PES Coding Collective', 'PES University', 'PES_BUILDERS', 50)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Initial Seeds for Growth Experiments
INSERT INTO `experiments` (`id`, `name`, `hypothesis`, `variant_a`, `variant_b`, `metric`, `status`) VALUES
('exp_message', 'Message Positioning Test', 'Students will register more when workshop is positioned around building a tangible resume-ready project rather than simply learning AI concepts.', 'Build Your First AI Project in 60 Minutes', 'Build an AI Project You Can Add to Your Resume in 60 Minutes', 'Registration Conversion Rate', 'running'),
('exp_referral', 'Referral Loop Activation Test', 'Students are significantly more likely to share when presented with a personalized link and 1-click WhatsApp copy immediately upon registration.', 'Standard Confirmation Modal', 'Personalized Link + 1-Click WhatsApp Smart Share Options', 'Viral Referral Share Rate', 'running'),
('exp_urgency', 'CTA Urgency Framing Test', 'Time-bound seat reservation framing creates higher intent and lower drop-off near campaign deadline than generic registration CTA.', 'Register Free', 'Reserve Your Free Workshop Seat', 'Hero CTA Click-Through Rate', 'running')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
