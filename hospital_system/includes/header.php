<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Hospital System';
$role = currentRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Hospital System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <script>window.APP = { baseUrl: "<?= BASE_URL ?>" };</script>
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= e(dashboardUrl($role)) ?>"> Hospital System</a>
    <nav>
        <?php if ($role === 'patient'): ?>
            <a href="<?= BASE_URL ?>patient/dashboard.php">Dashboard</a>
            <a href="<?= BASE_URL ?>patient/doctors.php">Doctors</a>
            <a href="<?= BASE_URL ?>patient/book.php">Book</a>
            <a href="<?= BASE_URL ?>patient/appointments.php">My Appointments</a>
            <a href="<?= BASE_URL ?>patient/profile.php">Profile</a>
        <?php elseif ($role === 'doctor'): ?>
            <a href="<?= BASE_URL ?>doctor/dashboard.php">Dashboard</a>
            <a href="<?= BASE_URL ?>doctor/appointments.php">Appointments</a>
            <a href="<?= BASE_URL ?>doctor/availability.php">Availability</a>
        <?php elseif ($role === 'admin'): ?>
            <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a>
            <a href="<?= BASE_URL ?>admin/doctors.php">Doctors</a>
            <a href="<?= BASE_URL ?>admin/patients.php">Patients</a>
            <a href="<?= BASE_URL ?>admin/appointments.php">Appointments</a>
            <a href="<?= BASE_URL ?>admin/payments.php">Payments</a>
            <a href="<?= BASE_URL ?>admin/specializations.php">Specializations</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php">Login</a>
            <a href="<?= BASE_URL ?>auth/register.php">Register</a>
        <?php endif; ?>

        <?php if ($role): ?>
            <span class="user"><?= e($_SESSION['user_name'] ?? '') ?> (<?= e($role) ?>)</span>
            <a href="<?= BASE_URL ?>auth/logout.php">Logout</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
