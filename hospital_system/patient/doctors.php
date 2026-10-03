<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$doctorObj = new Doctor();
$specs     = $doctorObj->getSpecializations();
$allDocs   = $doctorObj->getAll();

$specId = (int) ($_GET['specialization_id'] ?? 0);
$q      = trim($_GET['q'] ?? '');

// Count doctors per specialization (for the filter chips)
$counts = [];
foreach ($allDocs as $d) {
    $sid = (int) $d['specialization_id'];
    $counts[$sid] = ($counts[$sid] ?? 0) + 1;
}

// Apply filters
$doctors = array_filter($allDocs, function ($d) use ($specId, $q) {
    if ($specId && (int) $d['specialization_id'] !== $specId) {
        return false;
    }
    if ($q !== '' && stripos($d['full_name'], $q) === false) {
        return false;
    }
    return true;
});

function chipUrl(int $id, string $q): string
{
    $params = [];
    if ($id) $params['specialization_id'] = $id;
    if ($q !== '') $params['q'] = $q;
    return 'doctors.php' . ($params ? '?' . http_build_query($params) : '');
}

$pageTitle = 'Doctors';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Our Doctors</h1>

<div class="card">
    <form method="get" action="doctors.php" class="row" style="align-items:end">
        <?php if ($specId): ?><input type="hidden" name="specialization_id" value="<?= $specId ?>"><?php endif; ?>
        <div class="form-group">
            <label for="q">Search by name</label>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="e.g. Ada">
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Search</button>
            <?php if ($q !== '' || $specId): ?><a href="doctors.php" class="btn btn-outline">Clear</a><?php endif; ?>
        </div>
    </form>

    <div class="slots mt">
        <a class="slot <?= !$specId ? 'selected' : '' ?>" href="<?= e(chipUrl(0, $q)) ?>">All (<?= count($allDocs) ?>)</a>
        <?php foreach ($specs as $s): ?>
            <?php $sid = (int) $s['specialization_id']; ?>
            <a class="slot <?= $specId === $sid ? 'selected' : '' ?>" href="<?= e(chipUrl($sid, $q)) ?>">
                <?= e($s['name']) ?> (<?= (int) ($counts[$sid] ?? 0) ?>)
            </a>
        <?php endforeach; ?>
    </div>
</div>

<p class="muted"><?= count($doctors) ?> doctor(s) found</p>

<div class="grid mt">
    <?php foreach ($doctors as $d): ?>
        <div class="card">
            <h2>Dr. <?= e($d['full_name']) ?></h2>
            <p><span class="badge badge-completed"><?= e($d['specialization']) ?></span></p>
            <p class="muted mt"><?= e($d['bio'] ?: 'No bio provided.') ?></p>
            <p class="mt"><strong>&#8358;<?= number_format($d['consultation_fee'], 2) ?></strong> per consultation</p>
            <p class="mt"><a class="btn btn-sm" href="book.php?doctor_id=<?= (int) $d['doctor_id'] ?>">Book</a></p>
        </div>
    <?php endforeach; ?>
    <?php if (!$doctors): ?><p class="muted">No doctors match your search.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
