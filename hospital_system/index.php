<?php
require_once __DIR__ . '/includes/auth.php';

if (touchSession()) {
    header('Location: ' . dashboardUrl(currentRole()));
} else {
    header('Location: ' . BASE_URL . 'auth/login.php');
}
exit;
