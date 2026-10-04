<?php
/**
 * Admin Actions API: Demo Data Seeding, Database Reset, Demo Mode Toggle
 */
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = Database::getConnection();

if ($action === 'seed_demo') {
    try {
        // Clear previous demo records
        $db->exec("DELETE FROM referrals WHERE referred_user_id IN (SELECT id FROM users WHERE is_demo = 1)");
        $db->exec("DELETE FROM users WHERE is_demo = 1");
        $db->exec("DELETE FROM campaign_events WHERE is_demo = 1");
        $db->exec("DELETE FROM experiment_events");

        $colleges = [
            'IIT Madras' => 'CSE',
            'NIT Trichy' => 'ECE',
            'Amrita Vishwa Vidyapeetham' => 'AI & DS',
            'VIT Vellore' => 'CSE',
            'SRM Institute of Science and Technology' => 'IT',
            'PES University' => 'CSE',
            'BMS College of Engineering' => 'ISE',
            'RV College of Engineering' => 'AI & ML',
            'PSG College of Technology' => 'ECE',
            'Manipal Institute of Technology' => 'CSE'
        ];

        $sources = ['whatsapp', 'ambassador', 'club', 'email', 'direct'];
        $firstNames = ['Aarav', 'Ananya', 'Rohan', 'Sneha', 'Aditya', 'Pooja', 'Vikram', 'Meera', 'Karthik', 'Divya', 'Siddharth', 'Nisha', 'Rahul', 'Tanvi', 'Varun', 'Deepa', 'Sai', 'Kavya', 'Harish', 'Preeti'];
        $lastNames = ['Sharma', 'Verma', 'Reddy', 'Iyer', 'Nair', 'Patel', 'Rao', 'Gupta', 'Menon', 'Kumar', 'Singh', 'Deshmukh'];

        // Seed Root Referrers (Seed top advocates)
        $rootUsers = [
            ['name' => 'Sneha Sharma', 'email' => 'sneha.demo@amrita.edu', 'college' => 'Amrita Vishwa Vidyapeetham', 'branch' => 'AI & DS', 'grad' => 2026, 'code' => 'SNEHA42', 'source' => 'whatsapp'],
            ['name' => 'Aditya Rao', 'email' => 'aditya.demo@vit.edu', 'college' => 'VIT Vellore', 'branch' => 'CSE', 'grad' => 2026, 'code' => 'ADITYA99', 'source' => 'ambassador'],
            ['name' => 'Rohan Patel', 'email' => 'rohan.demo@srm.edu', 'college' => 'SRM Institute of Science and Technology', 'branch' => 'IT', 'grad' => 2026, 'code' => 'ROHAN24', 'source' => 'club'],
            ['name' => 'Pooja Iyer', 'email' => 'pooja.demo@pes.edu', 'college' => 'PES University', 'branch' => 'CSE', 'grad' => 2026, 'code' => 'POOJA15', 'source' => 'email'],
        ];

        $rootUserIds = [];
        $stmtUser = $db->prepare("INSERT INTO users (name, email, college, branch, graduation_year, referral_code, referred_by, source, is_demo, created_at) VALUES (:name, :email, :college, :branch, :grad, :code, :referred_by, :source, 1, :created_at)");

        foreach ($rootUsers as $ru) {
            $created_at = date('Y-m-d H:i:s', strtotime('-6 days + ' . rand(1, 10) . ' hours'));
            $stmtUser->execute([
                'name' => $ru['name'],
                'email' => $ru['email'],
                'college' => $ru['college'],
                'branch' => $ru['branch'],
                'grad' => $ru['grad'],
                'code' => $ru['code'],
                'referred_by' => null,
                'source' => $ru['source'],
                'created_at' => $created_at
            ]);
            $rootUserIds[$ru['code']] = (int)$db->lastInsertId();
        }

        // Generate ~423 additional simulated students across 7 days to reach exactly ~427 demo registrations
        $targetDemoCount = 423;
        $referralCodePool = array_keys($rootUserIds);
        $userInsertedMap = [];

        $stmtReferral = $db->prepare("INSERT INTO referrals (referrer_id, referred_user_id, referral_code_used, status, created_at) VALUES (:referrer_id, :referred_user_id, :code, 'registered', :created_at)");

        for ($i = 1; $i <= $targetDemoCount; $i++) {
            $fname = $firstNames[array_rand($firstNames)];
            $lname = $lastNames[array_rand($lastNames)];
            $name = $fname . ' ' . $lname;
            $email = strtolower($fname . '.' . $lname . '.' . $i . '@student.edu');
            $college = array_rand($colleges);
            $branch = $colleges[$college];
            $grad = 2026;
            $code = strtoupper(substr($fname, 0, 4) . $i . rand(10, 99));
            
            // Channel & Referral attribution logic (mimicking real viral K-factor: 150 from referrals, 150 ambassadors, 127 organic/direct/whatsapp)
            $isReferred = (rand(1, 100) <= 38); // ~38% via viral peer referrals
            $referredByCode = null;
            $referrerId = null;
            $source = $sources[array_rand($sources)];

            if ($isReferred && !empty($referralCodePool)) {
                $referredByCode = $referralCodePool[array_rand($referralCodePool)];
                $source = 'referral';
            } elseif (rand(1, 100) <= 35) {
                $source = 'ambassador';
            }

            // Distribute registration timestamps across 7 days
            $dayOffset = rand(0, 6);
            $hourOffset = rand(0, 23);
            $minuteOffset = rand(0, 59);
            $createdAt = date('Y-m-d H:i:s', strtotime("-$dayOffset days + $hourOffset hours + $minuteOffset minutes"));

            $stmtUser->execute([
                'name' => $name,
                'email' => $email,
                'college' => $college,
                'branch' => $branch,
                'grad' => $grad,
                'code' => $code,
                'referred_by' => $referredByCode,
                'source' => $source,
                'created_at' => $createdAt
            ]);
            $newUserId = (int)$db->lastInsertId();

            if ($referredByCode && isset($rootUserIds[$referredByCode])) {
                $stmtReferral->execute([
                    'referrer_id' => $rootUserIds[$referredByCode],
                    'referred_user_id' => $newUserId,
                    'code' => $referredByCode,
                    'created_at' => $createdAt
                ]);
            }

            // Add some of the newly registered students into referral code pool for secondary referral loops
            if (rand(1, 100) <= 15) {
                $referralCodePool[] = $code;
                $rootUserIds[$code] = $newUserId;
            }
        }

        // Seed realistic Experiment Impressions & Conversions
        // 1. exp_message: Variant B (Resume-ready) outperforming Variant A (General AI)
        $stmtExp = $db->prepare("INSERT INTO experiment_events (experiment_id, variant, session_id, user_id, event_type, created_at) VALUES (:exp, :var, :sess, :uid, :type, :created)");
        
        // Variant A: 750 impressions, 158 conversions (21.0%)
        for ($k = 0; $k < 750; $k++) {
            $sess = 'sess_a_' . $k;
            $created = date('Y-m-d H:i:s', strtotime('-' . rand(1, 6) . ' days'));
            $stmtExp->execute(['exp' => 'exp_message', 'var' => 'A', 'sess' => $sess, 'uid' => null, 'type' => 'impression', 'created' => $created]);
            if ($k < 158) {
                $stmtExp->execute(['exp' => 'exp_message', 'var' => 'A', 'sess' => $sess, 'uid' => null, 'type' => 'conversion', 'created' => $created]);
            }
        }
        // Variant B: 740 impressions, 215 conversions (29.0%)
        for ($k = 0; $k < 740; $k++) {
            $sess = 'sess_b_' . $k;
            $created = date('Y-m-d H:i:s', strtotime('-' . rand(1, 6) . ' days'));
            $stmtExp->execute(['exp' => 'exp_message', 'var' => 'B', 'sess' => $sess, 'uid' => null, 'type' => 'impression', 'created' => $created]);
            if ($k < 215) {
                $stmtExp->execute(['exp' => 'exp_message', 'var' => 'B', 'sess' => $sess, 'uid' => null, 'type' => 'conversion', 'created' => $created]);
            }
        }

        // 2. exp_referral: Variant B (Immediate Smart WhatsApp) 48% share vs Variant A 14%
        for ($k = 0; $k < 200; $k++) {
            $sess = 'sess_ref_a_' . $k;
            $stmtExp->execute(['exp' => 'exp_referral', 'var' => 'A', 'sess' => $sess, 'uid' => null, 'type' => 'impression', 'created' => date('Y-m-d H:i:s')]);
            if ($k < 28) {
                $stmtExp->execute(['exp' => 'exp_referral', 'var' => 'A', 'sess' => $sess, 'uid' => null, 'type' => 'conversion', 'created' => date('Y-m-d H:i:s')]);
            }
        }
        for ($k = 0; $k < 200; $k++) {
            $sess = 'sess_ref_b_' . $k;
            $stmtExp->execute(['exp' => 'exp_referral', 'var' => 'B', 'sess' => $sess, 'uid' => null, 'type' => 'impression', 'created' => date('Y-m-d H:i:s')]);
            if ($k < 96) {
                $stmtExp->execute(['exp' => 'exp_referral', 'var' => 'B', 'sess' => $sess, 'uid' => null, 'type' => 'conversion', 'created' => date('Y-m-d H:i:s')]);
            }
        }

        // 3. exp_urgency: Variant B (Seat Reservation) 34% vs Variant A 22%
        for ($k = 0; $k < 500; $k++) {
            $sess = 'sess_urg_a_' . $k;
            $stmtExp->execute(['exp' => 'exp_urgency', 'var' => 'A', 'sess' => $sess, 'uid' => null, 'type' => 'impression', 'created' => date('Y-m-d H:i:s')]);
            if ($k < 110) {
                $stmtExp->execute(['exp' => 'exp_urgency', 'var' => 'A', 'sess' => $sess, 'uid' => null, 'type' => 'conversion', 'created' => date('Y-m-d H:i:s')]);
            }
        }
        for ($k = 0; $k < 500; $k++) {
            $sess = 'sess_urg_b_' . $k;
            $stmtExp->execute(['exp' => 'exp_urgency', 'var' => 'B', 'sess' => $sess, 'uid' => null, 'type' => 'impression', 'created' => date('Y-m-d H:i:s')]);
            if ($k < 170) {
                $stmtExp->execute(['exp' => 'exp_urgency', 'var' => 'B', 'sess' => $sess, 'uid' => null, 'type' => 'conversion', 'created' => date('Y-m-d H:i:s')]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Demo data seeded successfully (427 registrations, full referral graph, 3 A/B test datasets).']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'reset_data') {
    try {
        $db->exec("DELETE FROM referrals");
        $db->exec("DELETE FROM users");
        $db->exec("DELETE FROM campaign_events");
        $db->exec("DELETE FROM experiment_events");
        echo json_encode(['success' => true, 'message' => 'Database reset to clean slate. Ready for live walkthrough!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
