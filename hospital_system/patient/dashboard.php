<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$all = (new Appointment())->getByPatient(currentUserId());

$counts = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
$upcoming = [];
foreach ($all as $a) {
    $counts[$a['status']]++;
    if (in_array($a['status'], ['pending', 'confirmed'], true) && $a['appointment_date'] >= date('Y-m-d')) {
        $upcoming[] = $a;
    }
}
$upcoming = array_slice(array_reverse($upcoming), 0, 5);

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Welcome, <?= e($_SESSION['user_name']) ?></h1>
<?php renderFlash(); ?>

<div class="grid">
    <?php foreach ($counts as $status => $total): ?>
        <div class="stat">
            <div class="num"><?= (int) $total ?></div>
            <div class="label"><span class="badge badge-<?= e($status) ?>"><?= e($status) ?></span></div>
        </div>
    <?php endforeach; ?>
</div>

<p class="mt">
    <a class="btn" href="book.php">Book an appointment</a>
    <a class="btn btn-outline" href="doctors.php">Browse doctors</a>
</p>

<div class="card mt">
    <h2>Upcoming appointments</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($upcoming as $a): ?>
            <tr>
                <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></td>
                <td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
                <td><?= e($a['doctor_name']) ?> <span class="muted">(<?= e($a['specialization']) ?>)</span></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                <td>
                    <?php if ($a['status'] === 'pending' && !$a['is_paid']): ?>
                        <a class="btn btn-sm" href="pay.php?appointment_id=<?= (int) $a['appointment_id'] ?>">Pay now</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$upcoming): ?><tr><td colspan="5" class="muted">No upcoming appointments.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
