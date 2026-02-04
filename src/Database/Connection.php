<?php

namespace App\Database;

use PDO;
use PDOException;

class Connection {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            try {
                self::$instance = new PDO(
                    "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
                    $config['user'],
                    $config['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                header('Content-Type: application/json', true, 500);
                echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
                exit();
            }
        }
        return self::$instance;
    }
}
