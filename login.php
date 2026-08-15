<?php
/**
 * Single login form for all three roles (resident, collector, administrator).
 * Checks the matching table for the selected role and starts a session.
 * Touches: residents, collectors, administrators (read only).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

// Already logged in? Skip the form and go straight to the right dashboard.
if ($user = current_user()) {
    redirect(role_home_path($user['role']));
}

$error = '';
$old_email = '';
$old_role = '';

// Maps the role picked in the form to its table and primary key column.
$role_tables = [
    'resident'      => ['table' => 'residents',      'id_col' => 'resident_id'],
    'collector'     => ['table' => 'collectors',     'id_col' => 'collector_id'],
    'administrator' => ['table' => 'administrators', 'id_col' => 'admin_id'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $old_email = $email;
    $old_role = $role;

    if ($email === '' || $password === '' || !isset($role_tables[$role])) {
        $error = 'Please choose a role and enter both your email and password.';
    } else {
        $table = $role_tables[$role]['table'];
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password_hash'])) {
            login_user($row, $role);
            redirect(role_home_path($role));
        } else {
            // Deliberately vague: never reveal whether the email or the
            // password (or the role) was the part that was wrong.
            $error = 'Invalid email, password, or role selected. Please try again.';
        }
    }
}

$page_title = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card">
      <div class="card-header bg-brand">Login</div>
      <div class="card-body">
<?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
        <form method="post" action="<?php echo BASE_URL; ?>/login.php" novalidate>
          <div class="mb-3">
            <label for="role" class="form-label">I am a</label>
            <select class="form-select" id="role" name="role" required>
              <option value="">Select role</option>
              <option value="resident" <?php echo $old_role === 'resident' ? 'selected' : ''; ?>>Resident</option>
              <option value="collector" <?php echo $old_role === 'collector' ? 'selected' : ''; ?>>Waste Collector</option>
              <option value="administrator" <?php echo $old_role === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?php echo htmlspecialchars($old_email); ?>" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-brand w-100">Login</button>
        </form>
        <p class="mt-3 mb-0">
          Not a resident yet?
          <a href="<?php echo BASE_URL; ?>/register.php">Register here</a>.
        </p>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
