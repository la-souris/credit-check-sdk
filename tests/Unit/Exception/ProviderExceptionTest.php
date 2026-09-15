<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Unit\Exception;

use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderAuthenticationException;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderException;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderNotFoundException;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderRateLimitException;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderTransientException;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProviderExceptionTest extends TestCase
{
    #[DataProvider('statusProvider')]
    public function testFromHttpStatusPicksSubtype(int $status, string $expectedClass, bool $retryable): void
    {
        $exception = ProviderException::fromHttpStatus('boom', $status, ['e1'], ['raw' => true], 'CODE');

        self::assertInstanceOf($expectedClass, $exception);
        self::assertSame($status, $exception->httpStatus);
        self::assertSame(['e1'], $exception->errors);
        self::assertSame(['raw' => true], $exception->body);
        self::assertSame('CODE', $exception->providerCode);
        self::assertSame($retryable, $exception->isRetryable());
    }

    /**
     * @return iterable<string, array{int, class-string, bool}>
     */
    public static function statusProvider(): iterable
    {
        yield '401' => [401, ProviderAuthenticationException::class, false];
        yield '403' => [403, ProviderAuthenticationException::class, false];
        yield '404' => [404, ProviderNotFoundException::class, false];
        yield '400' => [400, ProviderValidationException::class, false];
        yield '422' => [422, ProviderValidationException::class, false];
        yield '429' => [429, ProviderRateLimitException::class, true];
        yield '500' => [500, ProviderTransientException::class, true];
        yield '418' => [418, ProviderException::class, false];
    }
}
