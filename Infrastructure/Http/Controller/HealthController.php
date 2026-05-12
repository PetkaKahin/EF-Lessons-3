<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Application\UseCase\GetHealthStatusUseCase;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

final readonly class HealthController
{
    public function __construct(
        private GetHealthStatusUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return new JsonResponse($this->useCase->execute());
    }
}
