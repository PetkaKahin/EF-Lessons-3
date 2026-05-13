<?php

declare(strict_types=1);

namespace Application\Contracts;

interface TransactionManagerInterface
{
    /**
     * Выполняет callback внутри транзакции
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function transactional(callable $callback): mixed;
}
