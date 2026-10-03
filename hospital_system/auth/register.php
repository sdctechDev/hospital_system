<?php
require_once __DIR__ . '/../includes/auth.php';

if (touchSession()) {
    header('Location: ' . dashboardUrl(currentRole()));
    exit;
}

$error = '';
$old = [
    'full_name' => '', 'email' => '', 'phone' => '',
    'gender' => '', 'date_of_birth' => '', 'address' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $_) {
        $old[$key] = trim($_POST[$key] ?? '');
    }
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $data = $old;
        $data['password'] = $password;
        $result = (new Patient())->register($data);

        if ($result['success']) {
            header('Location: login.php?registered=1');
            exit;
        }
        $error = $result['message'];
    }
}

$pageTitle = 'Register';
require __DIR__ . '/../includes/header.php';
?>
<div class="card auth-box" style="max-width:560px">
    <h2>Patient Registration</h2>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="full_name">Full name *</label>
            <input type="text" name="full_name" id="full_name" value="<?= e($old['full_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" value="<?= e($old['email']) ?>" required>
        </div>
        <div class="row">
            <div class="form-group">
                <label for="password">Password * (min 6)</label>
                <input type="password" name="password" id="password" minlength="6" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password *</label>
                <input type="password" name="confirm_password" id="confirm_password" minlength="6" required>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" value="<?= e($old['phone']) ?>">
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender">
                    <option value="">Select</option>
                    <option value="male"   <?= $old['gender'] === 'male'   ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= $old['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other"  <?= $old['gender'] === 'other'  ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="date_of_birth">Date of birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" max="<?= date('Y-m-d') ?>" value="<?= e($old['date_of_birth']) ?>">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" value="<?= e($old['address']) ?>">
        </div>
        <button type="submit" class="btn btn-block">Register</button>
    </form>

    <p class="muted center mt">Already registered? <a href="login.php">Login</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
