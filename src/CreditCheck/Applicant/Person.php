<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;

use DateTimeImmutable;
use LaSouris\CreditCheck\Sdk\Support\Assert;

/**
 * One party to a credit check: a person, and optionally the partner whose income and debt
 * count toward the same household.
 */
final readonly class Person
{
    public function __construct(
        public string $initials,
        public string $firstName,
        public string $surname,
        public ?Gender $gender = null,
        public ?DateTimeImmutable $dateOfBirth= null,
        public ?ContactInformation $contactInformation= null,
        public ?Address $address = null,
    ) {
        Assert::length($initials, 1, 20, 'initials');
        Assert::length($firstName, 1, 75, 'firstName');
        Assert::length($surname, 1, 255, 'surname');
    }

    public function displayName(): string
    {
        return $this->firstName . ' ' . $this->surname;
    }
}
