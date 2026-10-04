<?php
/**
 * Student Registration API Endpoint
 * Handles registration, referral attribution, code generation, and analytics logging.
 */
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$db = Database::getConnection();

// Sanitize and read input
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$college = trim($_POST['college'] ?? '');
$branch = trim($_POST['branch'] ?? '');
$graduation_year = (int)($_POST['graduation_year'] ?? 0);
$referral_code_input = strtoupper(trim($_POST['referral_code'] ?? ''));
$source = trim($_POST['source'] ?? 'direct');
$ambassador_code = strtoupper(trim($_POST['ambassador'] ?? ''));

// Validate inputs
$errors = [];
if (empty($name) || strlen($name) < 2) {
    $errors[] = "Please provide your valid full name.";
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid academic/personal email address.";
}
if (empty($college)) {
    $errors[] = "Please specify your college or university name.";
}
if (empty($branch)) {
    $errors[] = "Please specify your engineering branch (e.g. CSE, ECE, AI/DS).";
}
if ($graduation_year < 2024 || $graduation_year > 2028) {
    $errors[] = "Graduation year must be between 2024 and 2028.";
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    // 1. Check for Duplicate Email Registration
    $checkStmt = $db->prepare("SELECT id, referral_code, name FROM users WHERE email = :email");
    $checkStmt->execute(['email' => $email]);
    $existingUser = $checkStmt->fetch();

    if ($existingUser) {
        // User already registered - log them in and redirect gracefully to success/dashboard
        $_SESSION['student_user_id'] = $existingUser['id'];
        $_SESSION['student_name'] = $existingUser['name'];
        $_SESSION['student_referral_code'] = $existingUser['referral_code'];
        
        echo json_encode([
            'success' => true,
            'is_existing' => true,
            'message' => 'Welcome back! You are already registered for the workshop.',
            'redirect_url' => 'dashboard.php?code=' . urlencode($existingUser['referral_code']),
            'referral_code' => $existingUser['referral_code']
        ]);
        exit;
    }

    // 2. Generate Unique Referral Code (e.g. FIRSTNAME + random 2-digit number)
    $cleanName = preg_replace('/[^A-Z]/', '', strtoupper($name));
    $prefix = substr($cleanName, 0, 5);
    if (strlen($prefix) < 3) {
        $prefix = 'BUILD';
    }

    $uniqueCode = '';
    $isUnique = false;
    $attempts = 0;
    while (!$isUnique && $attempts < 10) {
        $attempts++;
        $uniqueCode = $prefix . rand(10, 99);
        $stmtCheck = $db->prepare("SELECT id FROM users WHERE referral_code = :code");
        $stmtCheck->execute(['code' => $uniqueCode]);
        if (!$stmtCheck->fetch()) {
            $isUnique = true;
        }
    }
    if (!$isUnique) {
        $uniqueCode = $prefix . rand(100, 999);
    }

    // 3. Resolve Attribution & Referred By
    $referredByCode = null;
    $referrerUserId = null;

    if (!empty($referral_code_input)) {
        // Prevent Self-Referral
        if ($referral_code_input !== $uniqueCode) {
            $refStmt = $db->prepare("SELECT id, referral_code FROM users WHERE referral_code = :code");
            $refStmt->execute(['code' => $referral_code_input]);
            $referrer = $refStmt->fetch();
            if ($referrer) {
                $referredByCode = $referrer['referral_code'];
                $referrerUserId = (int)$referrer['id'];
                $source = 'referral';
            }
        }
    }

    // Check Ambassador parameter if not direct referral
    if (!$referrerUserId && !empty($ambassador_code)) {
        $ambStmt = $db->prepare("SELECT id, code FROM ambassadors WHERE code = :code AND is_active = 1");
        $ambStmt->execute(['code' => $ambassador_code]);
        if ($ambStmt->fetch()) {
            $source = 'ambassador';
            $referredByCode = $ambassador_code;
        }
    }

    // 4. Insert User Record
    $insertSql = "INSERT INTO users (name, email, college, branch, graduation_year, referral_code, referred_by, source, is_demo, created_at)
                  VALUES (:name, :email, :college, :branch, :grad, :code, :ref_by, :source, 0, :created_at)";
    $stmt = $db->prepare($insertSql);
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'college' => $college,
        'branch' => $branch,
        'grad' => $graduation_year,
        'code' => $uniqueCode,
        'ref_by' => $referredByCode,
        'source' => $source,
        'created_at' => $now
    ]);

    $newUserId = (int)$db->lastInsertId();

    // 5. If Referred, Insert into Referrals Graph Table
    if ($referrerUserId) {
        $refGraphStmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_user_id, referral_code_used, status, created_at) VALUES (:ref_id, :target_id, :code, 'registered', :created_at)");
        $refGraphStmt->execute([
            'ref_id' => $referrerUserId,
            'target_id' => $newUserId,
            'code' => $referredByCode,
            'created_at' => $now
        ]);

        // Log referral event
        $logRef = $db->prepare("INSERT INTO campaign_events (user_id, source, event_type, metadata, is_demo, created_at) VALUES (:uid, :source, 'referral_completed', :meta, 0, :created)");
        $logRef->execute([
            'uid' => $referrerUserId,
            'source' => 'referral',
            'meta' => json_encode(['referred_id' => $newUserId, 'code' => $referredByCode]),
            'created' => $now
        ]);
    }

    // 6. Log Registration Completed Analytics Event
    $logReg = $db->prepare("INSERT INTO campaign_events (user_id, source, event_type, metadata, is_demo, created_at) VALUES (:uid, :source, 'registration_completed', :meta, 0, :created)");
    $logReg->execute([
        'uid' => $newUserId,
        'source' => $source,
        'meta' => json_encode(['college' => $college, 'grad' => $graduation_year]),
        'created' => $now
    ]);

    // 7. Store in PHP Session
    $_SESSION['student_user_id'] = $newUserId;
    $_SESSION['student_name'] = $name;
    $_SESSION['student_referral_code'] = $uniqueCode;

    echo json_encode([
        'success' => true,
        'message' => "You're registered! 🎉",
        'referral_code' => $uniqueCode,
        'redirect_url' => 'success.php?code=' . urlencode($uniqueCode)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
