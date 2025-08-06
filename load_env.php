<?php
function loadEnv($path = __DIR__.'/.env') {
    if (!file_exists($path)) {
        $path = __DIR__.'/.env.example';
        if (!file_exists($path)) {
            return;
        }
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = array_map('trim', explode('=', $line, 2));
        if (!array_key_exists($key, $_ENV)) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}
loadEnv();
// foreach ($_ENV as $k => $v) {
//     error_log("ENV[{$k}] = {$v}");
// }
?>