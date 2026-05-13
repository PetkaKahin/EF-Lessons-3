<?php

declare(strict_types=1);

namespace Infrastructure\Database;

use Infrastructure\Config\Config;
use PDO;

final class PdoConnection
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly Config $config,
    ) {
    }

    public function get(): PDO
    {
        return $this->pdo ??= PdoFactory::create(
            $this->config->get('DATABASE_PATH'),
        );
    }
}
