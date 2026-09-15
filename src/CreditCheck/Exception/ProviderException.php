<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

use Throwable;

/**
 * Thrown for any error returned by (or while reaching) a provider's backend.
 *
 * Providers translate their transport/HTTP failures into this hierarchy so that
 * callers can handle credit-check errors uniformly regardless of the supplier.
 */
class ProviderException extends CreditCheckException
{
    /**
     * @param list<string>         $errors Human-readable messages returned by the provider.
     * @param array<string, mixed> $body   Decoded provider response body, for detail.
     */
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
        public readonly array $errors = [],
        public readonly array $body = [],
        public readonly ?string $providerCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus ?? 0, $previous);
    }

    /**
     * Build the most specific subtype for an HTTP status code.
     *
     * @param list<string>         $errors
     * @param array<string, mixed> $body
     */
    public static function fromHttpStatus(
        string $message,
        int $httpStatus,
        array $errors = [],
        array $body = [],
        ?string $providerCode = null,
        ?Throwable $previous = null,
    ): self {
        $class = match (true) {
            $httpStatus === 401, $httpStatus === 403 => ProviderAuthenticationException::class,
            $httpStatus === 404 => ProviderNotFoundException::class,
            $httpStatus === 400, $httpStatus === 409, $httpStatus === 422 => ProviderValidationException::class,
            $httpStatus === 429 => ProviderRateLimitException::class,
            $httpStatus >= 500 => ProviderTransientException::class,
            default => self::class,
        };

        return new $class($message, $httpStatus, $errors, $body, $providerCode, $previous);
    }

    /**
     * Whether retrying the request could plausibly succeed.
     */
    public function isRetryable(): bool
    {
        return false;
    }
}
