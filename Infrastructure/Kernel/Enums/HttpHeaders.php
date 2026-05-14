<?php

declare(strict_types=1);

namespace Infrastructure\Kernel\Enums;

enum HttpHeaders: string
{
    case CONTENT_TYPE = 'Content-Type';
    case AUTHORIZATION = 'Authorization';
    case IDEMPOTENCY_KEY = 'Idempotency-Key';
}
