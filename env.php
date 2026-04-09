<?php
/**
 * env.php — Lightweight .env file loader for PHP (no Composer needed)
 *
 * Usage:
 *   require_once 'env.php';
 *   $val = env('DB_HOST', 'localhost');  // second arg = default
 *
 * Include this ONCE at the very top of db_config.php (the entry point).
 */

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        // .env not found — silently fall through (use system env or defaults)
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and blank lines
        if ($line === '' || $line[0] === '#') continue;

        // Must contain '='
        if (strpos($line, '=') === false) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Strip inline comments  (e.g.  VALUE=foo   # comment)
        if (($pos = strpos($value, ' #')) !== false) {
            $value = trim(substr($value, 0, $pos));
        }

        // Strip surrounding quotes (single or double)
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // Only set if not already defined in the real environment
        if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/**
 * Get an env variable with an optional default fallback.
 *
 * @param  string $key
 * @param  mixed  $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    $val = $_ENV[$key] ?? getenv($key);
    if ($val === false || $val === null || $val === '') return $default;
    // Cast common boolean strings
    if ($val === 'true'  || $val === 'True'  || $val === '1') return true;
    if ($val === 'false' || $val === 'False' || $val === '0') return false;
    if ($val === 'null'  || $val === 'Null')                  return null;
    return $val;
}

// Auto-load .env from the project root (same directory as this file)
loadEnv(__DIR__ . '/.env');
