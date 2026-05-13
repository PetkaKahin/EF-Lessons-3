<?php

declare(strict_types=1);

namespace Infrastructure\Database;

use Application\Contracts\TransactionManagerInterface;
use Throwable;

final readonly class PdoTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private PdoConnection $pdoConnection,
    ) {
    }

    public function transactional(callable $callback): mixed
    {
        $pdo = $this->pdoConnection->get();
        $pdo->beginTransaction();

        try {
            $result = $callback();
            $pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }
}
