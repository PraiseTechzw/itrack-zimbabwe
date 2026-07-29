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
        <?php require dirname(__DIR__) . '/layouts/partials/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php require dirname(__DIR__) . '/layouts/partials/header.php'; ?>
            <?= $contentBlock ?? '' ?>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/partials/footer.php'; ?>
</body>
</html>
