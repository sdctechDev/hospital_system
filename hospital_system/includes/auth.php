<?php
require_once __DIR__ . '/../config/config.php';

// ---------- SESSION ----------

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function sessionExpired(): bool
{
    return isset($_SESSION['last_activity'])
        && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT;
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// Refresh activity time, or log out if the session timed out
function touchSession(): bool
{
    if (!isLoggedIn()) {
        return false;
    }
    if (sessionExpired()) {
        logoutUser();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function currentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function currentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

// ---------- PAGE PROTECTION ----------

// Use at the top of protected pages: requireRole('patient') or requireRole(['doctor','admin'])
function requireRole($roles): void
{
    $roles = (array) $roles;

    if (!touchSession()) {
        header('Location: ' . BASE_URL . 'auth/login.php?timeout=1');
        exit;
    }
    if (!in_array(currentRole(), $roles, true)) {
        header('Location: ' . BASE_URL . 'auth/login.php?denied=1');
        exit;
    }
}

// ---------- API PROTECTION ----------

// Use at the top of API endpoints. Returns JSON errors instead of redirecting.
function requireRoleApi($roles): void
{
    $roles = (array) $roles;

    if (!touchSession()) {
        jsonResponse(['success' => false, 'message' => 'Session expired. Please log in.'], 401);
    }
    if (!in_array(currentRole(), $roles, true)) {
        jsonResponse(['success' => false, 'message' => 'Access denied.'], 403);
    }
}

// ---------- HELPERS ----------

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Escape output in HTML pages (use it whenever printing user data)
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Where each role lands after login
function dashboardUrl(?string $role): string
{
    switch ($role) {
        case 'patient':
            return BASE_URL . 'patient/dashboard.php';
        case 'doctor':
            return BASE_URL . 'doctor/dashboard.php';
        case 'admin':
            return BASE_URL . 'admin/dashboard.php';
        default:
            return BASE_URL . 'auth/login.php';
    }
}

// Block wrong HTTP methods in API endpoints
function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

// Read input from form data (FormData) or a JSON body
function getInput(): array
{
    if (!empty($_POST)) {
        return $_POST;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// One-time messages shown after a redirect (type: success or error)
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function renderFlash(): void
{
    if (!isset($_SESSION['flash'])) {
        return;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
}
