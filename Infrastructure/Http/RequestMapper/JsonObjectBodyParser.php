<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper;

use Infrastructure\Kernel\Request;
use InvalidArgumentException;
use JsonException;
use stdClass;

final class JsonObjectBodyParser
{
    /**
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function parse(Request $request): array
    {
        $payload = json_decode($request->body, false, flags: JSON_THROW_ON_ERROR);

        if (!$payload instanceof stdClass) {
            throw new InvalidArgumentException('JSON body must be an object.');
        }

        return get_object_vars($payload);
    }
}
