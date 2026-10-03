<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$r = (new Payment())->getReceipt((int) ($_GET['payment_id'] ?? 0), currentUserId());
if (!$r) {
    setFlash('error', 'Receipt not found.');
    header('Location: appointments.php');
    exit;
}

$pageTitle = 'Receipt';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:560px; margin:0 auto">
    <h2>Payment Receipt</h2>
    <p><span class="badge badge-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></p>
    <div class="table-wrap">
    <table>
        <tr><th>Reference</th><td><?= e($r['transaction_ref']) ?></td></tr>
        <tr><th>Date paid</th><td><?= e(date('d M Y, H:i', strtotime($r['paid_at']))) ?></td></tr>
        <tr><th>Patient</th><td><?= e($r['patient_name']) ?></td></tr>
        <tr><th>Doctor</th><td>Dr. <?= e($r['doctor_name']) ?> (<?= e($r['specialization']) ?>)</td></tr>
        <tr><th>Appointment</th><td><?= e(date('d M Y', strtotime($r['appointment_date']))) ?> at <?= e(substr($r['appointment_time'], 0, 5)) ?></td></tr>
        <tr><th>Method</th><td><?= e(str_replace('_', ' ', $r['payment_method'])) ?></td></tr>
        <tr><th>Amount</th><td><strong>&#8358;<?= number_format($r['amount'], 2) ?></strong></td></tr>
        <?php if ($r['status'] === 'refunded'): ?>
        <tr><th>Refund reference</th><td><?= e($r['refund_ref']) ?></td></tr>
        <tr><th>Refunded on</th><td><?= e(date('d M Y, H:i', strtotime($r['refunded_at']))) ?></td></tr>
        <?php endif; ?>
    </table>
    </div>
    <p class="muted mt">Simulated payment. No real money was charged.</p>
    <p class="mt no-print">
        <button class="btn" onclick="window.print()">Print receipt</button>
        <a class="btn btn-outline" href="appointments.php">My appointments</a>
    </p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
