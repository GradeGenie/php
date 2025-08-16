<!-- <?php
// function loadEnv($path = __DIR__.'/.env') {
//     if (!file_exists($path)) {
//         $path = __DIR__.'/.env.example';
//         if (!file_exists($path)) {
//             return;
//         }
//     }
//     $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//     foreach ($lines as $line) {
//         if (strpos(trim($line), '#') === 0) continue;
//         list($key, $value) = array_map('trim', explode('=', $line, 2));
//         if (!array_key_exists($key, $_ENV)) {
//             putenv("{$key}={$value}");
//             $_ENV[$key] = $value;
//         }
//     }
// }
// loadEnv();
// foreach ($_ENV as $k => $v) {
//     error_log("ENV[{$k}] = {$v}");
// }
?> -->

<?php
// load_env.php

// If Composer autoload exists, load it (enables vlucas/phpdotenv if installed)
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

/**
 * Decide which env file to load.
 * - Local dev: .env.local in the same folder as this file
 * - Production: .env one level ABOVE /htdocs (safer). If not readable, fallback to /htdocs/.env
 */
function __env_candidates(): array {
    // Default to production unless explicitly set
    $appEnv = getenv('APP_ENV') ?: 'production';

    // If this file is in /htdocs, parent is the account's home
    $above = dirname(__DIR__);        // parent of /htdocs
    $here  = __DIR__;                 // /htdocs

    if ($appEnv === 'local') {
        return [
            [$here,  '.env.local'],
            [$here,  '.env'],         // optional fallback
        ];
    }

    // production
    return [
        [$above, '.env'],             // best: /<home>/.env
        [$here,  '.env'],             // fallback: /htdocs/.env
    ];
}

/** Load env with phpdotenv if available, else fallback parser */
function __load_env(): void {
    // If phpdotenv is installed, prefer it
    if (class_exists('Dotenv\\Dotenv')) {
        foreach (__env_candidates() as [$path, $file]) {
            $full = $path . DIRECTORY_SEPARATOR . $file;
            if (is_readable($full)) {
                $dotenv = Dotenv\Dotenv::createImmutable($path, $file);
                $dotenv->safeLoad();
                return;
            }
        }
        return;
    }

    // Fallback: tiny .env parser (KEY=VALUE, ignores # comments)
    foreach (__env_candidates() as [$path, $file]) {
        $full = $path . DIRECTORY_SEPARATOR . $file;
        if (!is_readable($full)) continue;

        $lines = file($full, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v);

            // Strip optional surrounding quotes
            if ((str_starts_with($v, '"') && str_ends_with($v, '"')) ||
                (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
                $v = substr($v, 1, -1);
            }

            putenv("$k=$v");
            $_ENV[$k]    = $v;
            $_SERVER[$k] = $v;
        }
        return;
    }
}
__load_env();

/** Helper to read env values with default */
function env(string $key, $default = null) {
    $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($val === false || $val === null || $val === '') ? $default : $val;
}

/** OPTIONAL: central PDO connection (call get_pdo() anywhere) */
function get_pdo(): ?PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $host = env('DB_HOST');
    $name = env('DB_NAME');
    if (!$host || !$name) return null;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host, env('DB_PORT', '3306'), $name
    );

    $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASS'), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}
