<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

/**
 * HTTP 429 — the provider is rate-limiting us; retrying later may succeed.
 */
class ProviderRateLimitException extends ProviderException
{
    public function isRetryable(): bool
    {
        return true;
    }
}
