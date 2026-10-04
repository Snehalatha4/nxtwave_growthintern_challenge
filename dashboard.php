<?php
/**
 * Student Impact & Referral Dashboard
 * Personalized growth hub for registered students.
 */
require_once __DIR__ . '/config/database.php';

$code = htmlspecialchars($_GET['code'] ?? ($_SESSION['student_referral_code'] ?? ''));
$db = Database::getConnection();

$user = null;
if ($code) {
    $stmt = $db->prepare("SELECT * FROM users WHERE referral_code = :code");
    $stmt->execute(['code' => $code]);
    $user = $stmt->fetch();
}

// If no user found and no code, provide sample demo student or lookup form
if (!$user && empty($code)) {
    // Check if there's any user in DB to pre-load, otherwise fallback to SNEHA42 demo
    $sampleUser = $db->query("SELECT * FROM users ORDER BY id ASC LIMIT 1")->fetch();
    if ($sampleUser) {
        $user = $sampleUser;
        $code = $user['referral_code'];
    } else {
        $code = 'SNEHA42';
        $user = [
            'id' => 1,
            'name' => 'Sneha Sharma',
            'email' => 'sneha@example.edu',
            'college' => 'Amrita Vishwa Vidyapeetham',
            'branch' => 'AI & Data Science',
            'referral_code' => 'SNEHA42'
        ];
    }
}

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$currentDir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$shareUrl = $user ? ($baseUrl . $currentDir . "/index.php?ref=" . $user['referral_code']) : '';

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 24px;">
    <!-- Dashboard Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="badge badge-indigo" style="margin-bottom: 8px;">Student Growth Hub</span>
            <h1 style="font-size: 2.2rem; color: #fff;">
                Welcome, <span id="student-name"><?php echo htmlspecialchars($user['name'] ?? 'Student'); ?></span> ⚡
            </h1>
            <p style="font-size: 0.95rem; color: var(--text-muted);">
                College: <strong><?php echo htmlspecialchars($user['college'] ?? 'Engineering Campus'); ?></strong> &bull; Branch: <strong><?php echo htmlspecialchars($user['branch'] ?? 'Computer Science'); ?></strong>
            </p>
        </div>

        <!-- Lookup Switcher for demo ease -->
        <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-subtle); padding: 12px 18px; border-radius: var(--radius-md);">
            <form method="GET" action="dashboard.php" style="display: flex; gap: 8px; align-items: center;">
                <label style="font-size: 0.8rem; color: var(--text-dim);" for="switch-code-input">Lookup Code:</label>
                <input type="text" id="switch-code-input" name="code" value="<?php echo htmlspecialchars($code); ?>" class="form-input" style="padding: 6px 10px; width: 110px; text-transform: uppercase;" placeholder="CODE">
                <button type="submit" class="btn btn-secondary btn-sm">Load</button>
            </form>
        </div>
    </div>

    <!-- Smart Next Action Banner (Feature 7) -->
    <div class="card" style="background: linear-gradient(135deg, rgba(30, 27, 75, 0.7) 0%, rgba(15, 23, 42, 0.9) 100%); border-color: rgba(99, 102, 241, 0.4); margin-bottom: 28px; padding: 20px 28px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 2rem;">💡</div>
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: #818cf8; letter-spacing: 0.05em;">Recommended Next Action</div>
                <div id="smart-next-action-text" style="font-size: 1.05rem; font-weight: 600; color: #fff; margin-top: 2px;">
                    Loading your personalized recommendation...
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Impact Metrics & Referral Link -->
    <div class="grid-3" style="grid-template-columns: 1fr 2fr; gap: 24px; margin-bottom: 32px;">
        <!-- Left: Impact Metrics -->
        <div class="card">
            <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px;">
                Your Peer Impact
            </div>
            
            <div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 6px;">
                <span id="referral-count" style="font-family: var(--font-heading); font-size: 3rem; font-weight: 800; color: #fff; line-height: 1;">0</span>
                <span style="color: var(--text-muted); font-size: 1rem;">classmates joined</span>
            </div>

            <!-- Milestone Progress Bar -->
            <div style="margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-dim); margin-bottom: 6px;">
                    <span>Milestone Progress</span>
                    <span>Target: <strong id="next-target-count" style="color: #fff;">3</strong> referrals</span>
                </div>
                <div class="progress-bar-bg" style="height: 10px;">
                    <div id="milestone-progress-bar" class="progress-bar-fill" style="width: 0%;"></div>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 12px; font-size: 0.82rem; color: var(--text-muted);">
                ℹ️ <em>"Your personal referral link helps us reach and upskill more students before workshop kick-off."</em>
            </div>
        </div>

        <!-- Right: Referral Link & Copy -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">
                    Your Unique Referral Asset
                </div>
                <span class="badge badge-emerald">Active & Attributed</span>
            </div>

            <div style="margin-bottom: 18px;">
                <div style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 4px;">Referral Code:</div>
                <div id="referral-code-val" style="font-family: var(--font-mono); font-size: 1.5rem; font-weight: 700; color: #a5b4fc; background: rgba(99, 102, 241, 0.1); padding: 6px 14px; border-radius: var(--radius-sm); display: inline-block; border: 1px solid rgba(99, 102, 241, 0.25);">
                    <?php echo htmlspecialchars($user['referral_code'] ?? 'BUILD60'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="referral-link-input">Shareable Invite URL</label>
                <div class="link-copy-container">
                    <input type="text" id="referral-link-input" class="link-input" value="<?php echo $shareUrl; ?>" readonly>
                    <button type="button" class="btn btn-secondary js-copy-link" data-target="referral-link-input">
                        📋 Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Share Copy Selector (Feature 4) -->
    <div class="card" style="margin-bottom: 32px;">
        <div style="margin-bottom: 20px;">
            <span class="badge badge-emerald" style="margin-bottom: 6px;">Feature 4: Smart Share Copy</span>
            <h2 style="font-size: 1.35rem; color: #fff;">Choose Your WhatsApp Share Message</h2>
            <p style="font-size: 0.88rem; color: var(--text-muted);">Select the positioning that best resonates with your peer group:</p>
        </div>

        <div class="grid-3" style="gap: 16px; margin-bottom: 20px;">
            <div class="share-copy-card js-share-copy-option selected" data-copy-type="friendly">
                <div class="share-copy-tag">Option A &bull; Friendly & Casual</div>
                <div class="share-copy-text">"Hey! I'm joining a free workshop to build my first AI project in 60 minutes. You can join too: [LINK]"</div>
            </div>

            <div class="share-copy-card js-share-copy-option" data-copy-type="placement">
                <div class="share-copy-tag">Option B &bull; Placement & Portfolio Focused</div>
                <div class="share-copy-text">"Want to build something AI-based for your portfolio? There's a free 60-minute hands-on workshop here: [LINK]"</div>
            </div>

            <div class="share-copy-card js-share-copy-option" data-copy-type="short">
                <div class="share-copy-tag">Option C &bull; Direct & Short</div>
                <div class="share-copy-text">"Free AI project workshop — 60 minutes. I'm joining. You should too: [LINK]"</div>
            </div>
        </div>

        <!-- Dynamic Live WhatsApp Preview & Trigger Button -->
        <div style="background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="flex: 1; min-width: 280px;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Live WhatsApp Message Preview:</div>
                <div id="whatsapp-message-preview" style="font-size: 0.88rem; color: #cbd5e1; font-family: var(--font-mono); background: #090d16; padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
                    Loading preview...
                </div>
            </div>

            <a href="#" target="_blank" class="btn btn-whatsapp btn-lg" id="whatsapp-share-btn">
                💬 Launch WhatsApp Share ↗
            </a>
        </div>
    </div>

    <!-- Milestone Hierarchy Display -->
    <div class="card" style="margin-bottom: 32px;">
        <h3 style="font-size: 1.25rem; margin-bottom: 6px; color: #fff;">Referral Milestones & Recognition</h3>
        <p style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 20px;">Non-monetary achievements unlocked as more friends register via your link.</p>

        <div class="milestones-track">
            <div class="milestone-node" data-req="0">
                <div class="milestone-badge-icon">🌱</div>
                <div class="milestone-tier">Early Builder</div>
                <div class="milestone-req">0 Referrals (Unlocked)</div>
            </div>
            <div class="milestone-node" data-req="3">
                <div class="milestone-badge-icon">⚡</div>
                <div class="milestone-tier">AI Builder</div>
                <div class="milestone-req">3 Classmates</div>
            </div>
            <div class="milestone-node" data-req="5">
                <div class="milestone-badge-icon">🏆</div>
                <div class="milestone-tier">Growth Champion</div>
                <div class="milestone-req">5 Classmates</div>
            </div>
            <div class="milestone-node" data-req="10">
                <div class="milestone-badge-icon">👑</div>
                <div class="milestone-tier">Campus Catalyst</div>
                <div class="milestone-req">10 Classmates</div>
            </div>
        </div>
    </div>

    <!-- Recent Referral Activity Table -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.2rem; color: #fff;">Recent Classmates Joined via Your Link</h3>
            <span class="badge badge-indigo">Real-Time Attributed</span>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Classmate Name</th>
                        <th>College</th>
                        <th>Branch</th>
                        <th>Referral Status</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody id="recent-referrals-list">
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 24px;">
                            Loading live referral activity...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        StudentDashboard.init('<?php echo htmlspecialchars($code); ?>');
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
