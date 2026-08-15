<?php
/**
 * Lists every complaint with the resident's name and lets the administrator
 * write a response and mark it resolved.
 * Access: administrator only.
 * Touches: complaints (read, update), residents (read).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resolve') {
    $complaint_id = filter_var($_POST['complaint_id'] ?? '', FILTER_VALIDATE_INT);
    $response = trim($_POST['admin_response'] ?? '');

    if ($complaint_id && $response !== '') {
        $stmt = $pdo->prepare(
            "UPDATE complaints SET status = 'resolved', admin_response = ? WHERE complaint_id = ?"
        );
        $stmt->execute([$response, $complaint_id]);
        redirect('/admin/complaints.php?flash=' . rawurlencode('Complaint marked resolved.'));
    }
    redirect('/admin/complaints.php?error=' . rawurlencode('Please write a response before marking a complaint resolved.'));
}

$complaints = $pdo->query(
    'SELECT c.*, r.full_name AS resident_name
     FROM complaints c
     JOIN residents r ON c.resident_id = r.resident_id
     ORDER BY (c.status = "open") DESC, c.date_submitted DESC'
)->fetchAll();

$page_title = 'Manage Complaints';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Complaints</h1>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Date</th>
      <th>Resident</th>
      <th>Description</th>
      <th>Status</th>
      <th>Response</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($complaints)): ?>
    <tr><td colspan="5" class="text-center text-muted">No complaints yet.</td></tr>
<?php else: foreach ($complaints as $c): ?>
    <tr>
      <td><?php echo htmlspecialchars($c['date_submitted']); ?></td>
      <td><?php echo htmlspecialchars($c['resident_name']); ?></td>
      <td><?php echo nl2br(htmlspecialchars($c['description'])); ?></td>
      <td><span class="badge badge-<?php echo htmlspecialchars($c['status']); ?>"><?php echo htmlspecialchars(ucfirst($c['status'])); ?></span></td>
      <td style="min-width: 260px;">
<?php if ($c['status'] === 'resolved'): ?>
        <?php echo nl2br(htmlspecialchars($c['admin_response'] ?? '')); ?>
<?php else: ?>
        <form method="post" action="<?php echo BASE_URL; ?>/admin/complaints.php">
          <input type="hidden" name="action" value="resolve">
          <input type="hidden" name="complaint_id" value="<?php echo (int) $c['complaint_id']; ?>">
          <textarea name="admin_response" class="form-control form-control-sm mb-2" rows="2" placeholder="Write a response" required></textarea>
          <button type="submit" class="btn btn-brand btn-sm">Mark Resolved</button>
        </form>
<?php endif; ?>
      </td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
