<?php
/**
 * Simple summary report for a chosen date range (defaults to the last 30
 * days): number of pickup requests by status, total payments received, and
 * number of complaints by status.
 * Access: administrator only.
 * Touches: pickup_requests, payments, complaints (all read only).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('administrator');

$default_start = date('Y-m-d', strtotime('-30 days'));
$default_end = date('Y-m-d');

$start_date = $_GET['start_date'] ?? $default_start;
$end_date = $_GET['end_date'] ?? $default_end;

if (!DateTime::createFromFormat('Y-m-d', $start_date)) {
    $start_date = $default_start;
}
if (!DateTime::createFromFormat('Y-m-d', $end_date)) {
    $end_date = $default_end;
}
if ($start_date > $end_date) {
    [$start_date, $end_date] = [$end_date, $start_date];
}

// Requests by status, filtered by the request's preferred date.
$stmt = $pdo->prepare(
    'SELECT status, COUNT(*) AS total FROM pickup_requests
     WHERE request_date BETWEEN ? AND ? GROUP BY status'
);
$stmt->execute([$start_date, $end_date]);
$request_counts = ['pending' => 0, 'assigned' => 0, 'completed' => 0, 'cancelled' => 0];
foreach ($stmt->fetchAll() as $row) {
    $request_counts[$row['status']] = (int) $row['total'];
}

// Total payments received, filtered by payment date.
$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0) FROM payments
     WHERE status = 'paid' AND DATE(payment_date) BETWEEN ? AND ?"
);
$stmt->execute([$start_date, $end_date]);
$total_payments = (float) $stmt->fetchColumn();

// Complaints by status, filtered by the date they were submitted.
$stmt = $pdo->prepare(
    'SELECT status, COUNT(*) AS total FROM complaints
     WHERE DATE(date_submitted) BETWEEN ? AND ? GROUP BY status'
);
$stmt->execute([$start_date, $end_date]);
$complaint_counts = ['open' => 0, 'resolved' => 0];
foreach ($stmt->fetchAll() as $row) {
    $complaint_counts[$row['status']] = (int) $row['total'];
}

$page_title = 'Reports';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Reports</h1>

<div class="card mb-4">
  <div class="card-body">
    <form method="get" action="<?php echo BASE_URL; ?>/admin/reports.php" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label for="start_date" class="form-label">Start date</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
      </div>
      <div class="col-md-4">
        <label for="end_date" class="form-label">End date</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-brand">Apply Filter</button>
      </div>
    </form>
  </div>
</div>

<h2 class="h4 mb-3">Pickup Requests by Status</h2>
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card stat-card h-100"><div class="card-body"><div class="stat-number"><?php echo $request_counts['pending']; ?></div><div>Pending</div></div></div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card h-100"><div class="card-body"><div class="stat-number"><?php echo $request_counts['assigned']; ?></div><div>Assigned</div></div></div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card h-100"><div class="card-body"><div class="stat-number"><?php echo $request_counts['completed']; ?></div><div>Completed</div></div></div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card h-100"><div class="card-body"><div class="stat-number"><?php echo $request_counts['cancelled']; ?></div><div>Cancelled</div></div></div>
  </div>
</div>

<h2 class="h4 mb-3">Payments</h2>
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat-card h-100"><div class="card-body"><div class="stat-number">UGX <?php echo number_format($total_payments); ?></div><div>Total Payments Received</div></div></div>
  </div>
</div>

<h2 class="h4 mb-3">Complaints by Status</h2>
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card stat-card h-100"><div class="card-body"><div class="stat-number"><?php echo $complaint_counts['open']; ?></div><div>Open</div></div></div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card h-100"><div class="card-body"><div class="stat-number"><?php echo $complaint_counts['resolved']; ?></div><div>Resolved</div></div></div>
  </div>
</div>

<p class="text-muted small">Showing data from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>.</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
