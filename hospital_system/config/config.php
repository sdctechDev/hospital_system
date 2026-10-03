<?php
// Database settings (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_NAME', 'hospital_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Timezone (affects date checks and slot times)
date_default_timezone_set('Africa/Lagos');

// App settings
define('BASE_URL', 'http://localhost/hospital_system/');
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Start session once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-load classes from /classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
