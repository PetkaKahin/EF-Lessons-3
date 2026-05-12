<?php

declare(strict_types=1);

namespace Infrastructure\Database;

use PDO;

final class PdoFactory
{
    public static function create(): PDO
    {
        $databaseDirectory = dirname(__DIR__, 2) . '/var';

        if (!is_dir($databaseDirectory)) {
            mkdir($databaseDirectory, 0777, true);
        }

        $pdo = new PDO('sqlite:' . $databaseDirectory . '/app.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
