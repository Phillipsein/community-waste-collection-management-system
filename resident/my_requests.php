<?php
/**
 * Lists the resident's own pickup requests with status, assigned collector,
 * and a Pay button once a request is completed and unpaid. Also lets the
 * resident cancel a request that is still pending (the enum in
 * pickup_requests already defines a 'cancelled' status for this).
 * Access: resident only.
 * Touches: pickup_requests (read, update for cancel), collectors (read),
 * payments (read, to know what is already paid).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('resident');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $request_id = filter_var($_POST['request_id'] ?? '', FILTER_VALIDATE_INT);
    if ($request_id) {
        $stmt = $pdo->prepare(
            "UPDATE pickup_requests SET status = 'cancelled'
             WHERE request_id = ? AND resident_id = ? AND status = 'pending'"
        );
        $stmt->execute([$request_id, $user['id']]);
    }
    redirect('/resident/my_requests.php?flash=' . rawurlencode('Request cancelled.'));
}

$stmt = $pdo->prepare(
    "SELECT pr.*, c.full_name AS collector_name,
            (SELECT COUNT(*) FROM payments p WHERE p.request_id = pr.request_id AND p.status = 'paid') AS paid_count
     FROM pickup_requests pr
     LEFT JOIN collectors c ON pr.collector_id = c.collector_id
     WHERE pr.resident_id = ?
     ORDER BY pr.created_at DESC"
);
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();

function status_badge(string $status): string
{
    $label = ucfirst($status);
    return '<span class="badge badge-' . htmlspecialchars($status) . '">' . htmlspecialchars($label) . '</span>';
}

$page_title = 'My Requests';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">My Requests</h1>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Date</th>
      <th>Waste Type</th>
      <th>Status</th>
      <th>Collector</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($requests)): ?>
    <tr><td colspan="5" class="text-center text-muted">You have not submitted any requests yet.</td></tr>
<?php else: foreach ($requests as $r): ?>
    <tr>
      <td><?php echo htmlspecialchars($r['request_date']); ?></td>
      <td><?php echo htmlspecialchars($r['waste_type']); ?></td>
      <td><?php echo status_badge($r['status']); ?></td>
      <td><?php echo $r['collector_name'] ? htmlspecialchars($r['collector_name']) : '<span class="text-muted">Not assigned yet</span>'; ?></td>
      <td>
<?php if ($r['status'] === 'completed' && (int) $r['paid_count'] === 0): ?>
        <a href="<?php echo BASE_URL; ?>/resident/pay.php?request_id=<?php echo (int) $r['request_id']; ?>" class="btn btn-accent btn-sm">Pay</a>
<?php elseif ($r['status'] === 'completed'): ?>
        <span class="badge badge-paid">Paid</span>
<?php elseif ($r['status'] === 'pending'): ?>
        <form method="post" action="<?php echo BASE_URL; ?>/resident/my_requests.php" class="d-inline"
              onsubmit="return confirm('Cancel this pickup request?');">
          <input type="hidden" name="action" value="cancel">
          <input type="hidden" name="request_id" value="<?php echo (int) $r['request_id']; ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
        </form>
<?php else: ?>
        <span class="text-muted">&mdash;</span>
<?php endif; ?>
      </td>
    </tr>
<?php endforeach; endif; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
