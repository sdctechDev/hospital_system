<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');

$doctorId = currentUserId();
$apptObj  = new Appointment();
$counts   = $apptObj->countByStatus($doctorId);
$today    = $apptObj->getByDoctor($doctorId, date('Y-m-d'));

$pageTitle = 'Doctor Dashboard';
$extraScripts = ['status.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Welcome, Dr. <?= e($_SESSION['user_name']) ?></h1>
<div id="js-message"></div>

<div class="grid">
    <?php foreach ($counts as $status => $total): ?>
        <div class="stat">
            <div class="num"><?= (int) $total ?></div>
            <div class="label"><span class="badge badge-<?= e($status) ?>"><?= e($status) ?></span></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mt">
    <h2>Today's appointments (<?= e(date('d M Y')) ?>)</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Time</th><th>Patient</th><th>Phone</th><th>Reason</th><th>Paid</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($today as $a): ?>
            <tr>
                <td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td><?= e($a['patient_phone']) ?></td>
                <td><?= e($a['reason']) ?></td>
                <td><?= $a['is_paid'] ? 'Yes' : 'No' ?></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                <td>
                    <?php if ($a['status'] === 'confirmed'): ?>
                        <button class="btn btn-sm" data-status-action data-id="<?= (int) $a['appointment_id'] ?>" data-status="completed">Complete</button>
                    <?php else: ?>
                        <span class="muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$today): ?><tr><td colspan="7" class="muted">No appointments today.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
