$css = @"

/* =========================================================
   HOSPITAL FINDER PAGE  (hospitals.php)
   ========================================================= */

.hosp-page {
    padding-top: 5rem;
    min-height: 100vh;
}

.hosp-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2.5rem 1.25rem 4rem;
}

/* ── Barangay Selector ──────────────────────────────────── */
.hosp-selector-card {
    background: var(--white, #fff);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(236,72,153,.10), 0 1px 6px rgba(0,0,0,.05);
    padding: 1.8rem 2rem;
    margin-bottom: 2rem;
}
[data-theme="dark"] .hosp-selector-card {
    background: var(--card-bg, #1e1b2e);
    box-shadow: 0 4px 24px rgba(0,0,0,.3);
}

.hosp-selector-inner {
    display: flex;
    flex-direction: column;
    gap: .6rem;
}

.hosp-selector-label {
    font-size: .9rem;
    font-weight: 700;
    color: var(--ink, #1a1130);
    display: flex;
    align-items: center;
    gap: .45rem;
}
.hosp-selector-label i { color: #ec4899; }
[data-theme="dark"] .hosp-selector-label { color: #f0ebff; }

.hosp-selector-wrap {
    position: relative;
}

.hosp-select {
    width: 100%;
    padding: .8rem 2.8rem .8rem 1rem;
    font-size: .95rem;
    font-family: inherit;
    font-weight: 500;
    border: 1.5px solid #e9d5ff;
    border-radius: 12px;
    background: var(--input-bg, #faf6ff);
    color: var(--ink, #1a1130);
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
}
.hosp-select:focus {
    outline: none;
    border-color: #a855f7;
    box-shadow: 0 0 0 3px rgba(168,85,247,.15);
}
[data-theme="dark"] .hosp-select {
    background: #2a2240;
    border-color: #4c3d6e;
    color: #f0ebff;
}

.hosp-select-caret {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #a855f7;
    font-size: .8rem;
    pointer-events: none;
}

/* ── Section Header ─────────────────────────────────────── */
.hosp-section-hdr {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.2rem;
}
.hosp-section-hdr > i {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ec4899, #a855f7);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.hosp-section-hdr h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--ink, #1a1130);
    margin: 0 0 .2rem;
}
[data-theme="dark"] .hosp-section-hdr h2 { color: #f0ebff; }
.hosp-section-hdr p {
    font-size: .83rem;
    color: var(--muted, #8b7aa3);
    margin: 0;
}

/* ── Hospital Grid ──────────────────────────────────────── */
.hosp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.2rem;
    margin-bottom: 1rem;
}

/* ── Hospital Card ──────────────────────────────────────── */
.hosp-card {
    background: var(--white, #fff);
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,.07);
    padding: 1.3rem 1.4rem 1.2rem;
    display: flex;
    flex-direction: column;
    gap: .85rem;
    position: relative;
    overflow: hidden;
    transition: box-shadow .2s, transform .15s;
}
.hosp-card:hover {
    box-shadow: 0 8px 32px rgba(168,85,247,.18);
    transform: translateY(-2px);
}
[data-theme="dark"] .hosp-card {
    background: var(--card-bg, #1e1b2e);
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
}

/* Nearest badge */
.hosp-nearest-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .72rem;
    font-weight: 700;
    background: linear-gradient(135deg, #ec4899, #a855f7);
    color: #fff;
    padding: .22rem .7rem;
    border-radius: 99px;
    width: fit-content;
    margin-bottom: -.2rem;
    letter-spacing: .02em;
}

/* Top row */
.hosp-card-top {
    display: flex;
    gap: .9rem;
    align-items: flex-start;
}
.hosp-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f5f0ff, #fce7f3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a855f7;
    font-size: 1.1rem;
    flex-shrink: 0;
}
[data-theme="dark"] .hosp-icon-wrap { background: #2d2448; }

.hosp-card-title-group {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.hosp-name {
    font-size: .95rem;
    font-weight: 700;
    color: var(--ink, #1a1130);
    margin: 0;
    line-height: 1.3;
}
[data-theme="dark"] .hosp-name { color: #f0ebff; }

.hosp-type-tag,
.hosp-level-tag {
    display: inline-block;
    font-size: .7rem;
    font-weight: 600;
    border-radius: 6px;
    padding: .1rem .5rem;
    margin-right: .3rem;
}
.hosp-type-tag {
    background: #f5f0ff;
    color: #7c3aed;
    border: 1px solid #e9d5ff;
}
.hosp-level-tag {
    background: #f0f9ff;
    color: #0284c7;
    border: 1px solid #bae6fd;
}
[data-theme="dark"] .hosp-type-tag { background: #2d2448; border-color: #4c3d6e; color: #c4b5fd; }
[data-theme="dark"] .hosp-level-tag { background: #0c2233; border-color: #0369a1; color: #7dd3fc; }

/* Cost banner */
.hosp-cost-banner {
    display: flex;
    align-items: center;
    gap: .85rem;
    border-radius: 10px;
    border: 1px solid;
    padding: .65rem .9rem;
}
.hosp-cost-pesos {
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1;
    flex-shrink: 0;
}
.hosp-cost-label {
    font-size: .82rem;
    font-weight: 700;
    line-height: 1.2;
}
.hosp-cost-note {
    font-size: .75rem;
    color: var(--muted, #8b7aa3);
    margin-top: .15rem;
    line-height: 1.4;
}

/* Info rows */
.hosp-info-row {
    display: flex;
    align-items: flex-start;
    gap: .55rem;
    font-size: .82rem;
    color: var(--muted, #8b7aa3);
    line-height: 1.45;
}
.hosp-info-row i {
    color: #ec4899;
    flex-shrink: 0;
    margin-top: .15rem;
    font-size: .8rem;
}
.hosp-phone-link {
    color: #a855f7;
    text-decoration: none;
    font-weight: 600;
}
.hosp-phone-link:hover { text-decoration: underline; }

/* Services */
.hosp-services {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
}
.hosp-service-tag {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .7rem;
    font-weight: 600;
    background: var(--surface, #f5f0ff);
    color: #7c3aed;
    border-radius: 6px;
    padding: .15rem .55rem;
}
.hosp-service-tag i { font-size: .6rem; color: #22c55e; }
[data-theme="dark"] .hosp-service-tag { background: #2d2448; color: #c4b5fd; }

/* Card footer */
.hosp-card-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: .6rem;
    border-top: 1px solid var(--border, #f0e8ff);
}
[data-theme="dark"] .hosp-card-foot { border-color: #2d2448; }

.hosp-emrg-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .72rem;
    font-weight: 700;
    background: #fff1f2;
    color: #be123c;
    border: 1px solid #fecdd3;
    border-radius: 99px;
    padding: .2rem .65rem;
}
.hosp-emrg-badge.hosp-emrg-no {
    background: #f9fafb;
    color: #6b7280;
    border-color: #e5e7eb;
}
[data-theme="dark"] .hosp-emrg-badge { background: #3f0e1e; border-color: #9f1239; color: #fda4af; }
[data-theme="dark"] .hosp-emrg-badge.hosp-emrg-no { background: #1f2937; border-color: #374151; color: #9ca3af; }

.hosp-map-btn {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .78rem;
    font-weight: 700;
    padding: .32rem .8rem;
    border-radius: 8px;
    background: linear-gradient(135deg, #ec4899, #a855f7);
    color: #fff;
    text-decoration: none;
    transition: opacity .2s, transform .15s;
    white-space: nowrap;
}
.hosp-map-btn:hover {
    opacity: .88;
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 640px) {
    .hosp-grid { grid-template-columns: 1fr; }
    .hosp-selector-card { padding: 1.3rem 1.1rem; }
    .hosp-container { padding: 2rem .85rem 3rem; }
}
"@

Add-Content -Path "C:\xampp\htdocs\Pregnancy\assets\css\style.css" -Value $css -Encoding UTF8
Write-Host "Done. Lines: $((Get-Content 'C:\xampp\htdocs\Pregnancy\assets\css\style.css').Count)"
