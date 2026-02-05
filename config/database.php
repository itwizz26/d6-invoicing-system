<?php

$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;

        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'db'   => $_ENV['DB_NAME'] ?? 'invoicing',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'pass' => $_ENV['DB_PASS'] ?? '',
];
