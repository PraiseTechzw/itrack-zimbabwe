<!DOCTYPE html>
<html lang="en">
<head>
    <?php $isPublicAuthPage = in_array($view ?? '', ['auth/login', 'auth/register', 'auth/forgot-password'], true); ?>
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
        .topbar-brand { display:none; align-items:center; gap:8px; text-decoration:none; color:var(--ink); font-size:13px; font-weight:850; }
        .topbar-brand .brand-mark { width:32px; height:32px; border-radius:10px; }
        .crumb { font-size:13px; color:var(--muted); }
        .crumb strong { color:var(--ink); font-weight:750; }
        .top-actions { margin-left:auto; display:flex; align-items:center; gap:10px; }
        .icon-button { width:38px; height:38px; display:grid; place-items:center; color:#64748b; border:1px solid var(--line); background:#fff; border-radius:11px; position:relative; }
        .icon-button:hover { color:var(--brand); border-color:#c9c5ff; background:#fafaff; }
        .notification-badge { position:absolute; width:7px; height:7px; background:#f15b5b; border-radius:50%; right:8px; top:7px; border:2px solid #fff; box-sizing:content-box; }
        .notification-count { position:absolute; min-width:18px; height:18px; padding:0 5px; display:grid; place-items:center; border-radius:999px; background:#ef4444; color:#fff; font-size:10px; font-weight:800; right:-4px; top:-6px; border:2px solid #fff; }
        .notification-toast-stack { position:fixed; right:22px; bottom:22px; z-index:1080; display:grid; gap:10px; width:min(390px, calc(100vw - 28px)); }
        .notification-toast { display:grid; grid-template-columns:20px minmax(0, 1fr) 28px; align-items:start; gap:10px; padding:14px 12px; border:1px solid #d8e7df; border-left:4px solid #16845b; border-radius:8px; background:#fff; color:#20352c; box-shadow:0 10px 30px rgba(20,35,28,.16); font-size:13px; animation:notification-toast-in .18s ease-out; }
        .notification-toast > i { color:#16845b; margin-top:2px; }
        .notification-toast-error { border-color:#f0d4d4; border-left-color:#bd3f45; color:#49282a; }
        .notification-toast-error > i { color:#bd3f45; }
        .notification-toast-close { border:0; background:transparent; color:#6c7882; font-size:20px; line-height:1; padding:0; }
        .notification-toast-close:hover { color:#17202a; }
        @keyframes notification-toast-in { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        @media (prefers-reduced-motion: reduce) { .notification-toast { animation:none; } }
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
        .auth-body { min-height:100vh; background:#f7f8fc; }
        .auth-site { min-height:100vh; display:flex; flex-direction:column; }
        .auth-header { min-height:78px; display:flex; align-items:center; justify-content:space-between; gap:20px; padding:14px clamp(20px, 6vw, 88px); border-bottom:1px solid #e9edf4; background:rgba(255,255,255,.88); }
        .auth-brand { display:inline-flex; align-items:center; gap:11px; color:var(--ink); text-decoration:none; }
        .auth-brand .brand-mark { width:42px; height:42px; border-radius:13px; font-size:16px; }
        .auth-brand-name { font-size:15px; line-height:1.05; font-weight:850; letter-spacing:.015em; }
        .auth-brand-subtitle { margin-top:4px; color:#8a96a8; font-size:9px; font-weight:750; letter-spacing:.15em; text-transform:uppercase; }
        .auth-header-note { display:flex; align-items:center; gap:20px; color:#7b879a; font-size:12px; }
        .auth-header-note a { color:var(--brand); text-decoration:none; font-weight:750; }
        .auth-header-note a:hover { color:#3730a3; text-decoration:underline; }
        .auth-main { width:min(1120px, calc(100% - 40px)); flex:1; display:grid; grid-template-columns:minmax(0, 1fr) minmax(360px, 460px); align-items:center; gap:clamp(38px, 8vw, 110px); margin:0 auto; padding:58px 0; }
        .auth-story { position:relative; padding:24px 0; }
        .auth-story:before { content:""; position:absolute; width:360px; height:360px; border-radius:50%; left:-145px; top:-140px; background:radial-gradient(circle, rgba(99,102,241,.11), rgba(99,102,241,0) 70%); pointer-events:none; }
        .auth-story > * { position:relative; }
        .auth-kicker { display:flex; align-items:center; gap:9px; margin:0 0 17px; color:#6158cf; font-size:10px; font-weight:850; letter-spacing:.16em; text-transform:uppercase; }
        .auth-kicker:before { content:""; width:20px; height:2px; border-radius:2px; background:#746bf0; }
        .auth-story h1 { max-width:520px; margin:0; color:#111b30; font-size:clamp(36px, 4.4vw, 56px); line-height:1.04; font-weight:850; letter-spacing:-.055em; }
        .auth-story-copy { max-width:460px; margin:20px 0 0; color:#69768b; font-size:15px; line-height:1.75; }
        .auth-benefits { display:grid; gap:13px; margin:32px 0 0; padding:0; list-style:none; color:#3e4a60; font-size:13px; font-weight:650; }
        .auth-benefits li { display:flex; align-items:center; gap:11px; }
        .auth-benefits i { display:grid; place-items:center; width:23px; height:23px; border-radius:8px; color:#16845b; background:#e5f6ee; font-size:11px; }
        .auth-panel { padding:clamp(24px, 4vw, 38px); border:1px solid #e7ebf3; border-radius:22px; background:#fff; box-shadow:0 25px 70px rgba(22,32,57,.09); }
        .auth-panel-heading { margin-bottom:26px; }
        .auth-panel-heading h2 { margin:0; color:#111b30; font-size:26px; font-weight:850; letter-spacing:-.045em; }
        .auth-panel-heading p { margin:8px 0 0; color:#79859a; font-size:13px; line-height:1.55; }
        .auth-form { display:grid; gap:17px; }
        .auth-field label { display:block; margin-bottom:7px; color:#344158; font-size:12px; font-weight:750; }
        .auth-field .form-control { min-height:47px; padding:12px 13px; border-color:#e1e6ef; border-radius:10px; color:#182238; font-size:13px; }
        .auth-field .form-control::placeholder { color:#a0aabc; }
        .auth-field .form-control:focus { border-color:#938cf5; box-shadow:0 0 0 4px rgba(99,102,241,.11); }
        .auth-form .btn { min-height:47px; margin-top:2px; font-size:13px; }
        .auth-form .btn i { margin-left:7px; font-size:11px; }
        .auth-form-meta { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-top:-3px; color:#707d91; font-size:12px; }
        .auth-form-meta a, .auth-panel-footer a { color:var(--brand); font-weight:750; text-decoration:none; }
        .auth-form-meta a:hover, .auth-panel-footer a:hover { text-decoration:underline; }
        .auth-panel-footer { margin:20px 0 0; padding-top:18px; border-top:1px solid #edf0f5; color:#7c8799; font-size:12px; text-align:center; }
        .auth-security-note { display:flex; justify-content:center; align-items:center; gap:7px; margin-top:17px; color:#8490a2; font-size:11px; }
        .auth-security-note i { color:#16845b; }
        .auth-main .alert { margin-bottom:19px; padding:12px 14px; }
        .auth-footer { padding:0 20px 22px; color:#9aa4b4; font-size:11px; text-align:center; }
        @media (max-width:850px) { .auth-main { grid-template-columns:minmax(0, 460px); justify-content:center; gap:8px; padding:38px 0 48px; } .auth-story { padding:12px 0 20px; } .auth-story h1 { max-width:580px; font-size:clamp(34px, 7vw, 46px); } .auth-story-copy { margin-top:12px; font-size:14px; } .auth-benefits { grid-template-columns:repeat(3, 1fr); gap:8px; margin-top:18px; font-size:11px; } .auth-benefits li { align-items:flex-start; gap:7px; } }
        @media (max-width:575px) { .topbar-brand { display:flex; } .crumb { display:none; } .auth-header { min-height:68px; padding:11px 18px; } .auth-header-note { gap:0; font-size:11px; } .auth-header-note > span { display:none; } .auth-main { width:min(calc(100% - 28px), 460px); padding:27px 0 35px; } .auth-story { padding:8px 0 19px; } .auth-story h1 { font-size:34px; } .auth-story-copy { font-size:13px; } .auth-benefits { grid-template-columns:1fr; gap:8px; margin-top:15px; } .auth-panel { padding:23px 20px; border-radius:17px; } .auth-panel-heading { margin-bottom:21px; } .auth-panel-heading h2 { font-size:23px; } .auth-footer { padding-bottom:16px; } }
        @media (max-width:1199px) { .stats-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:991px) { .sidebar { position:fixed; left:-280px; transition:left .25s ease; } body.sidebar-open .sidebar { left:0; box-shadow:18px 0 48px rgba(15,23,42,.24); } .menu-toggle { display:grid; place-items:center; } .topbar { padding:0 18px; } .content-wrapper { padding:26px 18px 40px; } .dashboard-grid { grid-template-columns:1fr; } }
        @media (max-width:575px) { .stats-grid { grid-template-columns:1fr; } .user-pill span { display:none; } .topbar { height:66px; } .content-wrapper { padding:22px 14px 34px; } .card .card-body { padding:17px; } }
    </style>
</head>
<body<?= $isPublicAuthPage ? ' class="auth-body"' : '' ?>>
<?php if ($isPublicAuthPage): ?>
    <?php
    $authAction = [
        'auth/login' => ['label' => 'Create account', 'href' => '/index.php?controller=auth&action=register', 'prompt' => 'New to iTrack?'],
        'auth/register' => ['label' => 'Sign in', 'href' => '/login.php', 'prompt' => 'Already registered?'],
        'auth/forgot-password' => ['label' => 'Back to sign in', 'href' => '/login.php', 'prompt' => 'Remember your password?'],
    ][$view];
    ?>
    <div class="auth-site">
        <header class="auth-header">
            <a class="auth-brand" href="/login.php" aria-label="iTrack Zimbabwe home">
                <span class="brand-mark"><i class="fa-solid fa-route"></i></span>
                <span><span class="auth-brand-name d-block">iTrack Zimbabwe</span><span class="auth-brand-subtitle d-block">Operations platform</span></span>
            </a>
            <div class="auth-header-note"><span><?= htmlspecialchars($authAction['prompt'], ENT_QUOTES, 'UTF-8') ?></span><a href="<?= htmlspecialchars($authAction['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($authAction['label'], ENT_QUOTES, 'UTF-8') ?> <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </header>
        <main class="auth-main">
            <section class="auth-story" aria-label="About iTrack Zimbabwe">
                <p class="auth-kicker">One workspace. Every operation.</p>
                <h1>Keep your operations moving.</h1>
                <p class="auth-story-copy">A clearer view of your inventory, teams and day-to-day work — all in one secure place.</p>
                <ul class="auth-benefits">
                    <li><i class="fa-solid fa-check"></i><span>Stay on top of inventory</span></li>
                    <li><i class="fa-solid fa-check"></i><span>Keep teams in sync</span></li>
                    <li><i class="fa-solid fa-check"></i><span>Make informed decisions</span></li>
                </ul>
            </section>
            <div class="auth-panel-wrap"><?= $contentBlock ?? '' ?></div>
        </main>
        <footer class="auth-footer">© <?= date('Y') ?> iTrack Zimbabwe <span class="mx-2">·</span> Built for better operations</footer>
    </div>
<?php else: ?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/partials/sidebar.php'; ?>
    <div class="main-area">
        <?php require dirname(__DIR__) . '/layouts/partials/header.php'; ?>
        <main class="content-wrapper">
            <?= $contentBlock ?? '' ?>
        </main>
    </div>
</div>
<div class="notification-toast-stack" id="notification-toast-stack" aria-live="polite" aria-relevant="additions"></div>
<script>
    const notificationToastStack = document.getElementById('notification-toast-stack');
    const notificationBadge = document.querySelector('[data-notification-badge]');
    const notificationCount = document.querySelector('[data-notification-count]');

    const updateBellState = (count) => {
        const numericCount = Number(count || 0);
        if (notificationBadge) {
            notificationBadge.hidden = numericCount <= 0;
        }
        if (notificationCount) {
            notificationCount.hidden = numericCount <= 0;
            notificationCount.textContent = numericCount > 0 ? String(numericCount) : '';
        }
    };

    const showNotificationToast = (title, message) => {
        if (!notificationToastStack) return;
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.setAttribute('role', 'status');

        const icon = document.createElement('i');
        icon.className = 'fa-solid fa-bell';

        const textWrap = document.createElement('div');
        const heading = document.createElement('div');
        heading.style.fontWeight = '800';
        heading.style.marginBottom = '2px';
        heading.textContent = title;

        const bodyText = document.createElement('div');
        bodyText.style.color = '#57647a';
        bodyText.style.lineHeight = '1.45';
        bodyText.textContent = message;

        textWrap.appendChild(heading);
        textWrap.appendChild(bodyText);

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'notification-toast-close';
        closeButton.setAttribute('aria-label', 'Dismiss notification');
        closeButton.textContent = '×';
        closeButton.addEventListener('click', () => toast.remove());

        toast.appendChild(icon);
        toast.appendChild(textWrap);
        toast.appendChild(closeButton);
        notificationToastStack.appendChild(toast);
        window.setTimeout(() => toast.remove(), 7000);
    };

    const requestDesktopPushPermission = async () => {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'default') {
            await Notification.requestPermission();
        }
    };

    const showSystemPush = (title, message) => {
        if (!('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }
        new Notification(title, { body: message, tag: 'itrack-system-notification' });
    };

    const loadNotifications = async () => {
        try {
            const response = await fetch('/index.php?controller=notification&action=apiList', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) return;
            const data = await response.json();
            const items = Array.isArray(data.notifications) ? data.notifications : [];
            updateBellState(data.count ?? items.length);

            if (items.length > 0) {
                items.slice(0, 4).forEach((item) => {
                    showNotificationToast(item.title || 'System update', item.message || 'You have a new notification.');
                    showSystemPush(item.title || 'System update', item.message || 'You have a new notification.');
                });
            }
        } catch (error) {
            console.warn('Notification fetch failed:', error);
        }
    };

    document.querySelector('.menu-toggle')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    document.querySelectorAll('.sidebar .nav-link').forEach(link => link.addEventListener('click', () => document.body.classList.remove('sidebar-open')));

    updateBellState(document.querySelector('[data-notification-count]')?.textContent || 0);
    requestDesktopPushPermission();
    window.addEventListener('load', loadNotifications);
</script>
<?php endif; ?>
</body>
</html>
