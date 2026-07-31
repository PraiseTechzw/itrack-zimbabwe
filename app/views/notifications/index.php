<?php $title = $title ?? 'Notifications'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Notifications</h2>
        <p class="text-muted mb-0">System alerts, reminders, and pending tasks for administrators.</p>
    </div>
    <a class="btn btn-primary" href="/itrack-zimbabwe/public/index.php?controller=settings">Notification settings</a>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Unread notifications</div>
                <div class="value">0</div>
                <p class="text-muted mb-0">No new alerts at the moment.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Scheduled reminders</div>
                <div class="value">0</div>
                <p class="text-muted mb-0">Nothing scheduled. Enable reminders in Settings.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Active alerts</div>
                <div class="value">0</div>
                <p class="text-muted mb-0">No active system alerts.</p>
            </div>
        </div>
    </div>
</div>
<div class="card mt-4 border-0">
    <div class="card-body">
        <h5 class="card-title">Notification center</h5>
        <p class="text-muted">Once your notification engine is configured, this page will show event history, unread alerts, and scheduled reminders.</p>
    </div>
</div>
