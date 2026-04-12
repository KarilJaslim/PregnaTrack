$css = @"

/* =========================================================
   ASSESSMENT — Tips & Medical Guidance panels
   ========================================================= */

/* --- Health Tips (green — normal + watch blocks) ---------- */
.assess-tips {
    margin-top: 1.1rem;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1px solid #86efac;
    border-radius: 12px;
    padding: 1rem 1.2rem 1.1rem;
}
[data-theme="dark"] .assess-tips {
    background: rgba(20,83,45,.18);
    border-color: #166534;
}
.assess-tips-hdr {
    font-weight: 700;
    font-size: .875rem;
    color: #15803d;
    margin-bottom: .65rem;
    display: flex;
    align-items: center;
    gap: .45rem;
}
[data-theme="dark"] .assess-tips-hdr { color: #86efac; }
.assess-tips-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .45rem;
}
.assess-tips-list li {
    font-size: .845rem;
    color: #166534;
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    line-height: 1.5;
}
[data-theme="dark"] .assess-tips-list li { color: #bbf7d0; }
.assess-tips-list li i {
    color: #22c55e;
    font-size: .72rem;
    flex-shrink: 0;
    margin-top: .25rem;
}

/* --- Medical Guidance (orange — warning block) ------------ */
.assess-medical {
    margin-top: 1.1rem;
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    border: 1px solid #fed7aa;
    border-radius: 12px;
    padding: 1rem 1.2rem 1.1rem;
}
[data-theme="dark"] .assess-medical {
    background: rgba(124,45,18,.18);
    border-color: #9a3412;
}
.assess-medical-hdr {
    font-weight: 700;
    font-size: .875rem;
    color: #c2410c;
    margin-bottom: .65rem;
    display: flex;
    align-items: center;
    gap: .45rem;
}
[data-theme="dark"] .assess-medical-hdr { color: #fb923c; }
.assess-medical-list {
    list-style: none;
    margin: 0 0 .85rem;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.assess-medical-list li {
    font-size: .845rem;
    color: #7c2d12;
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    line-height: 1.5;
}
[data-theme="dark"] .assess-medical-list li { color: #fed7aa; }
.assess-medical-list li i {
    font-size: .8rem;
    flex-shrink: 0;
    margin-top: .2rem;
    color: #ea580c;
}

/* Doctor link buttons row */
.assess-doc-links {
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
}
.assess-map-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .8rem;
    font-weight: 600;
    padding: .38rem .85rem;
    background: #fff;
    border: 1.5px solid #ea580c;
    border-radius: 8px;
    color: #c2410c;
    text-decoration: none;
    transition: background .2s, color .2s;
}
.assess-map-link:hover {
    background: #ea580c;
    color: #fff;
}
[data-theme="dark"] .assess-map-link {
    background: rgba(67,20,7,.5);
    border-color: #9a3412;
    color: #fed7aa;
}
[data-theme="dark"] .assess-map-link:hover {
    background: #9a3412;
    color: #fff;
}

/* Inline link inside the medical list (emergency hospital link) */
.assess-map-link-inline {
    font-weight: 700;
    color: #be123c;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.assess-map-link-inline:hover { color: #9f1239; }
[data-theme="dark"] .assess-map-link-inline { color: #fda4af; }

/* --- Emergency Guidance (red — emergency block) ----------- */
.assess-medical.assess-medical-emrg {
    background: linear-gradient(135deg, #fff1f2, #ffe4e6);
    border-color: #fecdd3;
}
[data-theme="dark"] .assess-medical.assess-medical-emrg {
    background: rgba(136,19,55,.2);
    border-color: #be123c;
}
.assess-medical.assess-medical-emrg .assess-medical-hdr { color: #be123c; }
[data-theme="dark"] .assess-medical.assess-medical-emrg .assess-medical-hdr { color: #fb7185; }
.assess-medical.assess-medical-emrg .assess-medical-list li { color: #881337; }
[data-theme="dark"] .assess-medical.assess-medical-emrg .assess-medical-list li { color: #fecdd3; }
.assess-medical.assess-medical-emrg .assess-medical-list li i { color: #e11d48; }
"@

Add-Content -Path "C:\xampp\htdocs\Pregnancy\assets\css\style.css" -Value $css -Encoding UTF8
Write-Host "Done. Lines: $((Get-Content 'C:\xampp\htdocs\Pregnancy\assets\css\style.css').Count)"
