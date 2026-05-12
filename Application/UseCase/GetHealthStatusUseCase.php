<?php

declare(strict_types=1);

namespace Application\UseCase;

final class GetHealthStatusUseCase
{
    /**
     * @return array{status: string}
     */
    public function execute(): array
    {
        return ['status' => 'ok'];
    }
}
