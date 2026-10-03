<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');

$date = $_GET['date'] ?? '';
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) {
    $date = '';
}

$appointments = (new Appointment())->getByDoctor(currentUserId(), $date ?: null);

$pageTitle = 'My Appointments';
$extraScripts = ['status.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">My Appointments</h1>
<div id="js-message"></div>

<div class="card">
    <form method="get" class="row" style="align-items:end">
        <div class="form-group">
            <label>Filter by date</label>
            <input type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()">
        </div>
        <div class="form-group">
            <?php if ($date): ?><a href="appointments.php" class="btn btn-outline">Show all</a><?php endif; ?>
        </div>
    </form>

    <div class="table-wrap">
    <table>
        <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Phone</th><th>Reason</th><th>Paid</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></td>
                <td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td><?= e($a['patient_phone']) ?></td>
                <td><?= e($a['reason']) ?></td>
                <td><?= $a['is_paid'] ? 'Yes' : 'No' ?></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                <td>
                    <?php if ($a['status'] === 'confirmed'): ?>
                        <button class="btn btn-sm" data-status-action data-id="<?= (int) $a['appointment_id'] ?>" data-status="completed">Complete</button>
                        <button class="btn btn-sm btn-danger" data-status-action data-id="<?= (int) $a['appointment_id'] ?>" data-status="cancelled">Cancel</button>
                    <?php elseif ($a['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-danger" data-status-action data-id="<?= (int) $a['appointment_id'] ?>" data-status="cancelled">Cancel</button>
                    <?php else: ?>
                        <span class="muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$appointments): ?><tr><td colspan="8" class="muted">No appointments found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
