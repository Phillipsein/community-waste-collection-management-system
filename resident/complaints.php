<?php
/**
 * Lets a resident submit a complaint and view the status/response of their
 * own past complaints.
 * Access: resident only.
 * Touches: complaints (insert, read).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('resident');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');

    if ($description === '') {
        $errors[] = 'Please describe your complaint before submitting.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO complaints (resident_id, description, status) VALUES (?, ?, 'open')"
        );
        $stmt->execute([$user['id'], $description]);
        redirect('/resident/complaints.php?flash=' . rawurlencode('Complaint submitted successfully.'));
    }
}

$stmt = $pdo->prepare(
    'SELECT * FROM complaints WHERE resident_id = ? ORDER BY date_submitted DESC'
);
$stmt->execute([$user['id']]);
$complaints = $stmt->fetchAll();

$page_title = 'Complaints';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Complaints</h1>

<div class="card mb-4">
  <div class="card-header bg-brand">Submit a New Complaint</div>
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
    <form method="post" action="<?php echo BASE_URL; ?>/resident/complaints.php">
      <div class="mb-3">
        <label for="description" class="form-label">Describe your complaint</label>
        <textarea class="form-control" id="description" name="description" rows="3" required><?php
            echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
        ?></textarea>
      </div>
      <button type="submit" class="btn btn-brand">Submit Complaint</button>
    </form>
  </div>
</div>

<h2 class="h4 mb-3">My Complaints</h2>
<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Date</th>
      <th>Description</th>
      <th>Status</th>
      <th>Admin Response</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($complaints)): ?>
    <tr><td colspan="4" class="text-center text-muted">You have not submitted any complaints yet.</td></tr>
<?php else: foreach ($complaints as $c): ?>
    <tr>
      <td><?php echo htmlspecialchars($c['date_submitted']); ?></td>
      <td><?php echo nl2br(htmlspecialchars($c['description'])); ?></td>
      <td><span class="badge badge-<?php echo htmlspecialchars($c['status']); ?>"><?php echo htmlspecialchars(ucfirst($c['status'])); ?></span></td>
      <td><?php echo $c['admin_response'] ? nl2br(htmlspecialchars($c['admin_response'])) : '<span class="text-muted">&mdash;</span>'; ?></td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
