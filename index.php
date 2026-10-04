<?php
/**
 * BUILD IN 60 - Main Growth Landing Page
 * Equipped with dynamic A/B test variations, referral detection, and live campaign goal tracking.
 */
require_once __DIR__ . '/config/database.php';
$db = Database::getConnection();

// 1. Resolve A/B Test Variants (from URL or cookie/default)
$msgVariant = $_GET['v_exp_message'] ?? 'A';
$urgencyVariant = $_GET['v_exp_urgency'] ?? 'B'; // Defaulting to high-converting "Reserve Your Free Workshop Seat"

// Headline dynamic selection based on A/B test
$headline = ($msgVariant === 'B')
    ? "Build an AI Project You Can Add to Your Resume in 60 Minutes"
    : "Build Your First AI Project in 60 Minutes";

$primaryCta = ($urgencyVariant === 'A')
    ? "Register Free"
    : "Reserve My Free Seat";

// 2. Fetch live vs demo registration count
try {
    $totalRegs = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $demoRegs = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_demo = 1")->fetchColumn();
    // If no data exists, display realistic default 427 for simulation
    $displayCount = $totalRegs > 0 ? $totalRegs : 427;
} catch (Exception $e) {
    $displayCount = 427;
}

$refParam = htmlspecialchars($_GET['ref'] ?? '');
$ambParam = htmlspecialchars($_GET['ambassador'] ?? '');

include __DIR__ . '/includes/header.php';
?>

<!-- Referral Banner if referred by classmate -->
<?php if ($refParam): ?>
<div style="background: rgba(99, 102, 241, 0.15); border-bottom: 1px solid rgba(99, 102, 241, 0.4); padding: 12px 0; text-align: center;">
    <div class="container" style="font-size: 0.95rem; color: #c7d2fe;">
        🎉 <strong>Special Peer Invite:</strong> You were invited via referral code <code><?php echo $refParam; ?></code>. Register below to unlock your bonus workshop resources!
    </div>
</div>
<?php elseif ($ambParam): ?>
<div style="background: rgba(16, 185, 129, 0.15); border-bottom: 1px solid rgba(16, 185, 129, 0.4); padding: 12px 0; text-align: center;">
    <div class="container" style="font-size: 0.95rem; color: #a7f3d0;">
        🎓 <strong>Campus Partner Access:</strong> Joining via <strong><?php echo $ambParam; ?></strong> campus cohort.
    </div>
</div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-tag">
            <span class="badge badge-indigo">🚀 Free Live Hands-On AI Workshop</span>
        </div>

        <h1 class="hero-title">
            <span class="hero-title-gradient"><?php echo $headline; ?></span>
        </h1>

        <p class="hero-subtitle">
            A free hands-on workshop for final-year engineering students who want to stop watching tutorials and start building. Walk away with a functioning AI app on GitHub.
        </p>

        <!-- Live Campaign Goal Progress Component -->
        <div class="campaign-progress-card">
            <div class="progress-header">
                <div>
                    <span class="badge badge-demo" style="margin-bottom: 4px;">Simulation Metric</span>
                    <div class="progress-count">
                        <span><?php echo $displayCount; ?></span> / <?php echo CAMPAIGN_GOAL; ?> students registered
                    </div>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-emerald">🔥 7-Day Sprint Active</span>
                    <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 4px;">
                        <?php echo max(0, CAMPAIGN_GOAL - $displayCount); ?> seats remaining
                    </div>
                </div>
            </div>

            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?php echo min(100, round(($displayCount / CAMPAIGN_GOAL) * 100)); ?>%;"></div>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.78rem; color: var(--text-dim);">
                <span>Campaign Target: 500 Students</span>
                <span>Viral Growth Engine: Active</span>
            </div>
        </div>

        <!-- Hero CTAs -->
        <div class="hero-cta-group">
            <a href="register.php<?php echo $refParam ? '?ref='.$refParam : ($ambParam ? '?ambassador='.$ambParam : ''); ?>" class="btn btn-primary btn-lg js-register-btn">
                <?php echo $primaryCta; ?> ⚡
            </a>
            <a href="#how-it-works" class="btn btn-secondary btn-lg">
                How It Works ↓
            </a>
        </div>

        <!-- Hero Value Pills -->
        <div class="hero-pills">
            <div class="hero-pill-item"><i>✓</i> 100% Free Workshop</div>
            <div class="hero-pill-item"><i>✓</i> 60 Minutes Duration</div>
            <div class="hero-pill-item"><i>✓</i> Beginner Friendly</div>
            <div class="hero-pill-item"><i>✓</i> Practical AI Project</div>
            <div class="hero-pill-item"><i>✓</i> Final-Year Focused</div>
        </div>
    </div>
</section>

<!-- Section 1: Why Students Will Care -->
<section class="section" id="why-students-care" style="background: rgba(15, 23, 42, 0.4);">
    <div class="container">
        <div class="section-header">
            <span class="badge badge-indigo">Outcome-Driven Learning</span>
            <h2 class="section-title">Why Engineering Students Care</h2>
            <p>Designed specifically to help 2026 graduates bridge the gap between theory and industry-grade AI applications.</p>
        </div>

        <div class="grid-3">
            <div class="benefit-card">
                <div class="benefit-icon">🛠️</div>
                <h3 class="benefit-title">Build Something Practical</h3>
                <p>No dry theory or boilerplate math. Write clean, working code and deploy a real AI-powered micro-application in 60 minutes flat.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">⚡</div>
                <h3 class="benefit-title">Learn by Doing</h3>
                <p>Guided hands-on live code session. Understand API integrations, prompt pipelines, and embeddings through active building.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">💼</div>
                <h3 class="benefit-title">Portfolio & Resume Ready</h3>
                <p>Stand out in final-year placement interviews with a tangible GitHub repository and live demo URL you actually coded yourself.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: How the Growth Loop Works -->
<section class="section" id="growth-loop">
    <div class="container">
        <div class="section-header">
            <span class="badge badge-emerald">Viral Distribution Engine</span>
            <h2 class="section-title">How The Growth Loop Works</h2>
            <p>Registration isn't the end of the funnel. Every student becomes an advocate with instant WhatsApp sharing and milestone perks.</p>
        </div>

        <div class="growth-loop-steps">
            <div class="loop-step">
                <div class="step-num">1</div>
                <div class="step-title">REGISTER</div>
                <div class="step-desc">Claim your free seat in under 30 seconds.</div>
            </div>
            <div class="loop-arrow">&rarr;</div>

            <div class="loop-step">
                <div class="step-num">2</div>
                <div class="step-title">GET YOUR LINK</div>
                <div class="step-desc">Instant personal referral code generated.</div>
            </div>
            <div class="loop-arrow">&rarr;</div>

            <div class="loop-step">
                <div class="step-num">3</div>
                <div class="step-title">SHARE</div>
                <div class="step-desc">1-Click pre-filled WhatsApp & Slack copy.</div>
            </div>
            <div class="loop-arrow">&rarr;</div>

            <div class="loop-step">
                <div class="step-num">4</div>
                <div class="step-title">INVITE FRIENDS</div>
                <div class="step-desc">Classmates join through your personal link.</div>
            </div>
            <div class="loop-arrow">&rarr;</div>

            <div class="loop-step">
                <div class="step-num">5</div>
                <div class="step-title">TRACK IMPACT</div>
                <div class="step-desc">Watch your referral milestones unlock live!</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="dashboard.php" class="btn btn-secondary">
                View Student Referral Dashboard Preview &rarr;
            </a>
        </div>
    </div>
</section>

<!-- Section 3: How It Works & What You Build -->
<section class="section" id="how-it-works" style="background: rgba(15, 23, 42, 0.4);">
    <div class="container">
        <div class="section-header">
            <span class="badge badge-indigo">Session Breakdown</span>
            <h2 class="section-title">What Happens in 60 Minutes?</h2>
            <p>A fast-paced, high-yield agenda designed for engineers.</p>
        </div>

        <div class="grid-3">
            <div class="card">
                <span class="badge badge-indigo" style="margin-bottom: 12px;">Minutes 00 - 15</span>
                <h3 style="font-size: 1.2rem; margin-bottom: 8px;">Architecture & API Setup</h3>
                <p style="font-size: 0.9rem;">Understand modern AI API patterns, token mechanics, and setting up your rapid development environment.</p>
            </div>

            <div class="card">
                <span class="badge badge-emerald" style="margin-bottom: 12px;">Minutes 15 - 45</span>
                <h3 style="font-size: 1.2rem; margin-bottom: 8px;">Core Build & Logic</h3>
                <p style="font-size: 0.9rem;">Code an interactive AI assistant that queries structured data and provides intelligent responses in real time.</p>
            </div>

            <div class="card">
                <span class="badge badge-amber" style="margin-bottom: 12px;">Minutes 45 - 60</span>
                <h3 style="font-size: 1.2rem; margin-bottom: 8px;">Deployment & GitHub</h3>
                <p style="font-size: 0.9rem;">Deploy your application live to the web, commit your code to GitHub, and add the project credential to your resume.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section 4: Sample Social Proof & Campus Competition -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="badge badge-demo">Example Student Perspectives (Demo Simulation)</span>
            <h2 class="section-title">Built by Engineers, For Engineers</h2>
            <p>How peer-driven learning drives massive engagement across top campuses.</p>
        </div>

        <div class="grid-3" style="margin-bottom: 40px;">
            <div class="card">
                <div style="font-size: 0.95rem; color: #cbd5e1; margin-bottom: 16px; font-style: italic;">
                    "Most workshops waste 40 minutes on history. In Build in 60, we started coding at minute 5 and had a working project deployed before the hour ended."
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #312e81; display: flex; align-items: center; justify-content: center; font-weight: 700;">AK</div>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Arun K. (Demo Profile)</div>
                        <div style="font-size: 0.78rem; color: var(--text-dim);">Final Year CSE &bull; VIT</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="font-size: 0.95rem; color: #cbd5e1; margin-bottom: 16px; font-style: italic;">
                    "Shared my personal referral link in our batch WhatsApp group. 6 of my friends registered in 10 minutes and we built the project together."
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #065f46; display: flex; align-items: center; justify-content: center; font-weight: 700;">SN</div>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Sneha N. (Demo Profile)</div>
                        <div style="font-size: 0.78rem; color: var(--text-dim);">AI & DS &bull; Amrita</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="font-size: 0.95rem; color: #cbd5e1; margin-bottom: 16px; font-style: italic;">
                    "Having a deployed project URL on my resume made my technical interview flow 10x smoother. Exactly what 2026 graduates need."
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #831843; display: flex; align-items: center; justify-content: center; font-weight: 700;">RD</div>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Rohan D. (Demo Profile)</div>
                        <div style="font-size: 0.78rem; color: var(--text-dim);">ECE &bull; SRM</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campus Leaderboard Snapshot -->
        <div class="card" style="max-width: 800px; margin: 0 auto; border-color: rgba(99, 102, 241, 0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <span class="badge badge-demo">Demo Leaderboard</span>
                    <h3 style="font-size: 1.2rem; color: #fff; margin-top: 4px;">Top Participating Campuses</h3>
                </div>
                <a href="admin.php" class="btn btn-secondary btn-sm">Full Analytics &rarr;</a>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>College / Institution</th>
                            <th>Registered Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>🥇 #1</strong></td>
                            <td>Amrita Vishwa Vidyapeetham</td>
                            <td><span class="badge badge-indigo">82 Registrations</span></td>
                        </tr>
                        <tr>
                            <td><strong>🥈 #2</strong></td>
                            <td>VIT Vellore</td>
                            <td><span class="badge badge-indigo">67 Registrations</span></td>
                        </tr>
                        <tr>
                            <td><strong>🥉 #3</strong></td>
                            <td>SRM Institute of Science & Technology</td>
                            <td><span class="badge badge-indigo">54 Registrations</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section class="section" style="background: linear-gradient(180deg, rgba(15, 23, 42, 0.5) 0%, rgba(30, 27, 75, 0.6) 100%); text-align: center; border-top: 1px solid var(--border-subtle);">
    <div class="container">
        <span class="badge badge-emerald" style="margin-bottom: 16px;">Limited Batch Capacity</span>
        <h2 style="font-size: 2.5rem; margin-bottom: 16px;">Ready to Build Your First AI App?</h2>
        <p style="max-width: 580px; margin: 0 auto 32px; color: var(--text-muted);">
            Join hundreds of ambitious engineering students in this high-impact 60-minute sprint. 100% free.
        </p>
        <a href="register.php<?php echo $refParam ? '?ref='.$refParam : ''; ?>" class="btn btn-primary btn-lg js-register-btn">
            <?php echo $primaryCta; ?> ⚡
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
