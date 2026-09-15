<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

use Money\Money;

/**
 * The part of a decision that belongs to one applicant rather than the check as a whole.
 *
 * Both fields are optional: a bureau may screen the household jointly and report nothing
 * per person, or finish screening before the affordability figures are in.
 */
final readonly class ApplicantResult
{
    public function __construct(
        public ?TrafficLight $trafficLight = null,
        public ?Money $expendableIncome = null,
    ) {
    }
}
