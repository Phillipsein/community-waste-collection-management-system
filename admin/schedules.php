<?php
/**
 * List, add, edit, and delete collection schedules.
 * Access: administrator only.
 * Touches: schedules (read, insert, update, delete), zones (read, for the
 * zone dropdown and to show the zone name in the table).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

$zones = $pdo->query('SELECT zone_id, zone_name FROM zones ORDER BY zone_name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $schedule_id = filter_var($_POST['schedule_id'] ?? '', FILTER_VALIDATE_INT);
        if ($schedule_id) {
            $stmt = $pdo->prepare('DELETE FROM schedules WHERE schedule_id = ?');
            $stmt->execute([$schedule_id]);
            redirect('/admin/schedules.php?flash=' . rawurlencode('Schedule deleted.'));
        }
    } else {
        $zone_id = filter_var($_POST['zone_id'] ?? '', FILTER_VALIDATE_INT);
        $collection_day = $_POST['collection_day'] ?? '';
        $collection_time = trim($_POST['collection_time'] ?? '');
        $frequency = trim($_POST['frequency'] ?? '');
        $schedule_id = filter_var($_POST['schedule_id'] ?? '', FILTER_VALIDATE_INT);

        $valid_zone_ids = array_column($zones, 'zone_id');
        if (!$zone_id || !in_array($zone_id, $valid_zone_ids, true)) {
            $errors[] = 'Please choose a valid zone.';
        }
        if (!in_array($collection_day, $days, true)) {
            $errors[] = 'Please choose a valid collection day.';
        }
        if ($collection_time === '') {
            $errors[] = 'Collection time is required.';
        }
        if ($frequency === '') {
            $errors[] = 'Frequency is required.';
        }

        if (empty($errors) && $action === 'update' && $schedule_id) {
            $stmt = $pdo->prepare(
                'UPDATE schedules SET zone_id = ?, collection_day = ?, collection_time = ?, frequency = ? WHERE schedule_id = ?'
            );
            $stmt->execute([$zone_id, $collection_day, $collection_time, $frequency, $schedule_id]);
            redirect('/admin/schedules.php?flash=' . rawurlencode('Schedule updated.'));
        } elseif (empty($errors) && $action === 'create') {
            $stmt = $pdo->prepare(
                'INSERT INTO schedules (zone_id, collection_day, collection_time, frequency) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$zone_id, $collection_day, $collection_time, $frequency]);
            redirect('/admin/schedules.php?flash=' . rawurlencode('Schedule added.'));
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $pdo->prepare('SELECT * FROM schedules WHERE schedule_id = ?');
        $stmt->execute([$edit_id]);
        $editing = $stmt->fetch() ?: null;
    }
}

$schedules = $pdo->query(
    'SELECT s.*, z.zone_name FROM schedules s JOIN zones z ON s.zone_id = z.zone_id
     ORDER BY z.zone_name, FIELD(s.collection_day, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday")'
)->fetchAll();

$page_title = 'Manage Schedules';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Collection Schedules</h1>

<div class="card mb-4">
  <div class="card-header bg-brand"><?php echo $editing ? 'Edit Schedule' : 'Add Schedule'; ?></div>
  <div class="card-body">
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
<?php foreach ($errors as $err): ?>
        <li><?php echo htmlspecialchars($err); ?></li>
<?php endforeach; ?>
      </ul>
    </div>
<?php endif; ?>
    <form method="post" action="<?php echo BASE_URL; ?>/admin/schedules.php" class="row g-3">
      <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
<?php if ($editing): ?>
      <input type="hidden" name="schedule_id" value="<?php echo (int) $editing['schedule_id']; ?>">
<?php endif; ?>
      <div class="col-md-3">
        <label for="zone_id" class="form-label">Zone</label>
        <select class="form-select" id="zone_id" name="zone_id" required>
          <option value="">Select zone</option>
<?php foreach ($zones as $z): ?>
          <option value="<?php echo (int) $z['zone_id']; ?>" <?php echo isset($editing['zone_id']) && (int) $editing['zone_id'] === (int) $z['zone_id'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($z['zone_name']); ?>
          </option>
<?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label for="collection_day" class="form-label">Day</label>
        <select class="form-select" id="collection_day" name="collection_day" required>
          <option value="">Select day</option>
<?php foreach ($days as $d): ?>
          <option value="<?php echo $d; ?>" <?php echo ($editing['collection_day'] ?? '') === $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
<?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label for="collection_time" class="form-label">Time</label>
        <input type="text" class="form-control" id="collection_time" name="collection_time" placeholder="e.g. 8:00 AM"
               value="<?php echo htmlspecialchars($editing['collection_time'] ?? ''); ?>" required>
      </div>
      <div class="col-md-2">
        <label for="frequency" class="form-label">Frequency</label>
        <input type="text" class="form-control" id="frequency" name="frequency" placeholder="e.g. Weekly"
               value="<?php echo htmlspecialchars($editing['frequency'] ?? ''); ?>" required>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-brand w-100"><?php echo $editing ? 'Update' : 'Add'; ?></button>
      </div>
<?php if ($editing): ?>
      <div class="col-12">
        <a href="<?php echo BASE_URL; ?>/admin/schedules.php" class="small">Cancel edit</a>
      </div>
<?php endif; ?>
    </form>
  </div>
</div>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Zone</th>
      <th>Day</th>
      <th>Time</th>
      <th>Frequency</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($schedules)): ?>
    <tr><td colspan="5" class="text-center text-muted">No schedules yet.</td></tr>
<?php else: foreach ($schedules as $s): ?>
    <tr>
      <td><?php echo htmlspecialchars($s['zone_name']); ?></td>
      <td><?php echo htmlspecialchars($s['collection_day']); ?></td>
      <td><?php echo htmlspecialchars($s['collection_time']); ?></td>
      <td><?php echo htmlspecialchars($s['frequency']); ?></td>
      <td>
        <a href="<?php echo BASE_URL; ?>/admin/schedules.php?edit=<?php echo (int) $s['schedule_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
        <form method="post" action="<?php echo BASE_URL; ?>/admin/schedules.php" class="d-inline"
              onsubmit="return confirm('Delete this schedule?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="schedule_id" value="<?php echo (int) $s['schedule_id']; ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
        </form>
      </td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
