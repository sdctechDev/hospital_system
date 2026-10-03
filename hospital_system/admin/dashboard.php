<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$stats = (new Admin())->getStats();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Admin Dashboard</h1>

<div class="grid">
    <div class="stat"><div class="num"><?= (int) $stats['patients'] ?></div><div class="label">Patients</div></div>
    <div class="stat"><div class="num"><?= (int) $stats['doctors'] ?></div><div class="label">Doctors</div></div>
    <div class="stat"><div class="num"><?= (int) $stats['appointments'] ?></div><div class="label">Appointments</div></div>
    <div class="stat"><div class="num">&#8358;<?= number_format($stats['revenue'], 2) ?></div><div class="label">Revenue (test)</div></div>
    <div class="stat"><div class="num"><?= (int) $stats['failed_payments'] ?></div><div class="label">Failed payments</div></div>
</div>

<div class="card mt">
    <h2>Appointments by status</h2>
    <div class="grid">
        <?php foreach ($stats['by_status'] as $status => $total): ?>
            <div class="stat">
                <div class="num"><?= (int) $total ?></div>
                <div class="label"><span class="badge badge-<?= e($status) ?>"><?= e($status) ?></span></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
