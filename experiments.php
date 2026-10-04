<?php
/**
 * Growth Experiments & A/B Testing Laboratory
 * Demonstrates systematic experimentation, hypothesis formulation, and metric evaluation.
 */
require_once __DIR__ . '/config/database.php';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 24px;">
    <!-- Experiments Header -->
    <div style="max-width: 780px; margin-bottom: 36px;">
        <span class="badge badge-emerald" style="margin-bottom: 8px;">Scientific Growth Methodology</span>
        <h1 style="font-size: 2.4rem; color: #fff;">Growth Experiments Hub</h1>
        <p style="font-size: 1.05rem; color: var(--text-muted);">
            We never assume our first intuition is correct. Every messaging angle, friction point, and viral mechanism is treated as a testable hypothesis backed by quantifiable metrics.
        </p>
    </div>

    <!-- Overview Banner -->
    <div class="card" style="background: linear-gradient(135deg, rgba(30, 27, 75, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%); border-color: rgba(99, 102, 241, 0.4); margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <span class="badge badge-indigo">Framework: Hypothesis &rarr; What to Test &rarr; Metric</span>
                <h3 style="font-size: 1.25rem; color: #fff; margin-top: 6px;">3 Active Growth Experiments</h3>
                <p style="font-size: 0.85rem; color: var(--text-dim);">Simulated data is tracked and rendered in real time from the analytics database.</p>
            </div>
            <div>
                <span class="badge badge-demo">Data Mode: Simulated Test Cohorts</span>
            </div>
        </div>
    </div>

    <!-- Experiment Cards Container -->
    <div id="experiments-list-container">
        <!-- Rendered via assets/js/experiments.js -->
        <div style="text-align: center; color: var(--text-dim); padding: 40px;">
            Loading active growth experiments...
        </div>
    </div>

    <!-- Strategic Takeaways Section -->
    <div class="card" style="margin-top: 36px;">
        <h3 style="font-size: 1.3rem; color: #fff; margin-bottom: 12px;">Growth Strategy Summary: What We Learned</h3>
        <div class="grid-3" style="gap: 20px;">
            <div style="background: rgba(255,255,255,0.02); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <div style="font-weight: 700; color: #a5b4fc; margin-bottom: 6px;">1. Placement Anchor Wins</div>
                <p style="font-size: 0.85rem;">Positioning the workshop around "Resume-ready project" generates a +38% higher conversion rate among final-year students compared to generic AI learning.</p>
            </div>

            <div style="background: rgba(255,255,255,0.02); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <div style="font-weight: 700; color: #34d399; margin-bottom: 6px;">2. Post-Reg Referral Momentum</div>
                <p style="font-size: 0.85rem;">Displaying pre-filled WhatsApp copy immediately on registration completion boosts the viral share rate from 14% to 48%, turning 1 registrant into 0.38 incremental registrants.</p>
            </div>

            <div style="background: rgba(255,255,255,0.02); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <div style="font-weight: 700; color: #fbbf24; margin-bottom: 6px;">3. Seat Reservation Framing</div>
                <p style="font-size: 0.85rem;">"Reserve My Free Seat" creates higher commitment and urgency than a generic "Register Free" button, increasing CTA click-through rate by +54%.</p>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/experiments.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
