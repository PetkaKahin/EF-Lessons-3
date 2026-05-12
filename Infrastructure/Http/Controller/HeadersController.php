<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Application\UseCase\GetImportantHeadersUseCase;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

final readonly class HeadersController
{
    public function __construct(
        private GetImportantHeadersUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return new JsonResponse($this->useCase->execute(
            userAgent: $request->header('User-Agent'),
            accept: $request->header('Accept'),
            authorization: $request->header('Authorization'),
        ));
    }
}
