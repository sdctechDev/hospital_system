<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$admin  = new Admin();
$db     = Database::getInstance();
$error  = '';
$form   = ['name' => '', 'description' => ''];
$editId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name   = $_POST['name'] ?? '';
    $desc   = $_POST['description'] ?? '';
    $result = ['success' => false, 'message' => 'Unknown action.'];

    if ($action === 'add') {
        $result = $admin->addSpecialization($name, $desc);
    } elseif ($action === 'update') {
        $editId = (int) ($_POST['specialization_id'] ?? 0);
        $result = $admin->updateSpecialization($editId, $name, $desc);
    } elseif ($action === 'delete') {
        $result = $admin->deleteSpecialization((int) ($_POST['specialization_id'] ?? 0));
    }

    if ($result['success']) {
        setFlash('success', $result['message']);
    } else {
        setFlash('error', $result['message']);
    }
    header('Location: specializations.php');
    exit;
}

if ($editId) {
    $row = $db->fetchOne("SELECT * FROM specializations WHERE specialization_id = ?", [$editId]);
    if ($row) {
        $form = $row;
    } else {
        $editId = 0;
    }
}

$list = $db->fetchAll(
    "SELECT s.*, (SELECT COUNT(*) FROM doctors d WHERE d.specialization_id = s.specialization_id) AS doctor_count
     FROM specializations s ORDER BY s.name"
);
$isEdit = $editId > 0;

$pageTitle = 'Specializations';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Specializations</h1>
<?php renderFlash(); ?>

<div class="card">
    <h2><?= $isEdit ? 'Edit specialization' : 'Add specialization' ?></h2>
    <form method="post" action="specializations.php">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'add' ?>">
        <?php if ($isEdit): ?><input type="hidden" name="specialization_id" value="<?= $editId ?>"><?php endif; ?>
        <div class="row">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" value="<?= e($form['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" value="<?= e($form['description'] ?? '') ?>">
            </div>
        </div>
        <button type="submit" class="btn"><?= $isEdit ? 'Save changes' : 'Add' ?></button>
        <?php if ($isEdit): ?><a href="specializations.php" class="btn btn-outline">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All specializations</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Description</th><th>Doctors</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($list as $s): ?>
            <tr>
                <td><?= e($s['name']) ?></td>
                <td><?= e($s['description']) ?></td>
                <td><?= (int) $s['doctor_count'] ?></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="specializations.php?edit=<?= (int) $s['specialization_id'] ?>">Edit</a>
                    <form method="post" action="specializations.php" style="display:inline"
                          onsubmit="return confirm('Delete this specialization?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="specialization_id" value="<?= (int) $s['specialization_id'] ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
