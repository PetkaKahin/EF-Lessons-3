<?php

declare(strict_types=1);

namespace Application\UseCase;

final class EchoJsonUseCase
{
    public function execute(mixed $payload): mixed
    {
        return $payload;
    }
}
