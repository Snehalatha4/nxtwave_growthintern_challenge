/**
 * Growth Experiments Dashboard Logic (A/B Testing Visualizer)
 */

const GrowthExperiments = {
    init() {
        this.fetchExperiments();
    },

    async fetchExperiments() {
        try {
            const res = await fetch('api/experiments.php');
            const data = await res.json();
            if (data.success) {
                this.renderExperiments(data.experiments);
            }
        } catch (err) {
            console.error('Failed to load growth experiments:', err);
        }
    },

    renderExperiments(experiments) {
        const container = document.getElementById('experiments-list-container');
        if (!container) return;

        container.innerHTML = experiments.map(exp => `
            <div class="card" style="margin-bottom: 28px; border-left: 4px solid var(--brand-primary);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <span class="badge badge-indigo" style="margin-bottom: 8px;">A/B Experiment: ${exp.id}</span>
                        <h3 style="font-size: 1.4rem; color: #fff;">${exp.name}</h3>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-emerald">Status: ${exp.status.toUpperCase()}</span>
                        <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 4px;">Metric: ${exp.metric}</div>
                    </div>
                </div>

                <p style="font-size: 0.95rem; color: #cbd5e1; background: rgba(255,255,255,0.03); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 24px; border: 1px solid var(--border-subtle);">
                    <strong>💡 Hypothesis:</strong> ${exp.hypothesis}
                </p>

                <div class="form-grid-2" style="gap: 20px; margin-bottom: 20px;">
                    <!-- Variant A -->
                    <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="badge badge-indigo">Variant A (Control)</span>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">${exp.variant_a.impressions} Impressions</span>
                        </div>
                        <h4 style="font-size: 1.05rem; margin-bottom: 16px; color: #f1f5f9;">"${exp.variant_a.label}"</h4>
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <div style="font-size: 0.8rem; color: var(--text-dim);">Conversions</div>
                                <div style="font-size: 1.2rem; font-weight: 700; color: #fff;">${exp.variant_a.conversions}</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-dim);">Conversion Rate</div>
                                <div style="font-size: 1.5rem; font-weight: 800; color: #818cf8;">${exp.variant_a.conversion_rate}%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Variant B -->
                    <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid ${exp.relative_lift_percent > 0 ? '#10b981' : 'var(--border-subtle)'}; border-radius: var(--radius-md); padding: 20px; position: relative;">
                        ${exp.relative_lift_percent > 0 ? '<span class="badge badge-emerald" style="position: absolute; top: -10px; right: 20px;">WINNING VARIANT (+ ' + exp.relative_lift_percent + '% Lift)</span>' : ''}
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="badge badge-emerald">Variant B (Challenger)</span>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">${exp.variant_b.impressions} Impressions</span>
                        </div>
                        <h4 style="font-size: 1.05rem; margin-bottom: 16px; color: #f1f5f9;">"${exp.variant_b.label}"</h4>
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <div style="font-size: 0.8rem; color: var(--text-dim);">Conversions</div>
                                <div style="font-size: 1.2rem; font-weight: 700; color: #fff;">${exp.variant_b.conversions}</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-dim);">Conversion Rate</div>
                                <div style="font-size: 1.5rem; font-weight: 800; color: #34d399;">${exp.variant_b.conversion_rate}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 14px; font-size: 0.88rem;">
                    <div style="color: var(--text-muted);">
                        Statistical Confidence: <strong style="color: #6ee7b7;">${exp.confidence}</strong>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="index.php?v_${exp.id}=A" target="_blank" class="btn btn-secondary btn-sm">Preview Variant A ↗</a>
                        <a href="index.php?v_${exp.id}=B" target="_blank" class="btn btn-primary btn-sm">Preview Variant B ↗</a>
                    </div>
                </div>
            </div>
        `).join('');
    }
};

document.addEventListener('DOMContentLoaded', () => GrowthExperiments.init());
