<?php
/**
 * Footer Component with Growth Architecture Summary and Disclosure
 */
?>
<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <div>
                <div class="brand-logo" style="margin-bottom: 8px;">
                    <div class="brand-icon" style="width:28px; height:28px; font-size:0.9rem;">⚡</div>
                    <span><?php echo APP_NAME; ?></span>
                </div>
                <p style="font-size: 0.85rem; max-width: 480px;">
                    A viral referral growth engine prototype designed for the NxtWave Growth Challenge. Demonstrating Acquisition &rarr; Activation &rarr; Referral &rarr; Attribution &rarr; Experimentation.
                </p>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px;">
                    <strong>Simulation Model:</strong> 500 Students in 7 Days (₹2,000 Budget)
                </div>
                <div style="font-size: 0.78rem; color: var(--text-dim);">
                    Demo Data strictly labeled. Not an official NxtWave product.
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-subtle); margin-top: 24px; padding-top: 16px; display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--text-dim); flex-wrap: wrap; gap: 10px;">
            <div>&copy; <?php echo date('Y'); ?> BUILD IN 60 &bull; Growth Challenge Prototype</div>
            <div style="display: flex; gap: 16px;">
                <a href="index.php" style="color: var(--text-dim);">Home</a>
                <a href="dashboard.php" style="color: var(--text-dim);">Dashboard</a>
                <a href="experiments.php" style="color: var(--text-dim);">Experiments</a>
                <a href="admin.php" style="color: var(--text-dim);">Admin Growth Ops</a>
            </div>
        </div>
    </div>
</footer>

<!-- Global Registration Modal -->
<div id="register-modal" class="modal-overlay">
    <div class="modal-content">
        <button type="button" class="modal-close" id="modal-close-btn">&times;</button>
        <div style="margin-bottom: 20px;">
            <span class="badge badge-emerald" style="margin-bottom: 8px;">100% Free Live Workshop</span>
            <h2 style="font-size: 1.6rem; color: #fff;">Reserve Your Free Seat</h2>
            <p style="font-size: 0.88rem; color: var(--text-muted);">60-minute practical AI project build. Certificate included.</p>
        </div>

        <form id="registration-form" method="POST" action="api/register.php">
            <input type="hidden" name="source" id="form-source-hidden" value="direct">
            <input type="hidden" name="ambassador" id="form-ambassador-hidden" value="">

            <div class="form-group">
                <label class="form-label" for="reg-name">Full Name *</label>
                <input type="text" id="reg-name" name="name" class="form-input" placeholder="e.g. Sneha Sharma" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-email">Email Address (Academic/Personal) *</label>
                <input type="email" id="reg-email" name="email" class="form-input" placeholder="e.g. sneha@college.edu" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="reg-college">College / University *</label>
                    <input type="text" id="reg-college" name="college" class="form-input" placeholder="e.g. VIT / Amrita / IIT" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-branch">Branch *</label>
                    <input type="text" id="reg-branch" name="branch" class="form-input" placeholder="e.g. CSE / ECE / AI&DS" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="reg-grad">Graduation Year *</label>
                    <select id="reg-grad" name="graduation_year" class="form-select" required>
                        <option value="2026" selected>2026 (Final Year)</option>
                        <option value="2027">2027 (Pre-final Year)</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="referral_code_input">Referral Code (Optional)</label>
                    <input type="text" id="referral_code_input" name="referral_code" class="form-input" placeholder="e.g. SNEHA42" style="text-transform: uppercase;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;">
                Confirm My Registration ⚡
            </button>
            <div style="text-align: center; margin-top: 10px; font-size: 0.78rem; color: var(--text-dim);">
                🔒 No spam. You will immediately get a personal invite link to share with peers.
            </div>
        </form>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
