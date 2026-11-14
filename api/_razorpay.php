<?php
// Shared helpers for the Razorpay endpoints. Not callable directly (blocked in .htaccess).

declare(strict_types=1);

const RZP_MIN_AMOUNT = 100;          // paise (₹1) — Razorpay minimum
const RZP_MAX_AMOUNT = 10000000;     // paise (₹1,00,000) — sanity cap for fee payments
const RZP_CURRENCY   = 'INR';

function rzp_json(int $status, array $body): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body);
    exit;
}

/** Reads KEY=VALUE lines from a file into an array (ignores blanks and # comments). */
function rzp_read_env_file(string $path): array {
    if (!is_file($path) || !is_readable($path)) return [];
    $out = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $out[trim($k)] = trim(trim($v), "\"'");
    }
    return $out;
}

/**
 * Credentials come from, in order:
 *  1. real environment variables (RAZORPAY_KEY_ID / RAZORPAY_KEY_SECRET)
 *  2. razorpay.env one level ABOVE the website folder (production on Hostinger — never in Git)
 *  3. .env in the project root (local development only; git-ignored)
 */
function rzp_config(): array {
    $id = getenv('RAZORPAY_KEY_ID') ?: '';
    $secret = getenv('RAZORPAY_KEY_SECRET') ?: '';
    if ($id === '' || $secret === '') {
        foreach ([dirname(__DIR__, 2) . '/razorpay.env', dirname(__DIR__) . '/.env'] as $file) {
            $env = rzp_read_env_file($file);
            if (!empty($env['RAZORPAY_KEY_ID']) && !empty($env['RAZORPAY_KEY_SECRET'])) {
                $id = $env['RAZORPAY_KEY_ID'];
                $secret = $env['RAZORPAY_KEY_SECRET'];
                break;
            }
        }
    }
    if ($id === '' || $secret === '') {
        error_log('Razorpay credentials are not configured');
        rzp_json(500, ['error' => 'Payments are not configured yet. Please try again later.']);
    }
    return ['key_id' => $id, 'key_secret' => $secret];
}

function rzp_require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        rzp_json(405, ['error' => 'Method not allowed']);
    }
}

/** Decodes the JSON request body (falls back to form fields). */
function rzp_input(): array {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}
