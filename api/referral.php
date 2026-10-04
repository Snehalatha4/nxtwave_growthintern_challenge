<?php
/**
 * Referral Statistics & Lookup API Endpoint
 */
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once __DIR__ . '/../config/database.php';

$code = strtoupper(trim($_GET['code'] ?? ''));
$userId = (int)($_GET['user_id'] ?? 0);

$db = Database::getConnection();

if (empty($code) && empty($userId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Referral code or user ID is required']);
    exit;
}

try {
    if (!empty($code)) {
        $stmt = $db->prepare("SELECT id, name, email, college, referral_code, created_at FROM users WHERE referral_code = :code");
        $stmt->execute(['code' => $code]);
    } else {
        $stmt = $db->prepare("SELECT id, name, email, college, referral_code, created_at FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }

    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Student not found']);
        exit;
    }

    // Query successful referrals
    $refStmt = $db->prepare("
        SELECT r.id, r.created_at, u.name as referred_name, u.college as referred_college, u.branch as referred_branch
        FROM referrals r
        JOIN users u ON r.referred_user_id = u.id
        WHERE r.referrer_id = :ref_id
        ORDER BY r.created_at DESC
    ");
    $refStmt->execute(['ref_id' => $user['id']]);
    $referralsList = $refStmt->fetchAll();
    $referralCount = count($referralsList);

    // Calculate Milestone
    $currentMilestone = 'Early Builder';
    $nextMilestone = 'AI Builder';
    $targetForNext = 3;
    $progressPercent = min(100, round(($referralCount / 3) * 100));

    if ($referralCount >= 10) {
        $currentMilestone = 'Campus Catalyst';
        $nextMilestone = 'Max Level Achieved';
        $targetForNext = 10;
        $progressPercent = 100;
    } elseif ($referralCount >= 5) {
        $currentMilestone = 'Growth Champion';
        $nextMilestone = 'Campus Catalyst';
        $targetForNext = 10;
        $progressPercent = min(100, round(($referralCount / 10) * 100));
    } elseif ($referralCount >= 3) {
        $currentMilestone = 'AI Builder';
        $nextMilestone = 'Growth Champion';
        $targetForNext = 5;
        $progressPercent = min(100, round(($referralCount / 5) * 100));
    }

    // Smart Next Action Guidance
    $smartNextAction = "";
    if ($referralCount === 0) {
        $smartNextAction = "Your classmates haven't seen this yet. Share your link on your batch WhatsApp group to unlock your AI Builder badge!";
    } elseif ($referralCount < 3) {
        $needed = 3 - $referralCount;
        $smartNextAction = "You're only $needed referral" . ($needed > 1 ? 's' : '') . " away from unlocking the AI Builder milestone. Invite your lab partners!";
    } elseif ($referralCount < 5) {
        $needed = 5 - $referralCount;
        $smartNextAction = "Outstanding momentum! $needed more friend" . ($needed > 1 ? 's' : '') . " needed to become a certified Growth Champion.";
    } else {
        $smartNextAction = "You're already supercharging growth! Share in other engineering department groups to reach Campus Catalyst tier.";
    }

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'college' => $user['college'],
            'referral_code' => $user['referral_code'],
            'referral_link' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/index.php?ref=" . $user['referral_code'],
        ],
        'stats' => [
            'total_referrals' => $referralCount,
            'current_milestone' => $currentMilestone,
            'next_milestone' => $nextMilestone,
            'target_for_next' => $targetForNext,
            'progress_percent' => $progressPercent,
            'smart_next_action' => $smartNextAction,
        ],
        'recent_referrals' => $referralsList
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
