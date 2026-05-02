<?php
// Divyachethana Society - Backend Configuration
// Production-ready PHP API with MySQL

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'divyachethana');
define('ADMIN_DEFAULT_USER', 'admin');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Allowed file types
$ALLOWED_DOC_TYPES = [
    'application/pdf', 
    'image/jpeg', 
    'image/png', 
    'image/jpg',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];
$ALLOWED_IMG_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];

// CORS headers for API access
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Start session for auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection helper
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }
    }
    return $pdo;
}

// JSON response helper
function jsonResponse($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Auth check helper
function requireAuth() {
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        jsonResponse(['success' => false, 'error' => 'Unauthorized']);
    }
}

// XSS escape helper
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// CSRF token helper
function generateToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyToken($token) {
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// Helper to restructure $_FILES array for multiple uploads
function reArrayFiles($files) {
    if (!isset($files['name']) || !is_array($files['name'])) {
        return [$files];
    }
    $fileArray = [];
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        $fileArray[] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
    }
    return $fileArray;
}
