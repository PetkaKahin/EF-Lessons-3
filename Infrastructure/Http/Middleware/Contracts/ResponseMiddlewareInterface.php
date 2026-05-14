<?php

declare(strict_types=1);

namespace Infrastructure\Http\Middleware\Contracts;

use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

interface ResponseMiddlewareInterface
{
    public function processResponse(Request $request, Response $response): Response;
}
