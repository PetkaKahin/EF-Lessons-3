<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Application\UseCase\EchoJsonUseCase;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

final readonly class EchoController
{
    public function __construct(
        private EchoJsonUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $payload = json_decode($request->body, true, flags: JSON_THROW_ON_ERROR);

        return new JsonResponse($this->useCase->execute($payload));
    }
}
