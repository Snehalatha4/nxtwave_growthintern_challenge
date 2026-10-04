/**
 * Student Dashboard & Smart WhatsApp Copy Generator
 */

const StudentDashboard = {
    user: null,
    stats: null,
    selectedCopyType: 'friendly',

    shareCopies: {
        friendly: (link) => `Hey! I'm joining a free workshop to build my first AI project in 60 minutes. You can join too: ${link}`,
        placement: (link) => `Want to build something AI-based for your portfolio? There's a free 60-minute hands-on workshop here: ${link}`,
        short: (link) => `Free AI project workshop — 60 minutes. I'm joining. You should too: ${link}`
    },

    init(code) {
        if (!code) return;
        this.fetchStudentData(code);
        this.bindShareSelectors();
    },

    async fetchStudentData(code) {
        try {
            const res = await fetch(`api/referral.php?code=${encodeURIComponent(code)}`);
            const data = await res.json();
            if (data.success) {
                this.user = data.user;
                this.stats = data.stats;
                this.render(data);
            }
        } catch (err) {
            console.error('Failed to load referral stats:', err);
        }
    },

    render(data) {
        const { user, stats, recent_referrals } = data;

        // Populate User Info
        const studentNameEl = document.getElementById('student-name');
        if (studentNameEl) studentNameEl.innerText = user.name;

        const referralCodeEl = document.getElementById('referral-code-val');
        if (referralCodeEl) referralCodeEl.innerText = user.referral_code;

        const referralLinkInput = document.getElementById('referral-link-input');
        if (referralLinkInput) referralLinkInput.value = user.referral_link;

        // Populate Stats
        const countEl = document.getElementById('referral-count');
        if (countEl) countEl.innerText = stats.total_referrals;

        const nextTargetEl = document.getElementById('next-target-count');
        if (nextTargetEl) nextTargetEl.innerText = stats.target_for_next;

        const progressBar = document.getElementById('milestone-progress-bar');
        if (progressBar) {
            progressBar.style.width = stats.progress_percent + '%';
        }

        const nextActionEl = document.getElementById('smart-next-action-text');
        if (nextActionEl) {
            nextActionEl.innerText = stats.smart_next_action;
        }

        // Highlight Active Milestone
        document.querySelectorAll('.milestone-node').forEach(node => {
            const req = parseInt(node.getAttribute('data-req') || '0', 10);
            if (stats.total_referrals >= req) {
                node.classList.add('achieved');
            } else if (req === stats.target_for_next) {
                node.classList.add('active');
            }
        });

        // Update WhatsApp Share Link
        this.updateWhatsAppUrl();

        // Render Recent Referral Activity
        const recentList = document.getElementById('recent-referrals-list');
        if (recentList) {
            if (recent_referrals && recent_referrals.length > 0) {
                recentList.innerHTML = recent_referrals.map(r => `
                    <tr>
                        <td><strong>${r.referred_name}</strong></td>
                        <td>${r.referred_college}</td>
                        <td><span class="badge badge-indigo">${r.referred_branch}</span></td>
                        <td><span class="badge badge-emerald">Joined via your link</span></td>
                        <td>${new Date(r.created_at).toLocaleDateString()}</td>
                    </tr>
                `).join('');
            } else {
                recentList.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 28px;">
                            No classmates joined through your link yet. Share below to get your first referral!
                        </td>
                    </tr>
                `;
            }
        }
    },

    bindShareSelectors() {
        document.querySelectorAll('.js-share-copy-option').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.js-share-copy-option').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                this.selectedCopyType = card.getAttribute('data-copy-type') || 'friendly';
                this.updateWhatsAppUrl();

                if (window.App) {
                    window.App.logEvent('share_copy_selected', { copy_type: this.selectedCopyType });
                }
            });
        });
    },

    updateWhatsAppUrl() {
        if (!this.user) return;
        const link = this.user.referral_link;
        const messageGen = this.shareCopies[this.selectedCopyType] || this.shareCopies.friendly;
        const fullMessage = messageGen(link);

        const waBtn = document.getElementById('whatsapp-share-btn');
        if (waBtn) {
            waBtn.href = `https://api.whatsapp.com/send?text=${encodeURIComponent(fullMessage)}`;
            waBtn.onclick = () => {
                if (window.App) {
                    window.App.logEvent('whatsapp_clicked', { copy_type: this.selectedCopyType });
                }
            };
        }

        const previewEl = document.getElementById('whatsapp-message-preview');
        if (previewEl) {
            previewEl.innerText = fullMessage;
        }
    }
};
