<?php
/**
 * List, add, edit, and delete collection zones.
 * Access: administrator only.
 * Touches: zones (read, insert, update, delete).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $zone_id = filter_var($_POST['zone_id'] ?? '', FILTER_VALIDATE_INT);
        if ($zone_id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM zones WHERE zone_id = ?');
                $stmt->execute([$zone_id]);
                redirect('/admin/zones.php?flash=' . rawurlencode('Zone deleted.'));
            } catch (PDOException $e) {
                redirect('/admin/zones.php?error=' . rawurlencode('Cannot delete this zone: it is still used by residents, collectors, or schedules.'));
            }
        }
    } else {
        $zone_name = trim($_POST['zone_name'] ?? '');
        $location_description = trim($_POST['location_description'] ?? '');
        $zone_id = filter_var($_POST['zone_id'] ?? '', FILTER_VALIDATE_INT);

        if ($zone_name === '') {
            $errors[] = 'Zone name is required.';
        }

        if (empty($errors) && $action === 'update' && $zone_id) {
            $stmt = $pdo->prepare('UPDATE zones SET zone_name = ?, location_description = ? WHERE zone_id = ?');
            $stmt->execute([$zone_name, $location_description, $zone_id]);
            redirect('/admin/zones.php?flash=' . rawurlencode('Zone updated.'));
        } elseif (empty($errors) && $action === 'create') {
            $stmt = $pdo->prepare('INSERT INTO zones (zone_name, location_description) VALUES (?, ?)');
            $stmt->execute([$zone_name, $location_description]);
            redirect('/admin/zones.php?flash=' . rawurlencode('Zone added.'));
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $pdo->prepare('SELECT * FROM zones WHERE zone_id = ?');
        $stmt->execute([$edit_id]);
        $editing = $stmt->fetch() ?: null;
    }
}

$zones = $pdo->query('SELECT * FROM zones ORDER BY zone_name')->fetchAll();

$page_title = 'Manage Zones';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Zones</h1>

<div class="card mb-4">
  <div class="card-header bg-brand"><?php echo $editing ? 'Edit Zone' : 'Add Zone'; ?></div>
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
    <form method="post" action="<?php echo BASE_URL; ?>/admin/zones.php" class="row g-3">
      <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
<?php if ($editing): ?>
      <input type="hidden" name="zone_id" value="<?php echo (int) $editing['zone_id']; ?>">
<?php endif; ?>
      <div class="col-md-4">
        <label for="zone_name" class="form-label">Zone name</label>
        <input type="text" class="form-control" id="zone_name" name="zone_name"
               value="<?php echo htmlspecialchars($editing['zone_name'] ?? ''); ?>" required>
      </div>
      <div class="col-md-6">
        <label for="location_description" class="form-label">Location description</label>
        <input type="text" class="form-control" id="location_description" name="location_description"
               value="<?php echo htmlspecialchars($editing['location_description'] ?? ''); ?>">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-brand w-100"><?php echo $editing ? 'Update' : 'Add'; ?></button>
      </div>
<?php if ($editing): ?>
      <div class="col-12">
        <a href="<?php echo BASE_URL; ?>/admin/zones.php" class="small">Cancel edit</a>
      </div>
<?php endif; ?>
    </form>
  </div>
</div>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Zone Name</th>
      <th>Location Description</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($zones)): ?>
    <tr><td colspan="3" class="text-center text-muted">No zones yet.</td></tr>
<?php else: foreach ($zones as $z): ?>
    <tr>
      <td><?php echo htmlspecialchars($z['zone_name']); ?></td>
      <td><?php echo htmlspecialchars($z['location_description'] ?? ''); ?></td>
      <td>
        <a href="<?php echo BASE_URL; ?>/admin/zones.php?edit=<?php echo (int) $z['zone_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
        <form method="post" action="<?php echo BASE_URL; ?>/admin/zones.php" class="d-inline"
              onsubmit="return confirm('Delete this zone? This cannot be undone.');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="zone_id" value="<?php echo (int) $z['zone_id']; ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
        </form>
      </td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
