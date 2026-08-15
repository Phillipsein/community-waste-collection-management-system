<?php
/**
 * Public landing page. Anyone can view this page, logged in or not.
 * Touches no database tables.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>

<div class="hero mb-4">
  <h1>2A Web Based Waste Collection Management System</h1>
  <p class="lead mb-4">
    Helping urban residential communities request pickups, track collection
    schedules, pay for service, and raise complaints, all in one place, while
    giving collectors and administrators an easy way to manage the process.
  </p>
<?php if (!current_user()): ?>
  <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-light btn-lg me-2">Register</a>
  <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-accent btn-lg">Login</a>
<?php endif; ?>
</div>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header bg-brand">For Residents</div>
      <div class="card-body">
        <p class="card-text">Request a pickup, follow its status, view your zone's
        collection schedule, pay for completed pickups, and submit complaints.</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header bg-brand">For Collectors</div>
      <div class="card-body">
        <p class="card-text">See the pickup requests assigned to you and mark
        them completed once the waste has been collected.</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header bg-brand">For Administrators</div>
      <div class="card-body">
        <p class="card-text">Manage zones, schedules, vehicles, and collectors,
        assign requests, resolve complaints, and review summary reports.</p>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
