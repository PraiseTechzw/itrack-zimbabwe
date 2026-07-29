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
