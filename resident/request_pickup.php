<?php
/**
 * Form for a resident to submit a new pickup request.
 * Access: resident only.
 * Touches: pickup_requests (insert).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('resident');

$errors = [];
$old = ['waste_type' => '', 'request_date' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['waste_type'] = $_POST['waste_type'] ?? '';
    $old['request_date'] = $_POST['request_date'] ?? '';

    if (!in_array($old['waste_type'], WASTE_TYPES, true)) {
        $errors[] = 'Please choose a valid waste type.';
    }

    $date_ok = (bool) DateTime::createFromFormat('Y-m-d', $old['request_date']);
    if (!$date_ok) {
        $errors[] = 'Please choose a valid preferred date.';
    } elseif ($old['request_date'] < date('Y-m-d')) {
        $errors[] = 'The preferred date cannot be in the past.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO pickup_requests (resident_id, request_date, waste_type, status)
             VALUES (?, ?, ?, 'pending')"
        );
        $stmt->execute([$user['id'], $old['request_date'], $old['waste_type']]);
        redirect('/resident/my_requests.php?flash=' . rawurlencode('Pickup request submitted successfully.'));
    }
}

$page_title = 'Request Pickup';
require __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header bg-brand">Request a Pickup</div>
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
        <form method="post" action="<?php echo BASE_URL; ?>/resident/request_pickup.php" novalidate>
          <div class="mb-3">
            <label for="waste_type" class="form-label">Waste type</label>
            <select class="form-select" id="waste_type" name="waste_type" required>
              <option value="">Select waste type</option>
<?php foreach (WASTE_TYPES as $type): ?>
              <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $old['waste_type'] === $type ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($type); ?>
              </option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="request_date" class="form-label">Preferred date</label>
            <input type="date" class="form-control" id="request_date" name="request_date"
                   min="<?php echo date('Y-m-d'); ?>"
                   value="<?php echo htmlspecialchars($old['request_date']); ?>" required>
          </div>
          <button type="submit" class="btn btn-brand">Submit Request</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
