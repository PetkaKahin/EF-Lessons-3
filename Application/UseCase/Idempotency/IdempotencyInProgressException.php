<?php

declare(strict_types=1);

namespace Application\UseCase\Idempotency;

use RuntimeException;

final class IdempotencyInProgressException extends RuntimeException
{
    public static function forUnfinishedOperation(): self
    {
        return new self('Idempotent operation is still processing.');
    }
}
