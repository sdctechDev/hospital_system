<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$patientId = currentUserId();
$appointments = (new Appointment())->getByPatient($patientId);

// Map appointment -> its successful payment (for the receipt link)
$receipts = [];
foreach ((new Payment())->getByPatient($patientId) as $p) {
    if (in_array($p['status'], ['success', 'refunded'], true)) {
        $receipts[$p['appointment_id']] = $p['payment_id'];
    }
}

$pageTitle = 'My Appointments';
$extraScripts = ['ui.js', 'appointments.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">My Appointments</h1>
<?php renderFlash(); ?>
<div id="js-message"></div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Fee</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></td>
                <td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
                <td>Dr. <?= e($a['doctor_name']) ?> <span class="muted">(<?= e($a['specialization']) ?>)</span></td>
                <td>&#8358;<?= number_format($a['fee'], 2) ?></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                <td>
                    <?php if ($a['status'] === 'pending' && !$a['is_paid']): ?>
                        <a class="btn btn-sm" href="pay.php?appointment_id=<?= (int) $a['appointment_id'] ?>">Pay</a>
                    <?php endif; ?>
                    <?php if (isset($receipts[$a['appointment_id']])): ?>
                        <a class="btn btn-sm btn-outline" href="receipt.php?payment_id=<?= (int) $receipts[$a['appointment_id']] ?>">Receipt</a>
                    <?php endif; ?>
                    <?php if (in_array($a['status'], ['pending', 'confirmed'], true)): ?>
                        <button class="btn btn-sm btn-danger" data-cancel-id="<?= (int) $a['appointment_id'] ?>">Cancel</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$appointments): ?><tr><td colspan="6" class="muted">No appointments yet. <a href="book.php">Book one</a>.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
