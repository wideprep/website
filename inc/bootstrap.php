<?php
// Shared setup for every page and endpoint: settings, database, helpers.
// This folder is blocked from direct web access in .htaccess.

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
date_default_timezone_set('Asia/Kolkata');

define('SITE_ROOT', dirname(__DIR__));
define('SITE_URL', 'https://wideprep.in');

const BUSINESS = [
    'name'    => 'Wideprep.in',
    'brand'   => 'WidePrep',
    'email'   => 'contact@wideprep.in',
    'address' => '609, Aditya Enclave, Ameerpet, Hyderabad, Telangana 500038, India',
    'city'    => 'Hyderabad',
];

require_once __DIR__ . '/courses.php';

/* ---------------------------------------------------------------- settings */

/** Reads KEY=VALUE lines (blank lines and # comments ignored). */
function read_env_file(string $path): array {
    if (!is_file($path) || !is_readable($path)) return [];
    $out = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $out[trim($k)] = trim(trim($v), "\"'");
    }
    return $out;
}

/**
 * Secrets never live in Git. They are read from, in order:
 *   1. real environment variables
 *   2. wideprep.env one folder ABOVE the website folder (production, on Hostinger)
 *   3. .env in the project folder (local development only; git-ignored)
 */
function env(string $key, string $default = ''): string {
    static $file = null;
    if ($file === null) {
        $file = read_env_file(dirname(SITE_ROOT) . '/wideprep.env') ?: read_env_file(SITE_ROOT . '/.env');
    }
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    return ($file[$key] ?? '') !== '' ? $file[$key] : $default;
}

/* ---------------------------------------------------------------- database */

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = env('DB_DSN');   // local testing may use sqlite:/path/file.db
    if ($dsn === '') {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST', '127.0.0.1'), env('DB_PORT', '3306'), env('DB_NAME'));
    }
    $pdo = new PDO($dsn, env('DB_USER') ?: null, env('DB_PASS') ?: null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    ensure_schema($pdo);
    return $pdo;
}

/** Creates the tables on first use. Safe to run repeatedly. */
function ensure_schema(PDO $pdo): void {
    $sqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    $id = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
    $tail = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id $id,
        receipt VARCHAR(40) NOT NULL,
        rzp_order_id VARCHAR(40) NOT NULL UNIQUE,
        rzp_payment_id VARCHAR(40) NULL,
        course_slug VARCHAR(60) NOT NULL,
        amount INT NOT NULL,
        currency VARCHAR(3) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'created',
        access_token VARCHAR(64) NULL,
        email_sent_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        paid_at DATETIME NULL
    )$tail");

    $pdo->exec("CREATE TABLE IF NOT EXISTS enquiries (
        id $id,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        message TEXT NULL,
        created_at DATETIME NOT NULL
    )$tail");
}

function now(): string { return date('Y-m-d H:i:s'); }

/* ---------------------------------------------------------------- helpers */

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function rupees(int $paise): string {
    $r = $paise / 100;
    return '₹' . (fmod($r, 1.0) == 0.0 ? number_format($r) : number_format($r, 2));
}

function json_out(int $status, array $body): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        json_out(405, ['error' => 'Method not allowed']);
    }
}

/** JSON request body, falling back to form fields. */
function request_data(): array {
    $data = json_decode(file_get_contents('php://input') ?: '', true);
    return is_array($data) ? $data : $_POST;
}

function clean(mixed $v, int $max): string {
    return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)($v ?? '')))), 0, $max);
}

/** Validates name/email/phone. Returns [values, error|null]. */
function validate_contact(array $in): array {
    $name  = clean($in['name'] ?? '', 100);
    $email = strtolower(clean($in['email'] ?? '', 190));
    $phone = preg_replace('/[^\d+]/', '', (string)($in['phone'] ?? ''));
    $err = null;
    if (mb_strlen($name) < 2)                          $err = 'Please enter your full name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Please enter a valid email address.';
    elseif (!preg_match('/^\+?\d{10,15}$/', $phone))   $err = 'Please enter a valid mobile number.';
    return [['name' => $name, 'email' => $email, 'phone' => $phone], $err];
}
