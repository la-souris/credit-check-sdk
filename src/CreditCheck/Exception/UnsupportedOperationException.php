<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

use LaSouris\CreditCheck\Sdk\Provider\Capability;

/**
 * Thrown when a caller invokes an operation a provider does not support
 * (guard with CreditChecker::supports() first).
 */
final class UnsupportedOperationException extends CreditCheckException
{
    public static function forCapability(string $provider, Capability $capability): self
    {
        return new self(sprintf('Provider "%s" does not support the "%s" operation.', $provider, $capability->value));
    }
}
