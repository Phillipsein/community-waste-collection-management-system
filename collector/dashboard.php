<?php
/**
 * Collector dashboard: welcome message and a count of pending (assigned but
 * not yet done) versus completed requests assigned to this collector.
 * Access: collector only.
 * Touches: pickup_requests (read only).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('collector');

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE collector_id = ? AND status = 'assigned'");
$stmt->execute([$user['id']]);
$pending_count = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE collector_id = ? AND status = 'completed'");
$stmt->execute([$user['id']]);
$completed_count = (int) $stmt->fetchColumn();

$page_title = 'Collector Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $pending_count; ?></div>
        <div>Pending Pickups Assigned to You</div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $completed_count; ?></div>
        <div>Completed by You</div>
      </div>
    </div>
  </div>
</div>

<div class="mt-4">
  <a href="<?php echo BASE_URL; ?>/collector/my_requests.php" class="btn btn-brand">View My Assigned Requests</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
