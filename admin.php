<?php
/**
 * Admin Growth Analytics & Growth Ops Dashboard
 * Tracks campaign performance, viral referral health, attribution sources, and funnels.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Handle Demo Login Authentication
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $loginError = 'Invalid credentials. Use demo login: growth_admin / nxtwave2026';
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    header('Location: admin.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 24px;">

<?php if (!isAdminLoggedIn()): ?>
    <!-- Admin Login Screen for Prototype -->
    <div style="max-width: 440px; margin: 60px auto;">
        <div class="card" style="padding: 36px; text-align: center;">
            <div style="font-size: 2.4rem; margin-bottom: 8px;">🔐</div>
            <span class="badge badge-indigo" style="margin-bottom: 12px;">Growth Ops Access</span>
            <h1 style="font-size: 1.6rem; margin-bottom: 8px; color: #fff;">Admin Growth Dashboard</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px;">
                Enter demo growth credentials to access real-time funnel, attribution and viral loop metrics.
            </p>

            <?php if ($loginError): ?>
            <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid #f43f5e; color: #fda4af; padding: 10px; border-radius: var(--radius-sm); font-size: 0.85rem; margin-bottom: 16px;">
                <?php echo htmlspecialchars($loginError); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group" style="text-align: left;">
                    <label class="form-label" for="admin-user-input">Username</label>
                    <input type="text" id="admin-user-input" name="username" class="form-input" value="growth_admin" required>
                </div>

                <div class="form-group" style="text-align: left;">
                    <label class="form-label" for="admin-pass-input">Password</label>
                    <input type="password" id="admin-pass-input" name="password" class="form-input" value="nxtwave2026" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
                    Enter Growth Control Room 🚀
                </button>
            </form>

            <div style="margin-top: 20px; font-size: 0.78rem; color: var(--text-dim); background: rgba(255,255,255,0.02); padding: 8px; border-radius: 6px;">
                🔑 Default Demo Credentials: <code>growth_admin</code> / <code>nxtwave2026</code>
            </div>
        </div>
    </div>

<?php else: ?>

    <!-- Header & Quick Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge badge-emerald">Live Growth Ops</span>
                <span class="badge badge-demo">Demo Mode Enabled</span>
            </div>
            <h1 style="font-size: 2.2rem; color: #fff; margin-top: 6px;">Campaign Growth Dashboard</h1>
            <p style="font-size: 0.9rem; color: var(--text-muted);">
                Target: 500 Registrations &bull; Budget: ₹2,000 &bull; 7-Day Sprint Simulation
            </p>
        </div>

        <!-- Simulation Seeder Controls -->
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button type="button" class="btn btn-primary btn-sm" id="seed-demo-btn">
                ⚡ Seed 427 Demo Registrations
            </button>
            <button type="button" class="btn btn-secondary btn-sm" id="reset-data-btn">
                🔄 Reset Data
            </button>
            <a href="admin.php?action=logout" class="btn btn-secondary btn-sm" style="color: #f87171;">
                Logout
            </a>
        </div>
    </div>

    <!-- Campaign Goal Tracker Banner -->
    <div class="campaign-progress-card" style="max-width: 100%; margin-bottom: 32px;">
        <div class="progress-header">
            <div>
                <span class="badge badge-indigo">Primary Campaign Objective</span>
                <div class="progress-count" style="font-size: 1.8rem; margin-top: 4px;">
                    Progress toward Goal: <span id="admin-goal-text">Loading...</span>
                </div>
            </div>
            <div style="text-align: right;">
                <span class="badge badge-emerald">Sprint Window: Day 6 of 7</span>
                <div style="font-size: 0.85rem; color: var(--text-dim); margin-top: 4px;">Cost Per Reg: ~₹4.68 (Budget ₹2k)</div>
            </div>
        </div>
        <div class="progress-bar-bg" style="height: 16px;">
            <div id="admin-goal-progress" class="progress-bar-fill" style="width: 0%;"></div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-card-title">Total Registrations</div>
            <div class="metric-card-value" id="metric-total-reg">0</div>
            <div class="metric-card-sub">Combined Organic + Referral</div>
        </div>

        <div class="metric-card">
            <div class="metric-card-title">Referral Registrations</div>
            <div class="metric-card-value" id="metric-referral-reg" style="color: #818cf8;">0</div>
            <div class="metric-card-sub">Generated via peer links</div>
        </div>

        <div class="metric-card">
            <div class="metric-card-title">Viral Referral Rate</div>
            <div class="metric-card-value" id="metric-referral-rate" style="color: #34d399;">0%</div>
            <div class="metric-card-sub">% of total from referrals</div>
        </div>

        <div class="metric-card">
            <div class="metric-card-title">Referral Health (Feature 1)</div>
            <div style="margin-top: 6px;">
                <span id="metric-referral-health" class="badge badge-emerald" style="font-size: 1rem; padding: 6px 14px;">
                    Healthy 🟢
                </span>
            </div>
            <div class="metric-card-sub" style="margin-top: 10px;">
                Avg Referrals/Student: <strong id="metric-avg-referrals" style="color:#fff;">0</strong>
            </div>
        </div>
    </div>

    <!-- Funnel Visualization & Attribution Grid -->
    <div class="grid-3" style="grid-template-columns: 1.5fr 1fr; gap: 24px; margin-bottom: 32px;">
        <!-- Full Funnel -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <span class="badge badge-indigo">Acquisition &rarr; Referral Funnel</span>
                    <h2 style="font-size: 1.3rem; color: #fff; margin-top: 4px;">Growth Conversion Funnel</h2>
                </div>
                <span class="badge badge-demo">Simulated Traffic</span>
            </div>

            <div class="funnel-container" id="growth-funnel-container">
                <div style="text-align: center; color: var(--text-dim); padding: 20px;">Loading growth funnel...</div>
            </div>
        </div>

        <!-- Channel Attribution (Feature 2) -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <span class="badge badge-emerald">Feature 2: Channel Attribution</span>
                    <h2 style="font-size: 1.3rem; color: #fff; margin-top: 4px;">Source Breakdown</h2>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Channel Source</th>
                            <th>Registrations</th>
                            <th>Share %</th>
                        </tr>
                    </thead>
                    <tbody id="channel-breakdown-tbody">
                        <tr><td colspan="3" style="text-align:center;">Loading channels...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Campus Leaderboard (Feature 6) & Campus Ambassadors (Feature 3) -->
    <div class="grid-3" style="grid-template-columns: 1fr 1.5fr; gap: 24px;">
        <!-- Campus Leaderboard -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <span class="badge badge-demo">Feature 6: Campus Leaderboard</span>
                    <h2 style="font-size: 1.25rem; color: #fff; margin-top: 4px;">Top Colleges</h2>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>College</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody id="campus-leaderboard-tbody">
                        <tr><td colspan="3" style="text-align:center;">Loading leaderboard...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ambassador Network -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <span class="badge badge-indigo">Feature 3: Ambassador Network</span>
                    <h2 style="font-size: 1.25rem; color: #fff; margin-top: 4px;">Ambassador Performance</h2>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ambassador / Club</th>
                            <th>College</th>
                            <th>Code</th>
                            <th>Delivered / Target</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody id="ambassadors-tbody">
                        <tr><td colspan="5" style="text-align:center;">Loading ambassadors...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
<?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
