<?php
/**
 * Read only view of the collection schedule for the resident's own zone.
 * Access: resident only.
 * Touches: schedules, zones (read only).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('resident');

$stmt = $pdo->prepare('SELECT zone_name FROM zones WHERE zone_id = ?');
$stmt->execute([$user['zone_id']]);
$zone_name = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT collection_day, collection_time, frequency FROM schedules
     WHERE zone_id = ? ORDER BY schedule_id'
);
$stmt->execute([$user['zone_id']]);
$schedules = $stmt->fetchAll();

$page_title = 'Collection Schedule';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-1">Collection Schedule</h1>
<p class="text-muted mb-4">Zone: <?php echo htmlspecialchars($zone_name ?: 'Unknown'); ?></p>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Day</th>
      <th>Time</th>
      <th>Frequency</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($schedules)): ?>
    <tr><td colspan="3" class="text-center text-muted">No schedule has been set for your zone yet.</td></tr>
<?php else: foreach ($schedules as $s): ?>
    <tr>
      <td><?php echo htmlspecialchars($s['collection_day']); ?></td>
      <td><?php echo htmlspecialchars($s['collection_time']); ?></td>
      <td><?php echo htmlspecialchars($s['frequency']); ?></td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
