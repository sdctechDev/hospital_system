<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
$filter  = $_GET['status'] ?? '';
if (!in_array($filter, $allowed, true)) {
    $filter = '';
}

$appointments = (new Appointment())->getAll($filter ?: null);

$pageTitle = 'Appointments';
$extraScripts = ['status.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Appointments</h1>
<div id="js-message"></div>

<div class="card">
    <form method="get" class="row" style="align-items:end">
        <div class="form-group">
            <label>Filter by status</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <?php foreach ($allowed as $s): ?>
                    <option value="<?= $s ?>" <?= $filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Fee</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?= (int) $a['appointment_id'] ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td><?= e($a['doctor_name']) ?></td>
                <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></td>
                <td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
                <td>&#8358;<?= number_format($a['fee'], 2) ?></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                <td>
                    <?php if ($a['status'] === 'pending'): ?>
                        <button class="btn btn-sm" data-status-action data-id="<?= (int) $a['appointment_id'] ?>" data-status="confirmed">Confirm</button>
                        <button class="btn btn-sm btn-danger" data-status-action data-id="<?= (int) $a['appointment_id'] ?>" data-status="cancelled">Cancel</button>
                    <?php elseif ($a['status'] === 'confirmed'): ?>
                        <button class="btn btn-sm" data-status-action data-id="<?= (int) $a['appointment_id'] ?>" data-status="completed">Complete</button>
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
