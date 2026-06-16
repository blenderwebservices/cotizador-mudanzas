<?php
// Configuración de la API de Gemini

if (!function_exists('env')) {
    function env($key, $default = null) {
        static $dotenv = null;
        if ($dotenv === null) {
            $dotenv = [];
            $envPath = __DIR__ . '/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) {
                        continue;
                    }
                    if (strpos($line, '=') !== false) {
                        list($name, $value) = explode('=', $line, 2);
                        $dotenv[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
                    }
                }
            }
        }
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $dotenv[$key] ?? $default;
    }
}

define('GEMINI_API_KEY', env('GEMINI_API_KEY'));
define('GEMINI_MODEL', env('GEMINI_MODEL', 'gemini-3.5-flash-lite-preview'));
?>

