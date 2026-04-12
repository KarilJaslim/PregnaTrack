$css = "
/* =========================================================
   LOGIN PAGE  (login.php)
   ========================================================= */

.login-body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.login-main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6rem 1rem 3rem;
}

/* Card */
.login-card {
    background: var(--white, #fff);
    border-radius: 24px;
    box-shadow: 0 8px 40px rgba(236,72,153,.12), 0 2px 12px rgba(0,0,0,.06);
    padding: 2.5rem 2rem 2rem;
    width: 100%;
    max-width: 440px;
    text-align: center;
    position: relative;
}

[data-theme=""dark""] .login-card {
    background: var(--card-bg, #1e1b2e);
    box-shadow: 0 8px 40px rgba(0,0,0,.4);
}

/* Brand badge */
.login-brand-badge {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ec4899, #a855f7);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 16px rgba(236,72,153,.35);
}

.login-brand-heart {
    font-size: 1.5rem;
    color: #fff;
    line-height: 1;
}

/* Heading */
.login-heading {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.45rem;
    font-weight: 700;
    color: var(--ink, #1a1130);
    margin: 0 0 .3rem;
    line-height: 1.25;
}

[data-theme=""dark""] .login-heading { color: var(--ink-dark, #f0ebff); }

.login-subhead {
    font-size: .9rem;
    color: var(--muted, #8b7aa3);
    margin: 0 0 1.5rem;
}

/* Status banner */
.login-status {
    border-radius: 10px;
    padding: .65rem 1rem;
    font-size: .875rem;
    font-weight: 500;
    margin-bottom: 1rem;
    text-align: left;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.login-status.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.login-status.error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
[data-theme=""dark""] .login-status.success { background: #14532d33; border-color: #166534; color: #86efac; }
[data-theme=""dark""] .login-status.error   { background: #7f1d1d33; border-color: #991b1b; color: #fca5a5; }

/* Tabs */
.login-tabs {
    display: flex;
    gap: .5rem;
    background: var(--surface, #f5f0ff);
    border-radius: 12px;
    padding: .3rem;
    margin-bottom: 1.4rem;
}

[data-theme=""dark""] .login-tabs { background: #2d2640; }

.login-tab {
    flex: 1;
    padding: .55rem .5rem;
    border: none;
    border-radius: 9px;
    background: transparent;
    font-size: .855rem;
    font-weight: 600;
    color: var(--muted, #8b7aa3);
    cursor: pointer;
    transition: background .2s, color .2s, box-shadow .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
}

.login-tab.active {
    background: linear-gradient(135deg, #ec4899, #a855f7);
    color: #fff;
    box-shadow: 0 2px 10px rgba(236,72,153,.3);
}

.login-tab:not(.active):hover {
    background: var(--hover-bg, rgba(168,85,247,.08));
    color: var(--ink, #1a1130);
}

[data-theme=""dark""] .login-tab:not(.active):hover { color: #f0ebff; }

/* Panels */
.login-panel { display: none; text-align: left; }
.login-panel.active { display: block; }

/* Fields */
.login-field {
    margin-bottom: 1rem;
}

.login-field label {
    display: block;
    font-size: .82rem;
    font-weight: 600;
    color: var(--ink, #1a1130);
    margin-bottom: .4rem;
}

[data-theme=""dark""] .login-field label { color: #c4b5fd; }

.login-input-wrap {
    position: relative;
}

.login-input-wrap input {
    width: 100%;
    padding: .7rem 1rem;
    border: 1.5px solid #e9d5ff;
    border-radius: 10px;
    font-size: .9rem;
    font-family: inherit;
    background: var(--input-bg, #faf6ff);
    color: var(--ink, #1a1130);
    transition: border-color .2s, box-shadow .2s;
    box-sizing: border-box;
}

.login-input-wrap input:focus {
    outline: none;
    border-color: #a855f7;
    box-shadow: 0 0 0 3px rgba(168,85,247,.15);
}

[data-theme=""dark""] .login-input-wrap input {
    background: #2a2240;
    border-color: #4c3d6e;
    color: #f0ebff;
}

/* Password toggle eye */
.login-pw-wrap input { padding-right: 2.8rem; }

.login-eye {
    position: absolute;
    right: .8rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--muted, #8b7aa3);
    padding: .2rem;
    font-size: .9rem;
}
.login-eye:hover { color: #a855f7; }

/* OTP badge */
.login-otp-wrap input { padding-right: 6.5rem; }
.otp-badge {
    position: absolute;
    right: .7rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: .75rem;
    font-weight: 600;
    color: #15803d;
    background: #dcfce7;
    border-radius: 20px;
    padding: .15rem .55rem;
    white-space: nowrap;
}

/* OTP controls row */
.otp-controls {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-top: .45rem;
}

.otp-countdown {
    font-size: .8rem;
    color: var(--muted, #8b7aa3);
}

/* Buttons */
.login-btn-primary {
    width: 100%;
    padding: .8rem 1rem;
    border: none;
    border-radius: 12px;
    font-size: .95rem;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    background: linear-gradient(135deg, #ec4899, #a855f7);
    color: #fff;
    box-shadow: 0 4px 16px rgba(236,72,153,.3);
    transition: opacity .2s, transform .15s, box-shadow .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    margin-top: .5rem;
}

.login-btn-primary:hover:not(:disabled) {
    opacity: .92;
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(236,72,153,.38);
}

.login-btn-primary:disabled { opacity: .65; cursor: not-allowed; }

.login-btn-secondary {
    padding: .45rem .9rem;
    border: 1.5px solid #a855f7;
    border-radius: 8px;
    font-size: .82rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    background: transparent;
    color: #a855f7;
    transition: background .2s, color .2s;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
}

.login-btn-secondary:hover:not(:disabled) {
    background: #a855f7;
    color: #fff;
}

.login-btn-secondary:disabled { opacity: .55; cursor: not-allowed; }

/* Divider */
.login-divider {
    position: relative;
    text-align: center;
    margin: 1.2rem 0;
    color: var(--muted, #8b7aa3);
    font-size: .8rem;
}
.login-divider::before {
    content: '';
    position: absolute;
    inset: 50% 0 auto;
    height: 1px;
    background: var(--border, #e9d5ff);
}
.login-divider span {
    position: relative;
    background: var(--white, #fff);
    padding: 0 .75rem;
}
[data-theme=""dark""] .login-divider span { background: var(--card-bg, #1e1b2e); }

/* Google button */
.login-btn-google {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .75rem;
    width: 100%;
    padding: .75rem 1rem;
    border: 1.5px solid var(--border, #e9d5ff);
    border-radius: 12px;
    font-size: .9rem;
    font-weight: 600;
    color: var(--ink, #1a1130);
    background: var(--white, #fff);
    text-decoration: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
    box-sizing: border-box;
}
.login-btn-google:hover {
    border-color: #a855f7;
    box-shadow: 0 3px 12px rgba(168,85,247,.18);
}
[data-theme=""dark""] .login-btn-google {
    background: #2a2240;
    border-color: #4c3d6e;
    color: #f0ebff;
}

.google-icon { width: 20px; height: 20px; flex-shrink: 0; }

/* Helper text */
.login-helper {
    font-size: .78rem;
    color: var(--muted, #8b7aa3);
    margin: 1rem 0 0;
    text-align: center;
}

/* Password strength */
.password-strength { margin-top: .5rem; }
.strength-bar {
    height: 4px;
    background: var(--border, #e9d5ff);
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: .3rem;
}
.strength-fill {
    height: 100%;
    width: 0;
    border-radius: 99px;
    transition: width .3s, background-color .3s;
}
.strength-text { font-size: .75rem; color: var(--muted, #8b7aa3); }

/* Responsive */
@media (max-width: 480px) {
    .login-card { padding: 2rem 1.2rem 1.5rem; border-radius: 18px; }
    .login-heading { font-size: 1.25rem; }
    .login-main { padding: 5.5rem .75rem 2rem; }
}
"

Add-Content -Path "C:\xampp\htdocs\Pregnancy\assets\css\style.css" -Value $css -Encoding UTF8
Write-Host "Done. Lines: $((Get-Content 'C:\xampp\htdocs\Pregnancy\assets\css\style.css').Count)"
