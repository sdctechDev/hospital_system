<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$doctorObj = new Doctor();
$specs  = $doctorObj->getSpecializations();
$error  = '';
$form   = [];
$editId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = ['success' => false, 'message' => 'Unknown action.'];

    if ($action === 'add') {
        $result = $doctorObj->create($_POST);
    } elseif ($action === 'update') {
        $editId = (int) ($_POST['doctor_id'] ?? 0);
        $result = $doctorObj->update($editId, $_POST);
    } elseif ($action === 'status') {
        $result = $doctorObj->setStatus((int) ($_POST['doctor_id'] ?? 0), $_POST['status'] ?? '');
    }

    if ($result['success']) {
        setFlash('success', $result['message']);
        header('Location: doctors.php');
        exit;
    }
    // Failed: stay on the page and keep what was typed
    $error = $result['message'];
    $form  = $_POST;
}

if ($editId && !$form) {
    $form = $doctorObj->getById($editId) ?? [];
    if (!$form) {
        $editId = 0;
    }
}

$doctors = (new Admin())->getAllDoctors();
$isEdit  = $editId > 0;
$val = fn(string $k) => e($form[$k] ?? '');

$pageTitle = 'Manage Doctors';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Doctors</h1>
<?php renderFlash(); ?>

<div class="card">
    <h2><?= $isEdit ? 'Edit doctor' : 'Add doctor' ?></h2>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="doctors.php">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'add' ?>">
        <?php if ($isEdit): ?><input type="hidden" name="doctor_id" value="<?= $editId ?>"><?php endif; ?>

        <div class="row">
            <div class="form-group">
                <label>Full name *</label>
                <input type="text" name="full_name" value="<?= $val('full_name') ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?= $val('email') ?>" required>
            </div>
        </div>

        <div class="row">
            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label>Password * (min 6)</label>
                <input type="password" name="password" minlength="6" required>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?= $val('phone') ?>">
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Specialization *</label>
                <select name="specialization_id" required>
                    <option value="">Select</option>
                    <?php foreach ($specs as $s): ?>
                        <option value="<?= (int) $s['specialization_id'] ?>"
                            <?= (int) ($form['specialization_id'] ?? 0) === (int) $s['specialization_id'] ? 'selected' : '' ?>>
                            <?= e($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Consultation fee (&#8358;) *</label>
                <input type="number" name="consultation_fee" min="0" step="0.01" value="<?= $val('consultation_fee') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" rows="3"><?= $val('bio') ?></textarea>
        </div>

        <button type="submit" class="btn"><?= $isEdit ? 'Save changes' : 'Add doctor' ?></button>
        <?php if ($isEdit): ?><a href="doctors.php" class="btn btn-outline">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All doctors (<?= count($doctors) ?>)</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Specialization</th><th>Fee</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($doctors as $d): ?>
            <tr>
                <td><?= e($d['full_name']) ?></td>
                <td><?= e($d['email']) ?></td>
                <td><?= e($d['specialization']) ?></td>
                <td>&#8358;<?= number_format($d['consultation_fee'], 2) ?></td>
                <td><span class="badge badge-<?= $d['status'] === 'active' ? 'confirmed' : 'cancelled' ?>"><?= e($d['status']) ?></span></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="doctors.php?edit=<?= (int) $d['doctor_id'] ?>">Edit</a>
                    <form method="post" action="doctors.php" style="display:inline">
                        <input type="hidden" name="action" value="status">
                        <input type="hidden" name="doctor_id" value="<?= (int) $d['doctor_id'] ?>">
                        <input type="hidden" name="status" value="<?= $d['status'] === 'active' ? 'inactive' : 'active' ?>">
                        <button class="btn btn-sm <?= $d['status'] === 'active' ? 'btn-danger' : '' ?>">
                            <?= $d['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$doctors): ?><tr><td colspan="6" class="muted">No doctors yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
