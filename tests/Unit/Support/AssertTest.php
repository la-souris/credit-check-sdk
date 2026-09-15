<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Unit\Support;

use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\InvalidArgumentException;
use LaSouris\CreditCheck\Sdk\Support\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AssertTest extends TestCase
{
    public function testLengthPassesWithinBounds(): void
    {
        self::assertSame('abc', Assert::length('abc', 1, 5, 'field'));
    }

    public function testLengthRejectsTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Assert::length('abcdef', 1, 5, 'field');
    }

    public function testNotBlankRejectsWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Assert::notBlank('   ', 'field');
    }

    public function testPositiveRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Assert::positive(0, 'field');
    }

    #[DataProvider('emailProvider')]
    public function testEmail(string $value, bool $valid): void
    {
        if (!$valid) {
            $this->expectException(InvalidArgumentException::class);
        }

        $result = Assert::email($value, 'email');

        if ($valid) {
            self::assertSame($value, $result);
        }
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function emailProvider(): iterable
    {
        yield 'valid' => ['jan@example.com', true];
        yield 'invalid' => ['not-an-email', false];
    }



    public function testAllInstanceOfRejectsWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Assert::allInstanceOf(['a string'], \DateTimeInterface::class, 'items');
    }
}
