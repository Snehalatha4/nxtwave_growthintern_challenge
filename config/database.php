<?php
/**
 * BUILD IN 60 - Database Configuration & Connection Handler
 * Supports MySQL (WAMP standard) with automatic fallback to SQLite if MySQL is offline.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Configuration
define('APP_NAME', 'BUILD IN 60');
define('APP_TAGLINE', 'Build your first AI project in 60 minutes.');
define('CAMPAIGN_GOAL', 500);
define('ADMIN_USER', 'growth_admin');
define('ADMIN_PASS', 'nxtwave2026'); // In real production, use hashed passwords in env/vault

// Database Credentials (Standard WAMP setup)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'build_in_60');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        try {
            // Attempt 1: Connect directly to MySQL
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$driver = 'mysql';
        } catch (PDOException $e) {
            // If database doesn't exist, try connecting to MySQL server to create it
            try {
                $serverDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
                $pdo = new PDO($serverDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Reconnect to newly created database
                self::$instance = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$driver = 'mysql';
                self::initSchema(self::$instance, 'mysql');
            } catch (Exception $serverEx) {
                // If MySQL is offline, fallback to SQLite with bulletproof serverless support
                self::$driver = 'sqlite';
                $pdoCreated = false;
                
                // Priority 1: /tmp/ directory (always writable on Vercel/AWS Lambda/Render)
                $tempPath = sys_get_temp_dir() . '/build_in_60.sqlite';
                try {
                    $isNew = !file_exists($tempPath);
                    self::$instance = new PDO("sqlite:" . $tempPath, null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    $pdoCreated = true;
                    if ($isNew || filesize($tempPath) === 0) {
                        self::initSchema(self::$instance, 'sqlite');
                    }
                } catch (Exception $tmpEx) {
                    // Priority 2: In-Memory SQLite (100% guaranteed to work anywhere with zero disk requirement)
                    self::$instance = new PDO("sqlite::memory:", null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    $pdoCreated = true;
                    self::initSchema(self::$instance, 'sqlite');
                }
            }
        }

        return self::$instance;
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    private static function initSchema(PDO $db, string $driver): void {
        if ($driver === 'mysql') {
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $db->exec($sql);
            }
        } else {
            // SQLite schema
            $sqliteSql = <<<SQL
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                college TEXT NOT NULL,
                branch TEXT NOT NULL,
                graduation_year INTEGER NOT NULL,
                referral_code TEXT NOT NULL UNIQUE,
                referred_by TEXT DEFAULT NULL,
                source TEXT DEFAULT 'direct',
                utm_campaign TEXT DEFAULT '7day_sprint',
                is_demo INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS referrals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                referrer_id INTEGER NOT NULL,
                referred_user_id INTEGER NOT NULL UNIQUE,
                referral_code_used TEXT NOT NULL,
                status TEXT DEFAULT 'registered',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (referrer_id) REFERENCES users(id),
                FOREIGN KEY (referred_user_id) REFERENCES users(id)
            );

            CREATE TABLE IF NOT EXISTS ambassadors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                college TEXT NOT NULL,
                code TEXT NOT NULL UNIQUE,
                target_registrations INTEGER DEFAULT 50,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS experiments (
                id TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                hypothesis TEXT NOT NULL,
                variant_a TEXT NOT NULL,
                variant_b TEXT NOT NULL,
                metric TEXT NOT NULL,
                status TEXT DEFAULT 'running',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS experiment_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                experiment_id TEXT NOT NULL,
                variant TEXT NOT NULL,
                session_id TEXT NOT NULL,
                user_id INTEGER DEFAULT NULL,
                event_type TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS campaign_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT DEFAULT NULL,
                user_id INTEGER DEFAULT NULL,
                source TEXT DEFAULT 'direct',
                event_type TEXT NOT NULL,
                metadata TEXT DEFAULT NULL,
                is_demo INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            INSERT OR IGNORE INTO ambassadors (name, college, code, target_registrations) VALUES
            ('Amrita AI Club', 'Amrita Vishwa Vidyapeetham', 'AMRITA_AI_CLUB', 50),
            ('Vellore Tech Lead', 'VIT Vellore', 'VIT_INNOVATE', 50),
            ('SRM Developer Circle', 'SRM University', 'SRM_CODERS', 50),
            ('PES Coding Collective', 'PES University', 'PES_BUILDERS', 50);

            INSERT OR IGNORE INTO experiments (id, name, hypothesis, variant_a, variant_b, metric, status) VALUES
            ('exp_message', 'Message Positioning Test', 'Students will register more when workshop is positioned around building a tangible resume-ready project rather than simply learning AI concepts.', 'Build Your First AI Project in 60 Minutes', 'Build an AI Project You Can Add to Your Resume in 60 Minutes', 'Registration Conversion Rate', 'running'),
            ('exp_referral', 'Referral Loop Activation Test', 'Students are significantly more likely to share when presented with a personalized link and 1-click WhatsApp copy immediately upon registration.', 'Standard Confirmation Modal', 'Personalized Link + 1-Click WhatsApp Smart Share Options', 'Viral Referral Share Rate', 'running'),
            ('exp_urgency', 'CTA Urgency Framing Test', 'Time-bound seat reservation framing creates higher intent and lower drop-off near campaign deadline than generic registration CTA.', 'Register Free', 'Reserve Your Free Workshop Seat', 'Hero CTA Click-Through Rate', 'running');
SQL;
            $db->exec($sqliteSql);
        }
    }
}
