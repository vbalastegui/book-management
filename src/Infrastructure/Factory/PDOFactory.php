<?php

namespace BookManagement\Infrastructure\Factory;

use PDO;

class PDOFactory {
    public static function create(): PDO {
        $config = require __DIR__ . '/../../../config/database.php';
        $pdo = new PDO("sqlite:{$config['path']}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}

