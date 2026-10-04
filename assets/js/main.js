/**
 * BUILD IN 60 - Core Growth Engine & Client Logic
 */

const App = {
    sessionId: null,
    referralCode: null,
    source: 'direct',
    ambassador: null,

    init() {
        this.initSession();
        this.extractParams();
        this.logEvent('page_view', { path: window.location.pathname });
        this.bindEvents();
    },

    initSession() {
        this.sessionId = localStorage.getItem('build60_session_id');
        if (!this.sessionId) {
            this.sessionId = 'sess_' + Math.random().toString(36).substring(2, 12) + '_' + Date.now();
            localStorage.setItem('build60_session_id', this.sessionId);
        }
    },

    extractParams() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Capture Referral Code (?ref=SNEHA42)
        if (urlParams.has('ref')) {
            this.referralCode = urlParams.get('ref').toUpperCase().trim();
            sessionStorage.setItem('build60_ref_code', this.referralCode);
            this.source = 'referral';
        } else if (sessionStorage.getItem('build60_ref_code')) {
            this.referralCode = sessionStorage.getItem('build60_ref_code');
            this.source = 'referral';
        }

        // Capture Ambassador Code (?ambassador=AMRITA_AI_CLUB)
        if (urlParams.has('ambassador')) {
            this.ambassador = urlParams.get('ambassador').toUpperCase().trim();
            sessionStorage.setItem('build60_ambassador', this.ambassador);
            this.source = 'ambassador';
        } else if (sessionStorage.getItem('build60_ambassador')) {
            this.ambassador = sessionStorage.getItem('build60_ambassador');
        }

        // Capture Source (?source=whatsapp / club / email)
        if (urlParams.has('source')) {
            this.source = urlParams.get('source').toLowerCase().trim();
            sessionStorage.setItem('build60_source', this.source);
        } else if (sessionStorage.getItem('build60_source')) {
            this.source = sessionStorage.getItem('build60_source');
        }

        // Auto-fill referral input on registration forms if found
        const refInput = document.getElementById('referral_code_input');
        if (refInput && this.referralCode) {
            refInput.value = this.referralCode;
            refInput.style.borderColor = '#6366f1';
        }
    },

    logEvent(eventType, metadata = {}) {
        const payload = {
            event_type: eventType,
            session_id: this.sessionId,
            source: this.source,
            metadata: metadata
        };

        fetch('api/analytics.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).catch(err => console.debug('Analytics log skipped:', err));
    },

    bindEvents() {
        // Register CTA clicks (log registration start)
        document.querySelectorAll('.js-register-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.logEvent('registration_started');
                const modal = document.getElementById('register-modal');
                if (modal) {
                    modal.style.display = 'flex';
                }
            });
        });

        // Close Modal
        const closeBtn = document.getElementById('modal-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                document.getElementById('register-modal').style.display = 'none';
            });
        }

        // Copy Link Buttons
        document.querySelectorAll('.js-copy-link').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target') || 'referral-link-input';
                const input = document.getElementById(targetId);
                if (input) {
                    navigator.clipboard.writeText(input.value || input.innerText).then(() => {
                        this.showToast('Referral link copied to clipboard! 📋');
                        this.logEvent('referral_link_copied');
                    });
                }
            });
        });

        // Form Submission AJAX
        const regForm = document.getElementById('registration-form');
        if (regForm) {
            regForm.addEventListener('submit', (e) => this.handleRegistration(e, regForm));
        }
    },

    async handleRegistration(e, form) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Securing Your Seat... ⏳';
        submitBtn.disabled = true;

        const formData = new FormData(form);
        if (this.source) formData.append('source', this.source);
        if (this.ambassador) formData.append('ambassador', this.ambassador);

        try {
            const res = await fetch('api/register.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                this.showToast('Registration successful! Redirecting...');
                this.logEvent('registration_completed', { referral_code: data.referral_code });
                setTimeout(() => {
                    window.location.href = data.redirect_url || 'success.php?code=' + data.referral_code;
                }, 400);
            } else {
                const errorMsg = data.errors ? data.errors.join('<br>') : (data.error || 'Registration failed');
                this.showToast(errorMsg, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (err) {
            this.showToast('Connection error. Please try again.', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    },

    showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'toast';
        if (type === 'error') {
            toast.style.borderColor = '#f43f5e';
            toast.style.background = '#4c0519';
        }
        toast.innerHTML = `<span>${type === 'error' ? '⚠️' : '✅'}</span> <div>${message}</div>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());
