<?php
/**
 * List all collectors with their zone and vehicle, create new collector
 * accounts (this is where collector passwords are set), and edit or delete
 * existing ones. Collectors never self register, per the report's design.
 * Access: administrator only.
 * Touches: collectors (read, insert, update, delete), zones (read), vehicles (read).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

$zones = $pdo->query('SELECT zone_id, zone_name FROM zones ORDER BY zone_name')->fetchAll();
$vehicles = $pdo->query('SELECT vehicle_id, registration_number FROM vehicles ORDER BY registration_number')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $collector_id = filter_var($_POST['collector_id'] ?? '', FILTER_VALIDATE_INT);
        if ($collector_id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM collectors WHERE collector_id = ?');
                $stmt->execute([$collector_id]);
                redirect('/admin/collectors.php?flash=' . rawurlencode('Collector deleted.'));
            } catch (PDOException $e) {
                redirect('/admin/collectors.php?error=' . rawurlencode('Cannot delete this collector: they still have pickup requests linked to them.'));
            }
        }
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $zone_id = filter_var($_POST['zone_id'] ?? '', FILTER_VALIDATE_INT);
        $vehicle_id = filter_var($_POST['vehicle_id'] ?? '', FILTER_VALIDATE_INT);
        $collector_id = filter_var($_POST['collector_id'] ?? '', FILTER_VALIDATE_INT);

        if ($full_name === '') {
            $errors[] = 'Full name is required.';
        }
        if ($phone_number === '') {
            $errors[] = 'Phone number is required.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        $valid_zone_ids = array_column($zones, 'zone_id');
        if (!$zone_id || !in_array($zone_id, $valid_zone_ids, true)) {
            $errors[] = 'Please choose a valid zone.';
        }
        if ($action === 'create' && strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT collector_id FROM collectors WHERE email = ? AND collector_id != ?');
            $stmt->execute([$email, $collector_id ?: 0]);
            if ($stmt->fetch()) {
                $errors[] = 'Another collector already uses that email address.';
            }
        }

        if (empty($errors) && $action === 'update' && $collector_id) {
            if ($password !== '') {
                if (strlen($password) < 6) {
                    $errors[] = 'Password must be at least 6 characters long.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare(
                        'UPDATE collectors SET full_name = ?, phone_number = ?, email = ?, password_hash = ?, zone_id = ?, vehicle_id = ? WHERE collector_id = ?'
                    );
                    $stmt->execute([$full_name, $phone_number, $email, $hash, $zone_id, $vehicle_id ?: null, $collector_id]);
                }
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE collectors SET full_name = ?, phone_number = ?, email = ?, zone_id = ?, vehicle_id = ? WHERE collector_id = ?'
                );
                $stmt->execute([$full_name, $phone_number, $email, $zone_id, $vehicle_id ?: null, $collector_id]);
            }
            if (empty($errors)) {
                redirect('/admin/collectors.php?flash=' . rawurlencode('Collector updated.'));
            }
        } elseif (empty($errors) && $action === 'create') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO collectors (full_name, phone_number, email, password_hash, zone_id, vehicle_id) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$full_name, $phone_number, $email, $hash, $zone_id, $vehicle_id ?: null]);
            redirect('/admin/collectors.php?flash=' . rawurlencode('Collector account created.'));
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $pdo->prepare('SELECT * FROM collectors WHERE collector_id = ?');
        $stmt->execute([$edit_id]);
        $editing = $stmt->fetch() ?: null;
    }
}

$collectors = $pdo->query(
    'SELECT c.*, z.zone_name, v.registration_number
     FROM collectors c
     JOIN zones z ON c.zone_id = z.zone_id
     LEFT JOIN vehicles v ON c.vehicle_id = v.vehicle_id
     ORDER BY c.full_name'
)->fetchAll();

$page_title = 'Manage Collectors';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Collectors</h1>

<div class="card mb-4">
  <div class="card-header bg-brand"><?php echo $editing ? 'Edit Collector' : 'Add Collector'; ?></div>
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
    <form method="post" action="<?php echo BASE_URL; ?>/admin/collectors.php" class="row g-3">
      <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
<?php if ($editing): ?>
      <input type="hidden" name="collector_id" value="<?php echo (int) $editing['collector_id']; ?>">
<?php endif; ?>
      <div class="col-md-4">
        <label for="full_name" class="form-label">Full name</label>
        <input type="text" class="form-control" id="full_name" name="full_name"
               value="<?php echo htmlspecialchars($editing['full_name'] ?? ''); ?>" required>
      </div>
      <div class="col-md-4">
        <label for="phone_number" class="form-label">Phone number</label>
        <input type="text" class="form-control" id="phone_number" name="phone_number"
               value="<?php echo htmlspecialchars($editing['phone_number'] ?? ''); ?>" required>
      </div>
      <div class="col-md-4">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?php echo htmlspecialchars($editing['email'] ?? ''); ?>" required>
      </div>
      <div class="col-md-4">
        <label for="password" class="form-label">
          Password<?php echo $editing ? ' (leave blank to keep current password)' : ''; ?>
        </label>
        <input type="password" class="form-control" id="password" name="password" minlength="6" <?php echo $editing ? '' : 'required'; ?>>
      </div>
      <div class="col-md-4">
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
      <div class="col-md-4">
        <label for="vehicle_id" class="form-label">Vehicle</label>
        <select class="form-select" id="vehicle_id" name="vehicle_id">
          <option value="">None</option>
<?php foreach ($vehicles as $v): ?>
          <option value="<?php echo (int) $v['vehicle_id']; ?>" <?php echo isset($editing['vehicle_id']) && (int) $editing['vehicle_id'] === (int) $v['vehicle_id'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($v['registration_number']); ?>
          </option>
<?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-brand w-100"><?php echo $editing ? 'Update' : 'Add'; ?></button>
      </div>
<?php if ($editing): ?>
      <div class="col-12">
        <a href="<?php echo BASE_URL; ?>/admin/collectors.php" class="small">Cancel edit</a>
      </div>
<?php endif; ?>
    </form>
  </div>
</div>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Name</th>
      <th>Phone</th>
      <th>Email</th>
      <th>Zone</th>
      <th>Vehicle</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($collectors)): ?>
    <tr><td colspan="6" class="text-center text-muted">No collectors yet.</td></tr>
<?php else: foreach ($collectors as $c): ?>
    <tr>
      <td><?php echo htmlspecialchars($c['full_name']); ?></td>
      <td><?php echo htmlspecialchars($c['phone_number']); ?></td>
      <td><?php echo htmlspecialchars($c['email']); ?></td>
      <td><?php echo htmlspecialchars($c['zone_name']); ?></td>
      <td><?php echo $c['registration_number'] ? htmlspecialchars($c['registration_number']) : '<span class="text-muted">None</span>'; ?></td>
      <td>
        <a href="<?php echo BASE_URL; ?>/admin/collectors.php?edit=<?php echo (int) $c['collector_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
        <form method="post" action="<?php echo BASE_URL; ?>/admin/collectors.php" class="d-inline"
              onsubmit="return confirm('Delete this collector account?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="collector_id" value="<?php echo (int) $c['collector_id']; ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
        </form>
      </td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
