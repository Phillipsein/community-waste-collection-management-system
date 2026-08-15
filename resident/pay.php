<?php
/**
 * Simulated payment form and processing for a single completed pickup
 * request. No real payment gateway is involved.
 * Access: resident only, and only for their own completed & unpaid request.
 * Touches: pickup_requests (read), payments (insert).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_role('resident');

$request_id = filter_var($_GET['request_id'] ?? $_POST['request_id'] ?? '', FILTER_VALIDATE_INT);
if (!$request_id) {
    redirect('/resident/my_requests.php?error=' . rawurlencode('Invalid request.'));
}

$stmt = $pdo->prepare(
    "SELECT pr.*, (SELECT COUNT(*) FROM payments p WHERE p.request_id = pr.request_id AND p.status = 'paid') AS paid_count
     FROM pickup_requests pr WHERE pr.request_id = ? AND pr.resident_id = ?"
);
$stmt->execute([$request_id, $user['id']]);
$request = $stmt->fetch();

if (!$request || $request['status'] !== 'completed' || (int) $request['paid_count'] > 0) {
    redirect('/resident/my_requests.php?error=' . rawurlencode('This request is not available for payment.'));
}

$amount = WASTE_FEES[$request['waste_type']] ?? WASTE_FEES['Other'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? '';
    $valid_methods = ['mtn_mobile_money', 'airtel_money', 'cash'];

    if (!in_array($method, $valid_methods, true)) {
        $errors[] = 'Please choose a valid payment method.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO payments (resident_id, request_id, amount, payment_method, status)
             VALUES (?, ?, ?, ?, 'paid')"
        );
        $stmt->execute([$user['id'], $request_id, $amount, $method]);
        $success = true;
    }
}

$method_labels = [
    'mtn_mobile_money' => 'MTN Mobile Money',
    'airtel_money'      => 'Airtel Money',
    'cash'              => 'Cash',
];

$page_title = 'Pay for Pickup';
require __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header bg-brand">Pay for Pickup Request #<?php echo (int) $request_id; ?></div>
      <div class="card-body">
<?php if ($success): ?>
        <div class="alert alert-success">
          Payment of UGX <?php echo number_format($amount); ?> recorded successfully. Thank you!
        </div>
        <a href="<?php echo BASE_URL; ?>/resident/my_requests.php" class="btn btn-brand">Back to My Requests</a>
<?php else: ?>
        <p><strong>Waste type:</strong> <?php echo htmlspecialchars($request['waste_type']); ?></p>
        <p><strong>Request date:</strong> <?php echo htmlspecialchars($request['request_date']); ?></p>
        <p><strong>Amount due:</strong> UGX <?php echo number_format($amount); ?></p>
        <p class="small text-muted">This is a simulated payment for demonstration purposes. No real money
           moves and no external payment gateway is contacted.</p>
<?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
<?php foreach ($errors as $err): ?>
            <li><?php echo htmlspecialchars($err); ?></li>
<?php endforeach; ?>
          </ul>
        </div>
<?php endif; ?>
        <form method="post" action="<?php echo BASE_URL; ?>/resident/pay.php" novalidate>
          <input type="hidden" name="request_id" value="<?php echo (int) $request_id; ?>">
          <div class="mb-3">
            <label class="form-label">Payment method</label>
<?php foreach ($method_labels as $value => $label): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="method_<?php echo $value; ?>"
                     value="<?php echo $value; ?>" <?php echo ($_POST['payment_method'] ?? '') === $value ? 'checked' : ''; ?> required>
              <label class="form-check-label" for="method_<?php echo $value; ?>"><?php echo htmlspecialchars($label); ?></label>
            </div>
<?php endforeach; ?>
          </div>
          <button type="submit" class="btn btn-accent">Confirm Payment</button>
        </form>
<?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
