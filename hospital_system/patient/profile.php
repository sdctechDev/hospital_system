<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$patientObj = new Patient();
$id = currentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $patientObj->updateProfile($id, $_POST);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: profile.php');
    exit;
}

$p = $patientObj->getById($id);

$pageTitle = 'Profile';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">My Profile</h1>
<?php renderFlash(); ?>

<div class="card" style="max-width:560px">
    <form method="post" action="profile.php">
        <div class="form-group">
            <label>Full name *</label>
            <input type="text" name="full_name" value="<?= e($p['full_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= e($p['email']) ?>" readonly>
        </div>
        <div class="row">
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?= e($p['phone']) ?>">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <input type="text" value="<?= e($p['gender']) ?>" readonly>
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" value="<?= e($p['address']) ?>">
        </div>
        <button type="submit" class="btn">Save changes</button>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
