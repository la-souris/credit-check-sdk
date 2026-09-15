<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

/**
 * HTTP 5xx — a transient provider-side failure; retrying later may succeed.
 */
class ProviderTransientException extends ProviderException
{
    public function isRetryable(): bool
    {
        return true;
    }
}
