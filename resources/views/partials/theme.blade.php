{{-- Tema terpusat untuk seluruh layout (storefront & dashboard).
     Berisi font premium, design tokens (warna/radius/shadow), dan polesan
     komponen Tabler agar terasa modern & e-commerce. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root{
        /* Brand */
        --brand-50:  #ecfdf5;
        --brand-100: #d1fae5;
        --brand-200: #a7f3d0;
        --brand-500: #10b981;
        --brand-600: #059669;
        --brand-700: #047857;
        --brand-800: #065f46;
        --accent:    #f59e0b;
        --accent-d:  #d97706;

        /* Surface & ink */
        --ink:       #0f172a;
        --ink-2:     #1e293b;
        --muted:     #64748b;
        --muted-2:   #94a3b8;
        --border:    #e7eaf0;
        --border-2:  #f1f5f9;
        --surface:   #ffffff;
        --bg:        #f6f8fb;

        /* Status */
        --success:   #10b981;
        --info:      #06b6d4;
        --warning:   #f59e0b;
        --danger:    #ef4444;

        /* Effects */
        --radius-xs: 6px;
        --radius-sm: 10px;
        --radius:    14px;
        --radius-lg: 18px;
        --radius-xl: 22px;
        --shadow-xs: 0 1px 2px rgba(15,23,42,.04);
        --shadow-sm: 0 2px 6px -1px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.04);
        --shadow-md: 0 6px 22px -8px rgba(15,23,42,.12);
        --shadow-lg: 0 24px 48px -16px rgba(15,23,42,.18);

        /* Sidebar (dashboard) */
        --side-bg:        #0b1220;
        --side-bg-2:      #111c30;
        --side-text:      #cbd5e1;
        --side-text-soft: #94a3b8;
        --side-active:    rgba(16,185,129,.18);
        --side-active-bd: rgba(16,185,129,.45);
        --side-hover:     rgba(255,255,255,.06);
    }

    /* Base typography */
    html, body{
        font-family:'Plus Jakarta Sans', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
        font-feature-settings: "cv11","ss01";
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        color: var(--ink);
        letter-spacing:-.005em;
    }
    body{ background: var(--bg); }
    h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5,.h6{
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700;letter-spacing:-.015em;color:var(--ink);
    }
    .text-muted, .text-secondary{ color: var(--muted) !important; }
    code, .font-monospace{ font-family:'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace; }

    /* Card refinement */
    .card{
        border-radius: var(--radius) !important;
        border-color: var(--border) !important;
        box-shadow: var(--shadow-xs);
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .card:hover{ box-shadow: var(--shadow-sm); }
    .card-header{ border-bottom-color: var(--border-2) !important; padding:1rem 1.25rem; }
    .card-title{ font-weight:700; }
    .card-body{ padding:1.25rem; }
    .card.card-sm .card-body{ padding:.9rem 1rem; }

    /* Buttons */
    .btn{
        font-weight:600;letter-spacing:-.005em;
        border-radius: var(--radius-sm);
        transition:transform .12s ease, box-shadow .15s ease, background .2s, color .2s;
    }
    .btn:active{ transform:translateY(1px); }
    .btn-primary{ background: var(--brand-600); border-color: var(--brand-600); }
    .btn-primary:hover, .btn-primary:focus{ background: var(--brand-700); border-color: var(--brand-700); }
    .btn-success{ background: linear-gradient(135deg, var(--brand-500), var(--brand-700)); border:0; color:#fff; box-shadow:0 8px 18px -10px rgba(16,185,129,.6); }
    .btn-success:hover{ filter:brightness(1.05); color:#fff; }
    .btn-outline-primary{ color: var(--brand-700); border-color: var(--brand-200); }
    .btn-outline-primary:hover{ background: var(--brand-50); color: var(--brand-700); border-color: var(--brand-500); }
    .btn-outline-success{ color: var(--brand-700); border-color: var(--brand-200); background:#fff; }
    .btn-outline-success:hover{ background: var(--brand-50); color: var(--brand-700); border-color: var(--brand-500); }
    .btn-link{ color: var(--brand-700); }

    /* Forms */
    .form-control, .form-select{
        border-radius: var(--radius-sm);
        border-color: var(--border);
        transition: box-shadow .15s, border-color .15s;
    }
    .form-control:focus, .form-select:focus{
        border-color: var(--brand-500);
        box-shadow: 0 0 0 4px rgba(16,185,129,.12);
    }
    .form-label{ font-weight:600;color:var(--ink-2);font-size:.875rem; }
    .input-group-text{ border-radius: var(--radius-sm); background:#fff; border-color: var(--border); }

    /* Alerts */
    .alert{
        border-radius: var(--radius);
        border-width:1px;border-style:solid;
        padding:.85rem 1rem;
    }
    .alert-success{ background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
    .alert-danger{ background:#fef2f2; border-color:#fecaca; color:#991b1b; }
    .alert-warning{ background:#fffbeb; border-color:#fde68a; color:#92400e; }
    .alert-info{ background:#ecfeff; border-color:#a5f3fc; color:#155e75; }

    /* Badge polish */
    .badge{
        font-weight:600;letter-spacing:.01em;border-radius:999px;
        padding:.35em .65em;
    }

    /* Table polish */
    .table{ color: var(--ink); }
    .table thead th{
        font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
        color: var(--muted);background: #fafbfd;
        border-bottom-color: var(--border);
    }
    .table tbody td{ vertical-align: middle; }
    .table.card-table tbody tr:hover{ background:#fafbfd; }

    /* Avatars: gradient brand */
    .avatar.bg-brand{
        background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
        color:#fff;font-weight:700;
    }

    /* Helper utility */
    .text-brand{ color: var(--brand-700) !important; }
    .bg-brand-soft{ background: var(--brand-50) !important; }
    .border-brand{ border-color: var(--brand-200) !important; }
    .ring-brand{ box-shadow: 0 0 0 4px rgba(16,185,129,.15); }

    /* Scrollbar (webkit) */
    ::-webkit-scrollbar{ width:10px;height:10px; }
    ::-webkit-scrollbar-thumb{ background:#cbd5e1;border-radius:99px; }
    ::-webkit-scrollbar-thumb:hover{ background:#94a3b8; }

    /* Page transitions (subtle) */
    .page-body, main{ animation: theme-fade .25s ease-out both; }
    @keyframes theme-fade{ from{ opacity:0; transform: translateY(4px);} to{ opacity:1; transform: none; } }
    @media (prefers-reduced-motion: reduce){
        .page-body, main{ animation: none; }
    }
</style>
