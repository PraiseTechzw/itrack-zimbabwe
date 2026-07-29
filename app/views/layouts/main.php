<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'iTrack Zimbabwe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f4f7fb; }
        .sidebar { min-height: 100vh; background: #0f172a; }
        .sidebar .nav-link { color: #cbd5e1; border-radius: .5rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #1e293b; color: #fff; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php if (isLoggedIn()): ?>
            <nav class="col-md-3 col-lg-2 d-md-block sidebar py-4">
                <h4 class="text-white px-3 mb-4">iTrack Zimbabwe</h4>
                <ul class="nav flex-column px-2">
                    <li class="nav-item"><a class="nav-link active" href="/itrack-zimbabwe/public/dashboard.php"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=inventory"><i class="fa-solid fa-boxes-stacked me-2"></i>Inventory</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=accounting"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Accounting</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=purchases"><i class="fa-solid fa-cart-shopping me-2"></i>Purchases</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=sales"><i class="fa-solid fa-chart-line me-2"></i>Sales</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=requisition"><i class="fa-solid fa-clipboard-list me-2"></i>Requisitions</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=gps"><i class="fa-solid fa-location-dot me-2"></i>GPS Devices</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=users"><i class="fa-solid fa-users me-2"></i>User Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="/itrack-zimbabwe/public/index.php?controller=reports"><i class="fa-solid fa-file-lines me-2"></i>Reports</a></li>
                </ul>
            </nav>
        <?php endif; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4 px-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h5"><?= htmlspecialchars($title ?? 'Dashboard') ?></span>
                    <div class="ms-auto">
                        <?php if (isLoggedIn()): ?>
                            <span class="me-3 text-muted">Welcome, <?= htmlspecialchars(currentUser()['name'] ?? '') ?></span>
                            <a class="btn btn-outline-secondary btn-sm" href="/itrack-zimbabwe/public/logout.php">Logout</a>
                        <?php else: ?>
                            <a class="btn btn-primary btn-sm" href="/itrack-zimbabwe/public/login.php">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
            <?= $contentBlock ?? '' ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
