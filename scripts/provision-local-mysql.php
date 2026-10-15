<?php

// One-time convenience for this machine's local, passwordless XAMPP development server.
// Never use this helper in production; configure a least-privilege application account instead.
if (PHP_SAPI !== 'cli') {
    exit(1);
}
$database = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$serverVersion = $database->query('SELECT VERSION()')->fetchColumn();
$driver = str_contains(strtolower($serverVersion), 'mariadb') ? 'mariadb' : 'mysql';
$database->exec('CREATE DATABASE IF NOT EXISTS laravel_mastery_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$path = dirname(__DIR__).'/.env';
$env = file_get_contents($path);
foreach (['APP_NAME' => '"Laravel Mastery Academy"', 'APP_TIMEZONE' => 'Asia/Karachi', 'APP_URL' => 'http://127.0.0.1:8873', 'DB_CONNECTION' => $driver, 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '3306', 'DB_DATABASE' => 'laravel_mastery_academy', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => ''] as $key => $value) {
    $pattern = '/^#?\s*'.preg_quote($key, '/').'=.*/m';
    $env = preg_match($pattern, $env) ? preg_replace($pattern, $key.'='.$value, $env) : $env."\n".$key.'='.$value."\n";
}
$env = preg_replace('/^PHP_CLI_SERVER_WORKERS=.*/m', '# PHP_CLI_SERVER_WORKERS=4', $env);
file_put_contents($path, $env);
echo "Local MySQL-compatible schema is ready using the $driver driver; .env targets laravel_mastery_academy.\n";
