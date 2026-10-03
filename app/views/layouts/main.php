<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1324">
    <title><?= htmlspecialchars($title ?? 'iTrack Zimbabwe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --ink:#0b1324; --ink-2:#111d33; --muted:#738096; --line:#e6ebf2; --canvas:#f4f7fb; --card:#fff; --brand:#4f46e5; --brand-2:#6d5dfc; --mint:#13b981; --amber:#f59e0b; --danger:#e25555; --shadow:0 16px 40px rgba(15,23,42,.06); }
        * { box-sizing:border-box; }
        body { background:var(--canvas); color:var(--ink); margin:0; font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; letter-spacing:-.01em; }
        a { color:inherit; }
        .app-shell { min-height:100vh; display:flex; }
        .sidebar { width:264px; flex:0 0 264px; min-height:100vh; background:linear-gradient(180deg,#0b1324 0%,#101b30 100%); color:#d9e2f0; padding:22px 14px; position:sticky; top:0; height:100vh; overflow-y:auto; z-index:20; }
        .brand { display:flex; align-items:center; gap:11px; padding:6px 12px 22px; margin-bottom:10px; border-bottom:1px solid rgba(255,255,255,.09); }
        .brand-mark { width:38px; height:38px; border-radius:12px; display:grid; place-items:center; color:white; background:linear-gradient(135deg,#766bff,#4f46e5); box-shadow:0 8px 18px rgba(79,70,229,.32); }
        .brand-title { font-size:15px; font-weight:800; line-height:1.05; letter-spacing:.04em; color:#f8fbff; }
        .brand-subtitle { font-size:10px; text-transform:uppercase; letter-spacing:.14em; color:#8291aa; margin-top:4px; }
        .profile-chip { margin:16px 6px 18px; padding:12px; border:1px solid rgba(255,255,255,.1); border-radius:15px; background:rgba(255,255,255,.045); }
        .avatar { width:33px; height:33px; border-radius:11px; background:#e0e7ff; color:#4338ca; display:grid; place-items:center; font-weight:800; font-size:13px; }
        .profile-name { font-size:13px; font-weight:700; color:#f8fbff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .profile-role { font-size:11px; color:#9aa8bc; margin-top:2px; }
        .status-dot { width:7px; height:7px; background:#42d392; border-radius:50%; display:inline-block; margin-right:5px; box-shadow:0 0 0 3px rgba(66,211,146,.12); }
        .nav-title { color:#71809a; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.16em; padding:16px 12px 8px; }
        .sidebar .nav { gap:3px; }
        .sidebar .nav-link { display:flex; align-items:center; gap:11px; color:#aebbd0; border-radius:11px; padding:10px 12px; font-size:13px; font-weight:600; transition:.2s ease; }
        .sidebar .nav-link i { width:18px; text-align:center; color:#7f8da5; font-size:14px; }
        .sidebar .nav-link:hover { color:white; background:rgba(255,255,255,.07); transform:translateX(2px); }
        .sidebar .nav-link.active { color:white; background:linear-gradient(90deg,rgba(99,102,241,.3),rgba(99,102,241,.12)); box-shadow:inset 3px 0 0 #8178ff; }
        .sidebar .nav-link.active i { color:#a9a3ff; }
        .sidebar-footer { margin:22px 6px 2px; padding-top:16px; border-top:1px solid rgba(255,255,255,.09); }
        .main-area { min-width:0; flex:1; }
        .topbar { height:76px; display:flex; align-items:center; gap:16px; padding:0 34px; background:rgba(255,255,255,.88); border-bottom:1px solid var(--line); backdrop-filter:blur(16px); position:sticky; top:0; z-index:10; }
        .menu-toggle { display:none; width:38px; height:38px; border:1px solid var(--line); border-radius:10px; background:#fff; color:var(--ink); }
        .crumb { font-size:13px; color:var(--muted); }
        .crumb strong { color:var(--ink); font-weight:750; }
        .top-actions { margin-left:auto; display:flex; align-items:center; gap:10px; }
        .icon-button { width:38px; height:38px; display:grid; place-items:center; color:#64748b; border:1px solid var(--line); background:#fff; border-radius:11px; position:relative; }
        .icon-button:hover { color:var(--brand); border-color:#c9c5ff; background:#fafaff; }
        .notification-badge { position:absolute; width:7px; height:7px; background:#f15b5b; border-radius:50%; right:8px; top:7px; border:2px solid #fff; box-sizing:content-box; }
        .user-pill { display:flex; align-items:center; gap:9px; padding-left:8px; border-left:1px solid var(--line); }
        .user-pill span { font-size:12px; font-weight:700; color:#39465d; }
        .content-wrapper { padding:32px 34px 48px; max-width:1600px; }
        .page-title { font-size:clamp(25px,3vw,34px); font-weight:800; letter-spacing:-.045em; margin:0; line-height:1.1; } main h2.h4 { font-size:clamp(25px,3vw,34px); font-weight:800; letter-spacing:-.045em; line-height:1.1; } main > .d-flex.justify-content-between { margin-bottom:24px !important; }
        .page-subtitle { color:var(--muted); font-size:14px; margin:8px 0 0; max-width:700px; }
        .card { border:1px solid var(--line); border-radius:18px; background:var(--card); box-shadow:var(--shadow); }
        .card .card-body { padding:22px; }
        .eyebrow { font-size:11px; text-transform:uppercase; letter-spacing:.14em; color:#8390a4; font-weight:800; }
        .btn { border-radius:10px; font-weight:700; font-size:13px; padding:.63rem .9rem; }
        .btn-primary { background:var(--brand); border-color:var(--brand); box-shadow:0 7px 15px rgba(79,70,229,.18); }
        .btn-primary:hover { background:#4338ca; border-color:#4338ca; }
        .btn-light { border:1px solid var(--line); background:#fff; }
        .stats-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
        .metric-card { position:relative; overflow:hidden; min-height:148px; }
        .metric-card:after { content:""; width:100px; height:100px; border-radius:50%; position:absolute; right:-34px; bottom:-42px; background:rgba(99,102,241,.08); }
        .metric-head { display:flex; justify-content:space-between; align-items:flex-start; }
        .metric-icon { width:36px; height:36px; display:grid; place-items:center; border-radius:12px; color:#4f46e5; background:#eef0ff; }
        .metric-label { color:var(--muted); font-size:12px; font-weight:700; }
        .metric-value { font-size:29px; font-weight:850; letter-spacing:-.05em; margin-top:14px; }
        .metric-meta { color:#8b97a9; font-size:11px; margin-top:4px; }
        .dashboard-grid { display:grid; grid-template-columns:minmax(0,1.5fr) minmax(300px,1fr); gap:18px; }
        .section-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; }
        .section-heading h2 { font-size:16px; font-weight:800; margin:0; letter-spacing:-.03em; }
        .table { margin:0; font-size:13px; }
        .table thead th { color:#8a96a8; border-bottom:1px solid var(--line); text-transform:uppercase; letter-spacing:.08em; font-size:10px; font-weight:800; padding:0 10px 12px; white-space:nowrap; }
        .table tbody td { padding:14px 10px; border-color:#eef1f5; color:#526076; vertical-align:middle; }
        .table tbody tr:last-child td { border-bottom:0; }
        .table tbody td:first-child { color:var(--ink); font-weight:750; }
        .status { display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:5px 9px; font-size:10px; font-weight:800; text-transform:capitalize; }
        .status-success { color:#087a54; background:#e4f8ef; } .status-warning { color:#a46806; background:#fff4d8; } .status-danger { color:#a13f48; background:#ffebec; }
        .quick-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
        .quick-action { display:flex; align-items:center; gap:10px; border:1px solid var(--line); padding:12px; border-radius:13px; text-decoration:none; color:#45536b; font-size:12px; font-weight:700; transition:.2s ease; }
        .quick-action i { color:var(--brand); width:18px; text-align:center; } .quick-action:hover { border-color:#c7c3ff; background:#fafaff; color:var(--brand); transform:translateY(-2px); }
        .banner { border:0; background:linear-gradient(120deg,#302b7d 0%,#5549c8 60%,#7065e8 100%); color:#fff; overflow:hidden; position:relative; }
        .banner:after { content:""; position:absolute; width:260px; height:260px; border:1px solid rgba(255,255,255,.15); border-radius:50%; right:-90px; top:-120px; box-shadow:0 0 0 22px rgba(255,255,255,.04),0 0 0 45px rgba(255,255,255,.03); }
        .banner p { color:#d5d4ff; font-size:13px; margin:6px 0 0; } .banner .btn { background:#fff; color:#4f46e5; border-color:#fff; position:relative; z-index:1; }
        .module-card { height:100%; text-decoration:none; transition:.2s ease; } .module-card:hover { transform:translateY(-3px); border-color:#c9c5ff; }
        .module-icon { width:39px; height:39px; display:grid; place-items:center; color:var(--brand); background:#eef0ff; border-radius:12px; }
        .module-card h3 { font-size:14px; font-weight:800; margin:16px 0 6px; } .module-card p { color:var(--muted); font-size:12px; line-height:1.5; margin:0; }
        .form-control,.form-select { border-color:#dfe5ee; border-radius:10px; font-size:13px; padding:.7rem .8rem; } .form-control:focus,.form-select:focus { border-color:#9f98ff; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
        .table-striped>tbody>tr:nth-of-type(odd)>* { --bs-table-accent-bg:#fafbfe; color:inherit; } .table-hover>tbody>tr:hover>* { --bs-table-accent-bg:#f7f7ff; }
        .table-responsive { border-radius:12px; } .card > .card-body > h2, .card > .card-body > h3, .card > .card-body > h4, .card > .card-body > h5 { font-weight:800; letter-spacing:-.03em; }
        .card > .card-body > .d-flex.justify-content-between { gap:14px; } .card .form-select-sm { min-width:118px; padding-top:.42rem; padding-bottom:.42rem; }
        .alert { border:0; border-radius:12px; font-size:13px; } .badge { border-radius:999px; font-weight:750; padding:.46em .72em; }
        .btn-outline-secondary { color:#56647a; border-color:#d9e0eb; } .btn-outline-secondary:hover { color:var(--brand); border-color:#c7c3ff; background:#fafaff; }
        @media (max-width:1199px) { .stats-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:991px) { .sidebar { position:fixed; left:-280px; transition:left .25s ease; } body.sidebar-open .sidebar { left:0; box-shadow:18px 0 48px rgba(15,23,42,.24); } .menu-toggle { display:grid; place-items:center; } .topbar { padding:0 18px; } .content-wrapper { padding:26px 18px 40px; } .dashboard-grid { grid-template-columns:1fr; } }
        @media (max-width:575px) { .stats-grid { grid-template-columns:1fr; } .user-pill span { display:none; } .topbar { height:66px; } .content-wrapper { padding:22px 14px 34px; } .card .card-body { padding:17px; } }
    </style>
</head>
<body>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/partials/sidebar.php'; ?>
    <div class="main-area">
        <?php require dirname(__DIR__) . '/layouts/partials/header.php'; ?>
        <main class="content-wrapper">
            <?= $contentBlock ?? '' ?>
        </main>
    </div>
</div>
<script>
    document.querySelector('.menu-toggle')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    document.querySelectorAll('.sidebar .nav-link').forEach(link => link.addEventListener('click', () => document.body.classList.remove('sidebar-open')));
</script>
</body>
</html>
