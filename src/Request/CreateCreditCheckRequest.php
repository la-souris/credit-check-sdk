<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Request;

use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;
use LaSouris\CreditCheck\Sdk\CreditCheck\SalesChannel;
use LaSouris\CreditCheck\Sdk\CreditCheck\Subject;
use LaSouris\CreditCheck\Sdk\Support\Assert;
use Stringable;

/**
 * A request to assess one or more applicants against a financed {@see Subject}.
 *
 * The {@see $reference} is the caller's own identifier for this check; the provider echoes it
 * back on the result so submissions can be reconciled without persisting provider ids first.
 * It accepts any {@see Stringable}, so an application's own order-id value object can be
 * handed over as-is.
 */
final readonly class CreateCreditCheckRequest
{
    /** @var Applicant[] */
    public array $applicants;

    public string $reference;

    public function __construct(
        string|Stringable $reference,
        public Subject $subject,
        public SalesChannel $salesChannel = SalesChannel::Unknown,
        Applicant ...$applicants,
    ) {
        $this->reference = Assert::length((string) $reference, 1, 255, 'reference');
        $this->applicants = Assert::notEmpty($applicants, 'applicants');
    }

    public function primaryApplicant(): Applicant
    {
        return $this->applicants[0];
    }
}
