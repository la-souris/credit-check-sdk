<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;

use LaSouris\CreditCheck\Sdk\Support\Assert;

/**
 * Where a person lives.
 *
 * The house number is a string so suffixed numbers ("12A", "3-bis") survive intact; providers
 * that need the numeric part separately split it in their own mapper.
 */
final readonly class Address
{
    public function __construct(
        public string $country,
        public string $houseNumber,
        public string $street,
        public string $postalCode,
        public string $city,
        public int $occupants = 1,
    ) {
        Assert::length($houseNumber, 1, 20, 'houseNumber');
        Assert::length($street, 1, 255, 'street');
        Assert::length($postalCode, 1, 16, 'postalCode');
        Assert::length($city, 1, 255, 'city');
        Assert::positive($occupants, 'occupants');
    }
}
