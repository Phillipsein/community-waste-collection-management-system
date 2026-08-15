<?php
/**
 * Resident self registration. Collectors and administrators are created by
 * an administrator instead (see admin/collectors.php), or exist from seed
 * data, so this page only ever creates rows in the residents table.
 * Touches: residents (insert), zones (read, for the dropdown).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    redirect(role_home_path(current_user()['role']));
}

$zones = $pdo->query('SELECT zone_id, zone_name FROM zones ORDER BY zone_name')->fetchAll();

$errors = [];
$old = ['full_name' => '', 'phone_number' => '', 'email' => '', 'address' => '', 'zone_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['phone_number'] = trim($_POST['phone_number'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['address'] = trim($_POST['address'] ?? '');
    $old['zone_id'] = $_POST['zone_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }
    if ($old['phone_number'] === '') {
        $errors[] = 'Phone number is required.';
    }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Password and confirm password do not match.';
    }
    $zone_id = filter_var($old['zone_id'], FILTER_VALIDATE_INT);
    $valid_zone_ids = array_column($zones, 'zone_id');
    if (!$zone_id || !in_array($zone_id, $valid_zone_ids, true)) {
        $errors[] = 'Please choose a valid zone.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT resident_id FROM residents WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists. Please login instead.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO residents (full_name, phone_number, email, password_hash, address, zone_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$old['full_name'], $old['phone_number'], $old['email'], $hash, $old['address'], $zone_id]);

        $new_resident = [
            'resident_id' => $pdo->lastInsertId(),
            'full_name'   => $old['full_name'],
            'email'       => $old['email'],
            'zone_id'     => $zone_id,
        ];
        login_user($new_resident, 'resident');
        redirect('/resident/dashboard.php');
    }
}

$page_title = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card">
      <div class="card-header bg-brand">Resident Registration</div>
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
        <form method="post" action="<?php echo BASE_URL; ?>/register.php" novalidate>
          <div class="mb-3">
            <label for="full_name" class="form-label">Full name</label>
            <input type="text" class="form-control" id="full_name" name="full_name"
                   value="<?php echo htmlspecialchars($old['full_name']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="phone_number" class="form-label">Phone number</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number"
                   value="<?php echo htmlspecialchars($old['phone_number']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?php echo htmlspecialchars($old['email']); ?>" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" minlength="6" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="confirm_password" class="form-label">Confirm password</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address"
                   value="<?php echo htmlspecialchars($old['address']); ?>">
          </div>
          <div class="mb-3">
            <label for="zone_id" class="form-label">Zone</label>
            <select class="form-select" id="zone_id" name="zone_id" required>
              <option value="">Select your zone</option>
<?php foreach ($zones as $zone): ?>
              <option value="<?php echo (int) $zone['zone_id']; ?>" <?php echo (string) $zone['zone_id'] === (string) $old['zone_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($zone['zone_name']); ?>
              </option>
<?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-brand w-100">Create Account</button>
        </form>
        <p class="mt-3 mb-0">
          Already have an account?
          <a href="<?php echo BASE_URL; ?>/login.php">Login here</a>.
        </p>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
