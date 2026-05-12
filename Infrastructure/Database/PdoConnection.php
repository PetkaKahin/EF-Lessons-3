<?php

declare(strict_types=1);

namespace Infrastructure\Database;

use PDO;

final class PdoConnection
{
    private ?PDO $pdo = null;

    public function get(): PDO
    {
        return $this->pdo ??= PdoFactory::create();
    }
}
