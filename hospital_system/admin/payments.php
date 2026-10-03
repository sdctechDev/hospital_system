<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$filter = $_GET['status'] ?? '';
if (!in_array($filter, ['success', 'failed', 'refunded'], true)) {
    $filter = '';
}

$paymentObj = new Payment();
$payments   = $paymentObj->getAll($filter ?: null);

$pageTitle = 'Payments';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Payments</h1>

<div class="grid">
    <div class="stat"><div class="num">&#8358;<?= number_format($paymentObj->totalRevenue(), 2) ?></div><div class="label">Total revenue (simulated)</div></div>
</div>

<div class="card mt">
    <form method="get" class="row" style="align-items:end">
        <div class="form-group">
            <label>Filter by status</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="success" <?= $filter === 'success' ? 'selected' : '' ?>>Success</option>
                <option value="failed"  <?= $filter === 'failed'  ? 'selected' : '' ?>>Failed</option>
                <option value="refunded" <?= $filter === 'refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>
        </div>
    </form>

    <div class="table-wrap">
    <table>
        <thead><tr><th>Reference</th><th>Patient</th><th>Doctor</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= e($p['transaction_ref']) ?></td>
                <td><?= e($p['patient_name']) ?></td>
                <td><?= e($p['doctor_name']) ?></td>
                <td><?= e(str_replace('_', ' ', $p['payment_method'])) ?></td>
                <td>&#8358;<?= number_format($p['amount'], 2) ?></td>
                <td><span class="badge badge-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                <td><?= e(date('d M Y, H:i', strtotime($p['paid_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$payments): ?><tr><td colspan="7" class="muted">No payments found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
