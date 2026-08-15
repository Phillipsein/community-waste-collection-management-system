<?php
/**
 * Lists requests assigned to this collector with a Mark Completed button,
 * plus a short list of the collector's own recently completed requests.
 * Access: collector only.
 * Touches: pickup_requests (read, update), residents (read, for name/address).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('collector');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'complete') {
    $request_id = filter_var($_POST['request_id'] ?? '', FILTER_VALIDATE_INT);
    if ($request_id) {
        $stmt = $pdo->prepare(
            "UPDATE pickup_requests SET status = 'completed'
             WHERE request_id = ? AND collector_id = ? AND status = 'assigned'"
        );
        $stmt->execute([$request_id, $user['id']]);
    }
    redirect('/collector/my_requests.php?flash=' . rawurlencode('Request marked completed.'));
}

$stmt = $pdo->prepare(
    "SELECT pr.*, r.full_name AS resident_name, r.address, r.phone_number
     FROM pickup_requests pr
     JOIN residents r ON pr.resident_id = r.resident_id
     WHERE pr.collector_id = ? AND pr.status = 'assigned'
     ORDER BY pr.request_date"
);
$stmt->execute([$user['id']]);
$assigned = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT pr.*, r.full_name AS resident_name
     FROM pickup_requests pr
     JOIN residents r ON pr.resident_id = r.resident_id
     WHERE pr.collector_id = ? AND pr.status = 'completed'
     ORDER BY pr.request_id DESC LIMIT 10"
);
$stmt->execute([$user['id']]);
$completed = $stmt->fetchAll();

$page_title = 'My Assigned Requests';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">My Assigned Requests</h1>

<div class="table-responsive mb-5">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Date</th>
      <th>Resident</th>
      <th>Address</th>
      <th>Phone</th>
      <th>Waste Type</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($assigned)): ?>
    <tr><td colspan="6" class="text-center text-muted">No pending pickups assigned to you right now.</td></tr>
<?php else: foreach ($assigned as $r): ?>
    <tr>
      <td><?php echo htmlspecialchars($r['request_date']); ?></td>
      <td><?php echo htmlspecialchars($r['resident_name']); ?></td>
      <td><?php echo htmlspecialchars($r['address'] ?? ''); ?></td>
      <td><?php echo htmlspecialchars($r['phone_number']); ?></td>
      <td><?php echo htmlspecialchars($r['waste_type']); ?></td>
      <td>
        <form method="post" action="<?php echo BASE_URL; ?>/collector/my_requests.php" class="d-inline">
          <input type="hidden" name="action" value="complete">
          <input type="hidden" name="request_id" value="<?php echo (int) $r['request_id']; ?>">
          <button type="submit" class="btn btn-brand btn-sm">Mark Completed</button>
        </form>
      </td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<h2 class="h4 mb-3">Recently Completed by You</h2>
<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Date</th>
      <th>Resident</th>
      <th>Waste Type</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($completed)): ?>
    <tr><td colspan="4" class="text-center text-muted">You have not completed any pickups yet.</td></tr>
<?php else: foreach ($completed as $r): ?>
    <tr>
      <td><?php echo htmlspecialchars($r['request_date']); ?></td>
      <td><?php echo htmlspecialchars($r['resident_name']); ?></td>
      <td><?php echo htmlspecialchars($r['waste_type']); ?></td>
      <td><span class="badge badge-completed">Completed</span></td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
