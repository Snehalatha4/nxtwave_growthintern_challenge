<?php
/**
 * Registration Success & Instant Referral Activation Page
 */
require_once __DIR__ . '/config/database.php';

$code = htmlspecialchars($_GET['code'] ?? ($_SESSION['student_referral_code'] ?? ''));
$db = Database::getConnection();

$user = null;
$referralCount = 0;

if ($code) {
    $stmt = $db->prepare("SELECT * FROM users WHERE referral_code = :code");
    $stmt->execute(['code' => $code]);
    $user = $stmt->fetch();

    if ($user) {
        $refStmt = $db->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = :id");
        $refStmt->execute(['id' => $user['id']]);
        $referralCount = (int)$refStmt->fetchColumn();
    }
}

// Fallback dummy code if visited directly without session
if (!$user) {
    $code = 'BUILD' . rand(10, 99);
    $user = ['name' => 'Fellow Builder', 'referral_code' => $code];
}

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
// Correct path if in subfolder or root
$currentDir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$shareUrl = $baseUrl . $currentDir . "/index.php?ref=" . $user['referral_code'];
$whatsappMsg = "Hey! I'm joining a free workshop to build my first AI project in 60 minutes. You can join too: " . $shareUrl;

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 50px 24px; max-width: 780px;">
    <!-- Celebration Hero -->
    <div class="card" style="text-align: center; padding: 48px 36px; margin-bottom: 32px; border-color: rgba(99, 102, 241, 0.4);">
        <div style="font-size: 3rem; margin-bottom: 12px;">🎉</div>
        <span class="badge badge-emerald" style="margin-bottom: 12px;">Registration Confirmed</span>
        <h1 style="font-size: 2.4rem; margin-bottom: 8px;">You're Registered, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <p style="font-size: 1.05rem; max-width: 540px; margin: 0 auto 28px; color: var(--text-muted);">
            Your seat is reserved for "Build Your First AI Project in 60 Minutes". Check your inbox for the calendar invite.
        </p>

        <!-- Referral Launchpad Box -->
        <div class="referral-box">
            <span class="badge badge-indigo" style="margin-bottom: 8px;">🔥 Don't Build Alone &bull; Turn Registration into Growth</span>
            <h2 style="font-size: 1.35rem; color: #fff; margin-top: 6px;">Your Personal Invite Link</h2>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 14px;">
                Know classmates who would love to build their first AI project? Invite them and build together.
            </p>

            <div>
                <span style="font-size: 0.8rem; color: var(--text-dim); text-transform: uppercase;">Your Unique Referral Code:</span><br>
                <div class="referral-code-display"><?php echo htmlspecialchars($user['referral_code']); ?></div>
            </div>

            <div class="link-copy-container">
                <input type="text" id="referral-link-input" class="link-input" value="<?php echo $shareUrl; ?>" readonly>
                <button type="button" class="btn btn-secondary js-copy-link" data-target="referral-link-input">
                    📋 Copy Link
                </button>
            </div>

            <!-- Share Action Buttons -->
            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($whatsappMsg); ?>" target="_blank" class="btn btn-whatsapp" id="wa-share-btn">
                    💬 Share on WhatsApp
                </a>
                <a href="mailto:?subject=<?php echo urlencode('Join me: Build an AI Project in 60 Minutes'); ?>&body=<?php echo urlencode($whatsappMsg); ?>" class="btn btn-secondary">
                    ✉️ Share via Email
                </a>
                <a href="dashboard.php?code=<?php echo urlencode($user['referral_code']); ?>" class="btn btn-primary">
                    📊 Open Student Dashboard &rarr;
                </a>
            </div>
        </div>

        <!-- Referral Status & Milestones -->
        <div style="margin-top: 36px; text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div>
                    <h3 style="font-size: 1.15rem; color: #fff;">Your Referral Impact</h3>
                    <div style="font-size: 0.85rem; color: var(--text-dim);">Non-monetary milestone progression</div>
                </div>
                <div>
                    <span class="badge badge-indigo" style="font-size: 0.9rem; padding: 6px 14px;">
                        Your Referrals: <strong><?php echo $referralCount; ?></strong>
                    </span>
                </div>
            </div>

            <!-- Milestone Grid -->
            <div class="milestones-track">
                <div class="milestone-node <?php echo $referralCount >= 0 ? 'achieved' : ''; ?>" data-req="0">
                    <div class="milestone-badge-icon">🌱</div>
                    <div class="milestone-tier">Early Builder</div>
                    <div class="milestone-req">0 Referrals (Active)</div>
                </div>

                <div class="milestone-node <?php echo $referralCount >= 3 ? 'achieved' : ($referralCount < 3 ? 'active' : ''); ?>" data-req="3">
                    <div class="milestone-badge-icon">⚡</div>
                    <div class="milestone-tier">AI Builder</div>
                    <div class="milestone-req">3 Referrals</div>
                </div>

                <div class="milestone-node <?php echo $referralCount >= 5 ? 'achieved' : ''; ?>" data-req="5">
                    <div class="milestone-badge-icon">🏆</div>
                    <div class="milestone-tier">Growth Champion</div>
                    <div class="milestone-req">5 Referrals</div>
                </div>

                <div class="milestone-node <?php echo $referralCount >= 10 ? 'achieved' : ''; ?>" data-req="10">
                    <div class="milestone-badge-icon">👑</div>
                    <div class="milestone-tier">Campus Catalyst</div>
                    <div class="milestone-req">10+ Referrals</div>
                </div>
            </div>

            <div style="font-size: 0.78rem; color: var(--text-dim); text-align: center;">
                * Milestones and badges are non-monetary prototype campaign mechanics designed to simulate viral distribution.
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
