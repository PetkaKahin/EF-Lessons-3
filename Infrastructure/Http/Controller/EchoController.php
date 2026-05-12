<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Application\UseCase\EchoJsonUseCase;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;
use JsonException;

final readonly class EchoController
{
    public function __construct(
        private EchoJsonUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $payload = json_decode($request->body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        return new JsonResponse($this->useCase->execute($payload));
    }
}
