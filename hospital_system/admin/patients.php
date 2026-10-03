<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$patients = (new Admin())->getAllPatients();

$pageTitle = 'Patients';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Patients (<?= count($patients) ?>)</h1>
<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Date of birth</th><th>Registered</th></tr></thead>
        <tbody>
        <?php foreach ($patients as $p): ?>
            <tr>
                <td><?= e($p['full_name']) ?></td>
                <td><?= e($p['email']) ?></td>
                <td><?= e($p['phone']) ?></td>
                <td><?= e($p['gender']) ?></td>
                <td><?= e($p['date_of_birth']) ?></td>
                <td><?= e(date('d M Y', strtotime($p['created_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$patients): ?><tr><td colspan="6" class="muted">No patients yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
