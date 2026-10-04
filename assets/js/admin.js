/**
 * Admin Growth Analytics & Growth Ops Dashboard
 */

const AdminDashboard = {
    init() {
        this.fetchAnalytics();
        this.bindAdminControls();
    },

    async fetchAnalytics() {
        try {
            const res = await fetch('api/analytics.php');
            const data = await res.json();
            if (data.success) {
                this.renderSummary(data.summary);
                this.renderFunnel(data.funnel);
                this.renderChannels(data.channel_breakdown);
                this.renderLeaderboard(data.campus_leaderboard);
                this.renderAmbassadors(data.ambassadors);
            }
        } catch (err) {
            console.error('Failed to load growth metrics:', err);
        }
    },

    renderSummary(summary) {
        // Goal Progress
        const countEl = document.getElementById('metric-total-reg');
        if (countEl) countEl.innerText = summary.total_registrations;

        const goalProgressEl = document.getElementById('admin-goal-progress');
        if (goalProgressEl) goalProgressEl.style.width = summary.goal_progress_percent + '%';

        const goalTextEl = document.getElementById('admin-goal-text');
        if (goalTextEl) goalTextEl.innerText = `${summary.total_registrations} / ${summary.campaign_goal} (${summary.goal_progress_percent}%)`;

        // Metric Cards
        const refRegEl = document.getElementById('metric-referral-reg');
        if (refRegEl) refRegEl.innerText = summary.referral_registrations;

        const refRateEl = document.getElementById('metric-referral-rate');
        if (refRateEl) refRateEl.innerText = summary.referral_rate + '%';

        const viralKEl = document.getElementById('metric-viral-k');
        if (viralKEl) viralKEl.innerText = summary.viral_k_factor;

        const healthEl = document.getElementById('metric-referral-health');
        if (healthEl) {
            healthEl.innerText = summary.referral_health;
            healthEl.className = 'badge ' + (summary.viral_k_factor >= 0.3 ? 'badge-emerald' : 'badge-amber');
        }

        const avgRefEl = document.getElementById('metric-avg-referrals');
        if (avgRefEl) avgRefEl.innerText = summary.avg_referrals_per_student;
    },

    renderFunnel(funnel) {
        const container = document.getElementById('growth-funnel-container');
        if (!container) return;

        const maxCount = funnel.length > 0 ? funnel[0].count : 1;
        container.innerHTML = funnel.map(step => {
            const widthPercent = Math.max(12, Math.round((step.count / maxCount) * 100));
            return `
                <div class="funnel-row">
                    <div class="funnel-label">${step.stage}</div>
                    <div class="funnel-bar-wrapper">
                        <div class="funnel-bar-fill" style="width: ${widthPercent}%">
                            ${step.count.toLocaleString()}
                        </div>
                    </div>
                    <div class="funnel-metric">${step.drop}</div>
                </div>
            `;
        }).join('');
    },

    renderChannels(channels) {
        const tbody = document.getElementById('channel-breakdown-tbody');
        if (!tbody) return;

        const total = channels.reduce((acc, c) => acc + parseInt(c.count, 10), 0);
        tbody.innerHTML = channels.map(c => {
            const pct = total > 0 ? Math.round((c.count / total) * 100) : 0;
            return `
                <tr>
                    <td><strong>${this.formatChannel(c.source)}</strong></td>
                    <td>${c.count}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="flex:1; height:6px; background:rgba(255,255,255,0.08); border-radius:4px; overflow:hidden;">
                                <div style="height:100%; width:${pct}%; background:#6366f1;"></div>
                            </div>
                            <span style="font-size:0.8rem; width:35px;">${pct}%</span>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    },

    formatChannel(source) {
        const names = {
            'whatsapp': '💬 WhatsApp Student Groups',
            'ambassador': '🎓 Campus Ambassadors',
            'referral': '🔄 Peer Referral Loop',
            'club': '🤖 Tech/Coding Clubs',
            'email': '📧 Direct Email Outreach',
            'direct': '🌐 Direct Web Traffic'
        };
        return names[source] || source.toUpperCase();
    },

    renderLeaderboard(leaderboard) {
        const tbody = document.getElementById('campus-leaderboard-tbody');
        if (!tbody) return;

        tbody.innerHTML = leaderboard.map((c, i) => `
            <tr>
                <td><strong>#${i + 1}</strong></td>
                <td>${c.college}</td>
                <td><span class="badge badge-indigo">${c.count} students</span></td>
            </tr>
        `).join('');
    },

    renderAmbassadors(ambassadors) {
        const tbody = document.getElementById('ambassadors-tbody');
        if (!tbody) return;

        tbody.innerHTML = ambassadors.map(a => {
            const pct = Math.min(100, Math.round((a.actual_registrations / a.target_registrations) * 100));
            return `
                <tr>
                    <td><strong>${a.name}</strong></td>
                    <td>${a.college}</td>
                    <td><code>${a.code}</code></td>
                    <td>${a.actual_registrations} / ${a.target_registrations}</td>
                    <td><span class="badge ${pct >= 70 ? 'badge-emerald' : 'badge-amber'}">${pct}%</span></td>
                </tr>
            `;
        }).join('');
    },

    bindAdminControls() {
        // Seed Demo Button
        const seedBtn = document.getElementById('seed-demo-btn');
        if (seedBtn) {
            seedBtn.addEventListener('click', async () => {
                seedBtn.disabled = true;
                seedBtn.innerText = 'Seeding Data...';
                try {
                    const res = await fetch('api/admin_actions.php?action=seed_demo');
                    const data = await res.json();
                    if (data.success) {
                        if (window.App) window.App.showToast('Demo data seeded! 427 registrations simulated.');
                        this.fetchAnalytics();
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    seedBtn.disabled = false;
                    seedBtn.innerText = '⚡ Seed 427 Demo Registrations';
                }
            });
        }

        // Reset Data Button
        const resetBtn = document.getElementById('reset-data-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', async () => {
                if (confirm('Reset database to 0 registrations for a clean live demo run?')) {
                    resetBtn.disabled = true;
                    try {
                        const res = await fetch('api/admin_actions.php?action=reset_data');
                        const data = await res.json();
                        if (data.success) {
                            if (window.App) window.App.showToast('Database reset to clean state.');
                            this.fetchAnalytics();
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        resetBtn.disabled = false;
                    }
                }
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => AdminDashboard.init());
