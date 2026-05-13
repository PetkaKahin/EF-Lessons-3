<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Application\UseCase\Idempotency\IdempotencyConflictException;
use Application\UseCase\Idempotency\IdempotencyInProgressException;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use InvalidArgumentException;
use JsonException;
use Throwable;

final class ExceptionHandler
{
    public function handle(Throwable $exception): Response
    {
        if ($exception instanceof JsonException) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        if ($exception instanceof IdempotencyConflictException) {
            return new JsonResponse(['error' => $exception->getMessage()], 409);
        }

        if ($exception instanceof IdempotencyInProgressException) {
            return new JsonResponse(['error' => $exception->getMessage()], 409);
        }

        if ($exception instanceof InvalidArgumentException) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }

        return new JsonResponse(['error' => 'Internal server error'], 500);
    }
}
