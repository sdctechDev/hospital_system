<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$apptObj = new Appointment();
$appt = $apptObj->getById((int) ($_GET['appointment_id'] ?? 0));

if (!$appt || (int) $appt['patient_id'] !== currentUserId()) {
    setFlash('error', 'Appointment not found.');
    header('Location: appointments.php');
    exit;
}
if ($appt['status'] !== 'pending' || $apptObj->isPaid((int) $appt['appointment_id'])) {
    setFlash('error', 'This appointment is not awaiting payment.');
    header('Location: appointments.php');
    exit;
}

$pageTitle = 'Payment';
$extraScripts = ['ui.js', 'pay.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Payment</h1>
<div id="js-message"></div>

<div class="alert alert-info">Simulated payment only. No real money is charged.</div>

<div class="card" style="max-width:560px">
    <h2>Appointment summary</h2>
    <p><strong>Doctor:</strong> Dr. <?= e($appt['doctor_name']) ?> (<?= e($appt['specialization']) ?>)</p>
    <p><strong>Date:</strong> <?= e(date('d M Y', strtotime($appt['appointment_date']))) ?></p>
    <p><strong>Time:</strong> <?= e(substr($appt['appointment_time'], 0, 5)) ?></p>
    <p><strong>Amount:</strong> &#8358;<?= number_format($appt['fee'], 2) ?></p>
</div>

<div class="card" style="max-width:560px">
    <h2>Pay</h2>
    <form id="pay-form" autocomplete="off">
        <input type="hidden" name="appointment_id" value="<?= (int) $appt['appointment_id'] ?>">

        <div class="form-group">
            <label for="payment_method">Payment method</label>
            <select name="payment_method" id="payment_method">
                <option value="card">Card</option>
                <option value="bank_transfer">Bank transfer</option>
            </select>
        </div>

        <div id="card-fields">
            <div class="form-group">
                <label>Name on card</label>
                <input type="text" name="card_name" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label>Card number</label>
                <input type="text" name="card_number" autocomplete="off" inputmode="numeric" placeholder="4242 4242 4242 4242" required>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Expiry (MM/YY)</label>
                    <input type="text" name="expiry" autocomplete="off" placeholder="12/30" maxlength="5" required>
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="password" name="cvv" autocomplete="new-password" inputmode="numeric" maxlength="3" required>
                </div>
            </div>
            <p class="muted">Test cards: 4242 4242 4242 4242 (success), 4000 0000 0000 0002 (declined), 4000 0000 0000 9995 (insufficient funds).</p>
        </div>

        <div id="bank-fields" style="display:none">
            <div class="form-group">
                <label>Account name</label>
                <input type="text" name="account_name" required>
            </div>
            <div class="form-group">
                <label>Account number (10 digits)</label>
                <input type="text" name="account_number" inputmode="numeric" maxlength="10" required>
            </div>
            <p class="muted">Test: any 10 digits succeed. 0000000000 fails.</p>
        </div>

        <button type="submit" class="btn btn-block mt">Pay &#8358;<?= number_format($appt['fee'], 2) ?></button>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
