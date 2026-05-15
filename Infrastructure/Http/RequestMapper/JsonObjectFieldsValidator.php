<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper;

use InvalidArgumentException;

final class JsonObjectFieldsValidator
{
    /**
     * @param array<string, mixed> $payload
     * @param string[] $allowedFields
     */
    public function assertAllowedFields(array $payload, array $allowedFields): void
    {
        $unknownFields = array_diff(array_keys($payload), $allowedFields);

        if ($unknownFields !== []) {
            throw new InvalidArgumentException('Unknown fields: ' . implode(', ', $unknownFields) . '.');
        }
    }
}
