<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

use DateTimeImmutable;
use Money\Money;

/**
 * A bureau's decision on a submitted check, normalised across providers.
 *
 * Everything past the status is optional, because bureaus differ in what they report and in
 * how much of it is available before the check completes. The untouched provider response is
 * kept in {@see Response::$raw} so nothing is lost for auditing or for reading a
 * provider-specific field the SDK does not model.
 */
final readonly class GetCreditCheckResponse extends Response
{
    /**
     * @param list<RejectionReason>   $rejectionReasons
     * @param list<ApplicantResult>   $applicantResults In the order the applicants were submitted.
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        string $provider,
        public string $reference,
        public Decision $decision,
        public CheckStatus $status,
        public ?TrafficLight $trafficLight = null,
        public ?Money $expendableIncome = null,
        public array $rejectionReasons = [],
        public array $applicantResults = [],
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $completedAt = null,
        array $raw = [],
    ) {
        parent::__construct($provider, $raw);
    }

    public function isApproved(): bool
    {
        return $this->decision === Decision::Approved;
    }

    public function isRejected(): bool
    {
        return $this->decision === Decision::Rejected;
    }

    /**
     * Whether the decision can still change — the signal to stop polling.
     */
    public function isFinal(): bool
    {
        return $this->decision->isFinal();
    }
}
