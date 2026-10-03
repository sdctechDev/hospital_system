<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$info  = '';
$email = '';
$role  = 'patient';

if (isset($_GET['timeout'])) {
    $info = 'Please log in to continue.';
}
if (isset($_GET['registered'])) {
    $info = 'Registration successful. You can now log in.';
}
if (isset($_GET['denied'])) {
    $error = 'You do not have access to that page.';
}

// Already logged in? Go to the dashboard (unless they were just denied a page)
if (touchSession() && !isset($_GET['denied'])) {
    header('Location: ' . dashboardUrl(currentRole()));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = $_POST['role'] ?? 'patient';
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        switch ($role) {
            case 'patient':
                $result = (new Patient())->login($email, $password);
                break;
            case 'doctor':
                $result = (new Doctor())->login($email, $password);
                break;
            case 'admin':
                $result = (new Admin())->login($email, $password);
                break;
            default:
                $result = ['success' => false, 'message' => 'Invalid role selected.'];
        }

        if ($result['success']) {
            header('Location: ' . dashboardUrl($role));
            exit;
        }
        $error = $result['message'];
        $info  = '';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<div class="card auth-box">
    <h2>Login</h2>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($info): ?><div class="alert alert-info"><?= e($info) ?></div><?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="role">Login as</label>
            <select name="role" id="role">
                <option value="patient" <?= $role === 'patient' ? 'selected' : '' ?>>Patient</option>
                <option value="doctor"  <?= $role === 'doctor'  ? 'selected' : '' ?>>Doctor</option>
                <option value="admin"   <?= $role === 'admin'   ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= e($email) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="btn btn-block">Login</button>
    </form>

    <p class="muted center mt">New patient? <a href="register.php">Create an account</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
