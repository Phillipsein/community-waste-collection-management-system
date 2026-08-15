<?php
/**
 * Lists every pickup request across all zones and lets the administrator
 * assign a collector (from the same zone as the request's resident) to a
 * pending request.
 * Access: administrator only.
 * Touches: pickup_requests (read, update), residents (read), zones (read),
 * collectors (read).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign') {
    $request_id = filter_var($_POST['request_id'] ?? '', FILTER_VALIDATE_INT);
    $collector_id = filter_var($_POST['collector_id'] ?? '', FILTER_VALIDATE_INT);

    if ($request_id && $collector_id) {
        // Re-check server side that the chosen collector is really in the
        // same zone as the request's resident, even though the dropdown
        // already only offers matching collectors.
        $stmt = $pdo->prepare(
            'SELECT pr.request_id FROM pickup_requests pr
             JOIN residents r ON pr.resident_id = r.resident_id
             JOIN collectors c ON c.collector_id = ? AND c.zone_id = r.zone_id
             WHERE pr.request_id = ? AND pr.status = "pending"'
        );
        $stmt->execute([$collector_id, $request_id]);

        if ($stmt->fetch()) {
            $stmt = $pdo->prepare(
                "UPDATE pickup_requests SET collector_id = ?, status = 'assigned' WHERE request_id = ? AND status = 'pending'"
            );
            $stmt->execute([$collector_id, $request_id]);
            redirect('/admin/requests.php?flash=' . rawurlencode('Collector assigned. A notification would be sent to the resident here.'));
        }
    }
    redirect('/admin/requests.php?error=' . rawurlencode('Could not assign that collector to that request.'));
}

$requests = $pdo->query(
    'SELECT pr.*, r.full_name AS resident_name, r.zone_id AS resident_zone_id, z.zone_name,
            col.full_name AS collector_name
     FROM pickup_requests pr
     JOIN residents r ON pr.resident_id = r.resident_id
     JOIN zones z ON r.zone_id = z.zone_id
     LEFT JOIN collectors col ON pr.collector_id = col.collector_id
     ORDER BY pr.created_at DESC'
)->fetchAll();

$all_collectors = $pdo->query('SELECT collector_id, full_name, zone_id FROM collectors ORDER BY full_name')->fetchAll();
$collectors_by_zone = [];
foreach ($all_collectors as $c) {
    $collectors_by_zone[$c['zone_id']][] = $c;
}

function status_badge(string $status): string
{
    return '<span class="badge badge-' . htmlspecialchars($status) . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}

$page_title = 'Manage Requests';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Pickup Requests</h1>

<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead>
    <tr>
      <th>Date</th>
      <th>Resident</th>
      <th>Zone</th>
      <th>Waste Type</th>
      <th>Status</th>
      <th>Collector</th>
      <th>Assign</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($requests)): ?>
    <tr><td colspan="7" class="text-center text-muted">No pickup requests yet.</td></tr>
<?php else: foreach ($requests as $r): ?>
    <tr>
      <td><?php echo htmlspecialchars($r['request_date']); ?></td>
      <td><?php echo htmlspecialchars($r['resident_name']); ?></td>
      <td><?php echo htmlspecialchars($r['zone_name']); ?></td>
      <td><?php echo htmlspecialchars($r['waste_type']); ?></td>
      <td><?php echo status_badge($r['status']); ?></td>
      <td><?php echo $r['collector_name'] ? htmlspecialchars($r['collector_name']) : '<span class="text-muted">&mdash;</span>'; ?></td>
      <td>
<?php if ($r['status'] === 'pending'):
        $zone_collectors = $collectors_by_zone[$r['resident_zone_id']] ?? [];
?>
<?php if (empty($zone_collectors)): ?>
        <span class="text-muted small">No collectors in this zone yet</span>
<?php else: ?>
        <form method="post" action="<?php echo BASE_URL; ?>/admin/requests.php" class="d-flex gap-2">
          <input type="hidden" name="action" value="assign">
          <input type="hidden" name="request_id" value="<?php echo (int) $r['request_id']; ?>">
          <select name="collector_id" class="form-select form-select-sm" required>
            <option value="">Choose collector</option>
<?php foreach ($zone_collectors as $zc): ?>
            <option value="<?php echo (int) $zc['collector_id']; ?>"><?php echo htmlspecialchars($zc['full_name']); ?></option>
<?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-brand btn-sm text-nowrap">Assign</button>
        </form>
<?php endif; ?>
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
