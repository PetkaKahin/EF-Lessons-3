<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Infrastructure\Database\MigrationRunner;
use Infrastructure\Database\PdoConnection;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

final readonly class MigrationController
{
    public function __construct(
        private MigrationRunner $migrationRunner,
        private PdoConnection $pdoConnection,
    ) {
    }

    public function run(Request $request): Response
    {
        $appliedMigrations = $this->migrationRunner->run($this->pdoConnection->get());

        return new JsonResponse([
            'applied' => $appliedMigrations,
            'count' => count($appliedMigrations),
        ]);
    }
}
