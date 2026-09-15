<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck;

use DateTimeImmutable;
use LaSouris\CreditCheck\Sdk\Support\Assert;
use Money\Money;

/**
 * What the credit check is for: the financed amount, its term, and a description of the
 * underlying product (e.g. the car in a lease).
 */
final readonly class Subject
{
    public function __construct(
        public string $label,
        public string $variant,
        public Money $amount,
        public int $termInMonths,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
    ) {
        Assert::length($label, 1, 255, 'label');
        Assert::length($variant, 1, 255, 'variant');
        Assert::positive($termInMonths, 'termInMonths');
    }
}
