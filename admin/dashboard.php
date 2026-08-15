<?php
/**
 * Administrator dashboard: counters for total residents, total collectors,
 * pending requests, open complaints, and total payments received.
 * Access: administrator only.
 * Touches: residents, collectors, pickup_requests, complaints, payments
 * (all read only).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

$total_residents = (int) $pdo->query('SELECT COUNT(*) FROM residents')->fetchColumn();
$total_collectors = (int) $pdo->query('SELECT COUNT(*) FROM collectors')->fetchColumn();
$pending_requests = (int) $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'pending'")->fetchColumn();
$open_complaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'open'")->fetchColumn();
$total_payments = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid'")->fetchColumn();

$page_title = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>

<div class="row g-3">
  <div class="col-md-4 col-lg-2">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $total_residents; ?></div>
        <div>Residents</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-lg-2">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $total_collectors; ?></div>
        <div>Collectors</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-lg-2">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $pending_requests; ?></div>
        <div>Pending Requests</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-lg-2">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $open_complaints; ?></div>
        <div>Open Complaints</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-lg-3">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number">UGX <?php echo number_format($total_payments); ?></div>
        <div>Total Payments Received</div>
      </div>
    </div>
  </div>
</div>

<div class="mt-4 d-flex flex-wrap gap-2">
  <a href="<?php echo BASE_URL; ?>/admin/requests.php" class="btn btn-brand">Manage Requests</a>
  <a href="<?php echo BASE_URL; ?>/admin/complaints.php" class="btn btn-outline-secondary">Manage Complaints</a>
  <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="btn btn-outline-secondary">View Reports</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
