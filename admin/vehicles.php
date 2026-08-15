<?php
/**
 * List, add, edit, and delete collection vehicles.
 * Access: administrator only.
 * Touches: vehicles (read, insert, update, delete).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $vehicle_id = filter_var($_POST['vehicle_id'] ?? '', FILTER_VALIDATE_INT);
        if ($vehicle_id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM vehicles WHERE vehicle_id = ?');
                $stmt->execute([$vehicle_id]);
                redirect('/admin/vehicles.php?flash=' . rawurlencode('Vehicle deleted.'));
            } catch (PDOException $e) {
                redirect('/admin/vehicles.php?error=' . rawurlencode('Cannot delete this vehicle: it is still assigned to a collector.'));
            }
        }
    } else {
        $registration_number = trim($_POST['registration_number'] ?? '');
        $vehicle_type = trim($_POST['vehicle_type'] ?? '');
        $capacity_kg = filter_var($_POST['capacity_kg'] ?? '', FILTER_VALIDATE_INT);
        $vehicle_id = filter_var($_POST['vehicle_id'] ?? '', FILTER_VALIDATE_INT);

        if ($registration_number === '') {
            $errors[] = 'Registration number is required.';
        }

        if (empty($errors) && $action === 'update' && $vehicle_id) {
            $stmt = $pdo->prepare(
                'UPDATE vehicles SET registration_number = ?, vehicle_type = ?, capacity_kg = ? WHERE vehicle_id = ?'
            );
            $stmt->execute([$registration_number, $vehicle_type, $capacity_kg ?: null, $vehicle_id]);
            redirect('/admin/vehicles.php?flash=' . rawurlencode('Vehicle updated.'));
        } elseif (empty($errors) && $action === 'create') {
            $stmt = $pdo->prepare(
                'INSERT INTO vehicles (registration_number, vehicle_type, capacity_kg) VALUES (?, ?, ?)'
            );
            $stmt->execute([$registration_number, $vehicle_type, $capacity_kg ?: null]);
            redirect('/admin/vehicles.php?flash=' . rawurlencode('Vehicle added.'));
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $pdo->prepare('SELECT * FROM vehicles WHERE vehicle_id = ?');
        $stmt->execute([$edit_id]);
        $editing = $stmt->fetch() ?: null;
    }
}

$vehicles = $pdo->query('SELECT * FROM vehicles ORDER BY registration_number')->fetchAll();

$page_title = 'Manage Vehicles';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Vehicles</h1>

<div class="card mb-4">
  <div class="card-header bg-brand"><?php echo $editing ? 'Edit Vehicle' : 'Add Vehicle'; ?></div>
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
    <form method="post" action="<?php echo BASE_URL; ?>/admin/vehicles.php" class="row g-3">
      <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
<?php if ($editing): ?>
      <input type="hidden" name="vehicle_id" value="<?php echo (int) $editing['vehicle_id']; ?>">
<?php endif; ?>
      <div class="col-md-4">
        <label for="registration_number" class="form-label">Registration number</label>
        <input type="text" class="form-control" id="registration_number" name="registration_number"
               value="<?php echo htmlspecialchars($editing['registration_number'] ?? ''); ?>" required>
      </div>
      <div class="col-md-4">
        <label for="vehicle_type" class="form-label">Vehicle type</label>
        <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" placeholder="e.g. Truck, Van"
               value="<?php echo htmlspecialchars($editing['vehicle_type'] ?? ''); ?>">
      </div>
      <div class="col-md-2">
        <label for="capacity_kg" class="form-label">Capacity (kg)</label>
        <input type="number" min="0" class="form-control" id="capacity_kg" name="capacity_kg"
               value="<?php echo htmlspecialchars((string) ($editing['capacity_kg'] ?? '')); ?>">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-brand w-100"><?php echo $editing ? 'Update' : 'Add'; ?></button>
      </div>
<?php if ($editing): ?>
      <div class="col-12">
        <a href="<?php echo BASE_URL; ?>/admin/vehicles.php" class="small">Cancel edit</a>
      </div>
<?php endif; ?>
    </form>
  </div>
</div>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Registration Number</th>
      <th>Type</th>
      <th>Capacity (kg)</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($vehicles)): ?>
    <tr><td colspan="4" class="text-center text-muted">No vehicles yet.</td></tr>
<?php else: foreach ($vehicles as $v): ?>
    <tr>
      <td><?php echo htmlspecialchars($v['registration_number']); ?></td>
      <td><?php echo htmlspecialchars($v['vehicle_type'] ?? ''); ?></td>
      <td><?php echo $v['capacity_kg'] !== null ? (int) $v['capacity_kg'] : ''; ?></td>
      <td>
        <a href="<?php echo BASE_URL; ?>/admin/vehicles.php?edit=<?php echo (int) $v['vehicle_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
        <form method="post" action="<?php echo BASE_URL; ?>/admin/vehicles.php" class="d-inline"
              onsubmit="return confirm('Delete this vehicle?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="vehicle_id" value="<?php echo (int) $v['vehicle_id']; ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
        </form>
      </td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
