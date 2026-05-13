<?php

declare(strict_types=1);

namespace Infrastructure\Console;

use Infrastructure\Database\MigrationRunner;
use Infrastructure\Database\PdoConnection;
use Throwable;

final readonly class RunMigrationsCommand
{
    public function __construct(
        private MigrationRunner $migrationRunner,
        private PdoConnection $pdoConnection,
    ) {
    }

    /**
     * @return string[]
     *
     * @throws Throwable
     */
    public function execute(): array
    {
        return $this->migrationRunner->run($this->pdoConnection->get());
    }
}

