<?php
// ===================================
// CẤU HÌNH DATABASE VÀ APPLICATION
// Domain: qlvb.phongkhcn.vn
// ===================================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ===================================
// DATABASE CONFIGURATION
// ===================================
// Cập nhật thông tin này trong cPanel
define('DB_HOST', 'localhost');
define('DB_NAME', 'qlvb_db');
define('DB_USER', 'root'); // Thay bằng username cPanel của bạn
define('DB_PASS', ''); // Thay bằng password cPanel của bạn
define('DB_CHARSET', 'utf8mb4');

// ===================================
// JWT CONFIGURATION
// ===================================
define('JWT_SECRET', 'your-secret-key-change-in-production-2025');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 60 * 60 * 24 * 7); // 7 days

// ===================================
// GOOGLE DRIVE CONFIGURATION (Optional)
// ===================================
define('GOOGLE_DRIVE_ENABLED', false); // Set to true after configuration
define('GOOGLE_CREDENTIALS_FILE', __DIR__ . '/google-credentials.json');
define('GOOGLE_DRIVE_FOLDER_ID', ''); // Your Google Drive folder ID

// ===================================
// APPLICATION PATHS
// ===================================
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

// Create directories if not exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!file_exists(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

// ===================================
// DATABASE CONNECTION
// ===================================
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// ===================================
// UTILITY FUNCTIONS
// ===================================

function getDb() {
    return Database::getInstance()->getConnection();
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function sendJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['detail' => $message], JSON_UNESCAPED_UNICODE);
    exit();
}

function getJSONInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

function logError($message) {
    $logFile = LOGS_PATH . '/error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// ===================================
// JWT FUNCTIONS
// ===================================

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function createJWT($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
    
    $payload['exp'] = time() + JWT_EXPIRATION;
    $payload = json_encode($payload);
    
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = base64UrlEncode($signature);
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function verifyJWT($jwt) {
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) {
        return false;
    }
    
    $header = base64UrlDecode($tokenParts[0]);
    $payload = base64UrlDecode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];
    
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = base64UrlEncode($signature);
    
    if ($base64UrlSignature !== $signatureProvided) {
        return false;
    }
    
    $payload = json_decode($payload, true);
    
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

function getCurrentUser() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        sendError('Not authenticated', 401);
    }
    
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    
    $payload = verifyJWT($token);
    
    if (!$payload) {
        sendError('Invalid or expired token', 401);
    }
    
    // Get user from database
    $db = getDb();
    $stmt = $db->prepare("SELECT id, username, name, email, role, created_at FROM users WHERE username = ?");
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendError('User not found', 401);
    }
    
    return $user;
}

function requireAdmin() {
    $user = getCurrentUser();
    if ($user['role'] !== 'admin') {
        sendError('Admin access required', 403);
    }
    return $user;
}

// ===================================
// PASSWORD FUNCTIONS
// ===================================

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ===================================
// DOCUMENT STATUS CALCULATOR
// ===================================

function calculateDocumentStatus($expiryDate) {
    $today = new DateTime();
    $expiry = new DateTime($expiryDate);
    $diff = $today->diff($expiry)->days;
    $isPast = $expiry < $today;
    
    if ($isPast) {
        return 'expired';
    } elseif ($diff <= 7) {
        return 'expiring';
    } else {
        return 'active';
    }
}

?>