<?php

declare(strict_types=1);

namespace Application\UseCase\Idempotency;

use RuntimeException;

final class IdempotencyConflictException extends RuntimeException
{
    public static function forDifferentRequest(): self
    {
        return new self('Idempotency-Key already used with another request.');
    }
}
