<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'iTrack Zimbabwe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-strong: #f8fafc;
            --text-primary: #0f172a;
            --text-muted: #64748b;
            --brand: #4f46e5;
            --brand-soft: #eef2ff;
            --sidebar: #0b1220;
            --sidebar-active: #334155;
            --sidebar-text: #cbd5e1;
        }

        * { box-sizing: border-box; }
        body {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.12), transparent 30%),
                        linear-gradient(180deg, #f8fafc 0%, #eef2ff 60%, #f4f7fb 100%);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            max-width: 280px;
            background: var(--sidebar);
            color: #f8fafc;
            padding-top: 1.5rem;
        }
        .sidebar .nav-link {
            color: var(--sidebar-text);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            transform: translateX(2px);
        }
        .sidebar .nav-link.active {
            box-shadow: inset 3px 0 0 rgba(99, 102, 241, 0.8);
        }
        .sidebar .nav-title {
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 0.75rem;
            color: #94a3b8;
            margin: 1.5rem 0 0.75rem;
            padding-left: 1.5rem;
        }
        .sidebar .brand-title {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
            font-size: 1rem;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #eef2ff;
        }
        .sidebar .top-profile {
            padding: 0 1.5rem 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.12);
        }
        .sidebar .top-profile p { margin-bottom: 0.25rem; color: #cbd5e1; }
        .sidebar .top-profile span { display: block; font-size: 0.95rem; color: #e2e8f0; }
        .sidebar .top-profile .status {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #a5b4fc;
        }
        .content-wrapper {
            min-height: 100vh;
            padding: 2rem 2rem 3rem;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.06);
        }
        .card .card-body {
            padding: 1.75rem;
        }
        .page-title {
            font-size: clamp(1.9rem, 2.5vw, 2.3rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            color: var(--text-muted);
            margin-bottom: 1.75rem;
        }
        .stats-grid {
            gap: 1.5rem;
        }
        .stats-card {
            min-height: 150px;
        }
        .stats-card .title {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .stats-card .value {
            font-size: 2rem;
            font-weight: 700;
        }
        .quick-actions a {
            margin: 0.25rem;
        }
        .dashboard-banner {
            background: linear-gradient(135deg, rgba(99,102,241,0.16), rgba(59,130,246,0.05));
            border: 1px solid rgba(99, 102, 241, 0.12);
            border-radius: 1.25rem;
        }
        .dashboard-banner h5 {
            margin-bottom: 0.75rem;
        }
        .dashboard-banner p {
            color: var(--text-muted);
        }
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
        }
        .sidebar .nav-item { margin-bottom: 0.45rem; }
        .sidebar .badge {
            min-width: 2.2rem;
            min-height: 2.2rem;
            background: rgba(99, 102, 241, 0.2);
            color: #eef2ff;
        }
        @media (max-width: 991px) {
            .sidebar { max-width: 100%; }
            .content-wrapper { padding: 1.5rem 1rem 2rem; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php require dirname(__DIR__) . '/layouts/partials/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 content-wrapper">
            <?php require dirname(__DIR__) . '/layouts/partials/header.php'; ?>
            <?= $contentBlock ?? '' ?>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/partials/footer.php'; ?>
</body>
</html>
