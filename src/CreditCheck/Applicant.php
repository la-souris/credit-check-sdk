<?php

namespace LaSouris\CreditCheck\Sdk\CreditCheck;

use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Person;

final readonly class Applicant
{
    public function __construct(
        public Person $person,
        public Person $partner,
    ) {
    }
}