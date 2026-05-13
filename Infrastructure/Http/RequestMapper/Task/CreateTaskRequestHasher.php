<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper\Task;

use Application\DTO\Task\CreateTaskInput;
use Infrastructure\Kernel\Request;
use JsonException;

/**
 * Создаёт хэш создания таски
 */
final class CreateTaskRequestHasher
{
    /**
     * @throws JsonException
     */
    public function hash(Request $request, CreateTaskInput $input): string
    {
        $canonicalRequest = [
            'method' => strtoupper($request->method),
            'path' => $request->path,
            'body' => [
                'title' => $input->title,
                'description' => $input->description,
                'status' => $input->status->value,
            ],
        ];

        return hash(
            'sha256',
            // JSON_THROW_ON_ERROR чтобы получить исключение сразу
            json_encode($canonicalRequest, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
        );
    }
}
