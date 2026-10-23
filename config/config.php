<?php
/**
 * Application Configuration & Helper Functions
 * Lost & Found Campus System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Application Constants
define('APP_NAME', 'Lost & Found Campus');
define('APP_SUBTITLE', 'ระบบแจ้งของหายและค้นหาสิ่งของภายในมหาวิทยาลัย');

// Base URL calculation (auto-detects /PJoop or root for built-in PHP server)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if (strpos($scriptDir, '/PJoop') === 0) {
    define('BASE_URL', '/PJoop');
} else {
    define('BASE_URL', '');
}
define('FULL_BASE_URL', $protocol . $host . BASE_URL);

// File Upload Settings
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/items/');
define('UPLOAD_URL', BASE_URL . '/uploads/items/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Require Database Configuration
require_once __DIR__ . '/database.php';

// Class Autoloader
spl_autoload_register(function ($class) {
    $file = dirname(__DIR__) . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * CSRF Protection Helpers
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output Sanitization / XSS Prevention
 */
function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Flash Message Helpers
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function hasFlash(): bool {
    return isset($_SESSION['flash']);
}

/**
 * Redirect Helper
 */
function redirect(string $path): void {
    if (strpos($path, 'http') === 0) {
        header("Location: " . $path);
        exit;
    }
    $base = rtrim(BASE_URL, '/');
    $cleanPath = '/' . ltrim($path, '/');
    if (!empty($base) && strpos($cleanPath, $base . '/') !== 0 && $cleanPath !== $base) {
        $cleanPath = $base . $cleanPath;
    }
    header("Location: " . $cleanPath);
    exit;
}

/**
 * Thai Date Formatter
 */
function formatDateThai(?string $dateStr, bool $includeTime = false): string {
    if (!$dateStr) return '-';
    
    $timestamp = strtotime($dateStr);
    if (!$timestamp) return '-';

    $thaiMonthsShort = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];

    $day = (int)date('d', $timestamp);
    $month = (int)date('m', $timestamp);
    $year = (int)date('Y', $timestamp) + 543; // Buddhist Era

    $formatted = sprintf('%d %s %d', $day, $thaiMonthsShort[$month] ?? '', $year);
    if ($includeTime) {
        $formatted .= ' ' . date('H:i', $timestamp) . ' น.';
    }
    return $formatted;
}

/**
 * HTML Badges for Item Status
 */
function statusBadge(string $status): string {
    switch ($status) {
        case 'active':
            return '<span class="badge badge-status-active"><i class="bi bi-broadcast me-1"></i>กำลังดำเนินการ</span>';
        case 'returned':
            return '<span class="badge badge-status-returned"><i class="bi bi-check-circle-fill me-1"></i>ส่งคืนสำเร็จ</span>';
        case 'pending':
            return '<span class="badge badge-status-pending"><i class="bi bi-clock-history me-1"></i>รอตรวจสอบ</span>';
        case 'rejected':
            return '<span class="badge badge-status-rejected"><i class="bi bi-x-circle-fill me-1"></i>ปิดรายการ</span>';
        default:
            return '<span class="badge bg-secondary">' . e($status) . '</span>';
    }
}

/**
 * HTML Badges for Item Type
 */
function typeBadge(string $type): string {
    if ($type === 'lost') {
        return '<span class="badge badge-type-lost"><i class="bi bi-search me-1"></i>ของหาย</span>';
    } elseif ($type === 'found') {
        return '<span class="badge badge-type-found"><i class="bi bi-gift me-1"></i>พบของ</span>';
    }
    return '<span class="badge bg-secondary">' . e($type) . '</span>';
}

/**
 * Image helper
 */
function itemImageUrl(?string $image): string {
    if (!empty($image) && file_exists(UPLOAD_DIR . $image)) {
        return UPLOAD_URL . $image;
    }
    return BASE_URL . '/assets/img/placeholder.svg';
}
