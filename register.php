<?php
/**
 * Dedicated Workshop Registration Page
 */
require_once __DIR__ . '/config/database.php';

$refCode = htmlspecialchars($_GET['ref'] ?? '');
$ambCode = htmlspecialchars($_GET['ambassador'] ?? '');
$source = htmlspecialchars($_GET['source'] ?? ($refCode ? 'referral' : ($ambCode ? 'ambassador' : 'direct')));

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 60px 24px; max-width: 640px;">
    <div class="card" style="padding: 40px;">
        <div style="text-align: center; margin-bottom: 28px;">
            <span class="badge badge-emerald" style="margin-bottom: 12px;">100% Free Workshop</span>
            <h1 style="font-size: 2rem; margin-bottom: 8px;">Claim Your Workshop Seat</h1>
            <p style="font-size: 0.95rem;">Join "Build Your First AI Project in 60 Minutes" and start building.</p>
        </div>

        <?php if ($refCode): ?>
        <div style="background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: var(--radius-md); padding: 14px; margin-bottom: 24px; text-align: center; font-size: 0.9rem; color: #c7d2fe;">
            🎁 You're registering via friend's invite: <strong><?php echo $refCode; ?></strong>
        </div>
        <?php endif; ?>

        <form id="registration-form" method="POST" action="api/register.php">
            <input type="hidden" name="source" value="<?php echo $source; ?>">
            <input type="hidden" name="ambassador" value="<?php echo $ambCode; ?>">

            <div class="form-group">
                <label class="form-label" for="reg-page-name">Full Name *</label>
                <input type="text" id="reg-page-name" name="name" class="form-input" placeholder="e.g. Sneha Sharma" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-page-email">Email Address (Academic/Personal) *</label>
                <input type="email" id="reg-page-email" name="email" class="form-input" placeholder="e.g. sneha@college.edu" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="reg-page-college">College / University *</label>
                    <input type="text" id="reg-page-college" name="college" class="form-input" placeholder="e.g. Amrita / VIT / IIT" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-page-branch">Engineering Branch *</label>
                    <input type="text" id="reg-page-branch" name="branch" class="form-input" placeholder="e.g. CSE / AI&DS / ECE" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="reg-page-grad">Graduation Year *</label>
                    <select id="reg-page-grad" name="graduation_year" class="form-select" required>
                        <option value="2026" selected>2026 (Final Year)</option>
                        <option value="2027">2027 (Pre-final Year)</option>
                        <option value="2025">2025</option>
                        <option value="2028">2028</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="referral_code_input">Referral Code (Optional)</label>
                    <input type="text" id="referral_code_input" name="referral_code" value="<?php echo $refCode; ?>" class="form-input" placeholder="e.g. SNEHA42" style="text-transform: uppercase;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 12px;">
                Complete Registration ⚡
            </button>
            <div style="text-align: center; margin-top: 12px; font-size: 0.8rem; color: var(--text-dim);">
                🛡️ Instant access. You will immediately receive your personal referral link.
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
