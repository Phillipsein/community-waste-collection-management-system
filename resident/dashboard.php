<?php
/**
 * Resident dashboard: welcome message, counts of the resident's pending and
 * completed requests, and their zone's next scheduled collection.
 * Access: resident only.
 * Touches: pickup_requests, zones, schedules (all read only).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('resident');

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE resident_id = ? AND status = 'pending'");
$stmt->execute([$user['id']]);
$pending_count = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE resident_id = ? AND status = 'completed'");
$stmt->execute([$user['id']]);
$completed_count = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT zone_name FROM zones WHERE zone_id = ?');
$stmt->execute([$user['zone_id']]);
$zone_name = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT collection_day, collection_time, frequency FROM schedules WHERE zone_id = ?
     ORDER BY schedule_id LIMIT 1'
);
$stmt->execute([$user['zone_id']]);
$next_schedule = $stmt->fetch();

$page_title = 'Resident Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-1">Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
<p class="text-muted mb-4">Zone: <?php echo htmlspecialchars($zone_name ?: 'Unknown'); ?></p>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $pending_count; ?></div>
        <div>Pending Requests</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-number"><?php echo $completed_count; ?></div>
        <div>Completed Requests</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="fw-semibold mb-1">Next Scheduled Collection</div>
<?php if ($next_schedule): ?>
        <div><?php echo htmlspecialchars($next_schedule['collection_day']); ?> at
             <?php echo htmlspecialchars($next_schedule['collection_time']); ?></div>
        <div class="text-muted small"><?php echo htmlspecialchars($next_schedule['frequency']); ?></div>
<?php else: ?>
        <div class="text-muted">No schedule set for your zone yet.</div>
<?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="mt-4">
  <a href="<?php echo BASE_URL; ?>/resident/request_pickup.php" class="btn btn-brand">Request a Pickup</a>
  <a href="<?php echo BASE_URL; ?>/resident/my_requests.php" class="btn btn-outline-secondary">View My Requests</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
