<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Functional\Request;

use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\InvalidArgumentException;
use LaSouris\CreditCheck\Sdk\CreditCheck\SalesChannel;
use LaSouris\CreditCheck\Sdk\Request\CreateCreditCheck;
use LaSouris\CreditCheck\Sdk\Tests\Fake\SampleData;
use PHPUnit\Framework\TestCase;
use Stringable;

final class CreditCheckRequestTest extends TestCase
{
    public function testPrimaryApplicantIsTheFirst(): void
    {
        $request = SampleData::request();

        self::assertSame($request->applicants[0], $request->primaryApplicant());
    }

    public function testAcceptsAStringableReference(): void
    {
        $orderId = new class implements Stringable {
            public function __toString(): string
            {
                return 'ORDER-VO-9';
            }
        };

        $request = new CreateCreditCheck($orderId, SampleData::subject(), SalesChannel::Unknown, SampleData::applicant());

        self::assertSame('ORDER-VO-9', $request->reference);
    }

    public function testRejectsEmptyApplicants(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateCreditCheck('ORDER-1', SampleData::subject());
    }

    public function testRejectsNonApplicantEntries(): void
    {
        // $applicants is now a typed variadic (Applicant ...$applicants), so PHP itself
        // enforces this at the call boundary with a TypeError, not our own validation.
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line intentional bad input */
        new CreateCreditCheck('ORDER-1', SampleData::subject(), SalesChannel::Unknown, 'not an applicant');
    }
}
