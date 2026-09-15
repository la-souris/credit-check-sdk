<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Support;

final class Json
{
    /**
     * Drops null and empty values so optional fields are omitted from a serialized payload.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function filter(array $data): array
    {
        return array_filter($data, static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
