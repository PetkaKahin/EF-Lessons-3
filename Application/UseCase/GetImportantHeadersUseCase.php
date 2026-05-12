<?php

declare(strict_types=1);

namespace Application\UseCase;

final class GetImportantHeadersUseCase
{
    /**
     * @return array<string, string|null>
     */
    public function execute(
        ?string $userAgent,
        ?string $accept,
        ?string $authorization,
    ): array {
        $headers = [
            'User-Agent' => $userAgent,
            'Accept' => $accept,
        ];

        if ($authorization !== null) {
            $headers['Authorization'] = $authorization;
        }

        return $headers;
    }
}
