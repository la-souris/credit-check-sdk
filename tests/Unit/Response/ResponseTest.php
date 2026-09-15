<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Unit\Response;

use DateTimeImmutable;
use LaSouris\CreditCheck\Sdk\Response\ChangedChecksResponse;
use LaSouris\CreditCheck\Sdk\Response\CheckStatus;
use LaSouris\CreditCheck\Sdk\Response\CreateCreditCheckResponse;
use LaSouris\CreditCheck\Sdk\Response\Decision;
use LaSouris\CreditCheck\Sdk\Response\GetCreditCheckResponse;
use LaSouris\CreditCheck\Sdk\Response\RejectionReason;
use LaSouris\CreditCheck\Sdk\Response\Response;
use LaSouris\CreditCheck\Sdk\Response\TrafficLight;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testSubmissionCarriesReferenceProviderAndRawBody(): void
    {
        $response = new CreateCreditCheckResponse('edr', '4242', CheckStatus::Submitted, ['orderId' => 4242]);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('4242', $response->reference);
        self::assertSame(CheckStatus::Submitted, $response->status);
        self::assertSame('edr', $response->provider);
        self::assertSame(['orderId' => 4242], $response->raw);
    }

    public function testSubmissionDefaultsToSubmittedAndAnEmptyRawBody(): void
    {
        $response = new CreateCreditCheckResponse('fake', '1');

        self::assertSame(CheckStatus::Submitted, $response->status);
        self::assertSame([], $response->raw);
    }

    public function testResultCarriesTheFullDecision(): void
    {
        $capacity = Money::EUR(150000);
        $reason = new RejectionReason('ShortageOfIncome');
        $startedAt = new DateTimeImmutable('2026-01-01T09:00:00+00:00');
        $completedAt = new DateTimeImmutable('2026-01-01T09:05:00+00:00');

        $response = new GetCreditCheckResponse(
            'edr',
            '4242',
            Decision::Rejected,
            CheckStatus::Completed,
            TrafficLight::Red,
            $capacity,
            [$reason],
            [],
            $startedAt,
            $completedAt,
            ['status' => 'done'],
        );

        self::assertSame('4242', $response->reference);
        self::assertSame(Decision::Rejected, $response->decision);
        self::assertSame(CheckStatus::Completed, $response->status);
        self::assertSame(TrafficLight::Red, $response->trafficLight);
        self::assertSame($capacity, $response->expendableIncome);
        self::assertSame([$reason], $response->rejectionReasons);
        self::assertSame([], $response->applicantResults);
        self::assertSame($startedAt, $response->startedAt);
        self::assertSame($completedAt, $response->completedAt);
        self::assertSame('edr', $response->provider);
        self::assertSame(['status' => 'done'], $response->raw);
    }

    public function testResultReportsTheDecision(): void
    {
        $approved = new GetCreditCheckResponse('edr', '1', Decision::Approved, CheckStatus::Completed);
        $rejected = new GetCreditCheckResponse('edr', '2', Decision::Rejected, CheckStatus::Completed);
        $pending = new GetCreditCheckResponse('edr', '3', Decision::Pending, CheckStatus::InProgress);

        self::assertTrue($approved->isApproved());
        self::assertTrue($approved->isFinal());
        self::assertFalse($approved->isRejected());

        self::assertTrue($rejected->isRejected());
        self::assertTrue($rejected->isFinal());

        self::assertFalse($pending->isApproved());
        self::assertFalse($pending->isRejected());
        self::assertFalse($pending->isFinal());
    }

    /**
     * getChangedChecksSince() answers with a plain list of references, and a list-shaped raw
     * body has to survive just as well.
     */
    public function testChangedChecksCarriesAListOfReferences(): void
    {
        $response = new ChangedChecksResponse('edr', ['10', '20'], [10, 20]);

        self::assertSame(['10', '20'], $response->references);
        self::assertSame('edr', $response->provider);
        self::assertSame([10, 20], $response->raw);
    }

    public function testChangedChecksDefaultsToNothingChanged(): void
    {
        $response = new ChangedChecksResponse('fake');

        self::assertSame([], $response->references);
        self::assertSame([], $response->raw);
    }
}
