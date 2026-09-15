<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Functional\Contract;

use DateTimeImmutable;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\ContactInformation;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Gender;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Person;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderNotFoundException;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderValidationException;
use LaSouris\CreditCheck\Sdk\CreditCheck\SalesChannel;
use LaSouris\CreditCheck\Sdk\CreditCheck\Subject;
use LaSouris\CreditCheck\Sdk\Request\CreateCreditCheck;
use LaSouris\CreditCheck\Sdk\Response\CheckStatus;
use LaSouris\CreditCheck\Sdk\Response\Decision;
use LaSouris\CreditCheck\Sdk\Tests\Fake\FakeCreditChecker;
use LaSouris\CreditCheck\Sdk\Tests\Fake\SampleData;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class CreditCheckerContractTest extends TestCase
{
    public function testSubmitThenGetResultRoundTrip(): void
    {
        $checker = new FakeCreditChecker();

        $submitted = $checker->submitCheck(SampleData::request());
        self::assertSame('fake', $submitted->provider);
        self::assertSame(CheckStatus::Submitted, $submitted->status);

        $result = $checker->getResult($submitted->reference);
        self::assertSame('fake', $result->provider);
        self::assertSame($submitted->reference, $result->reference);
        self::assertSame(Decision::Approved, $result->decision);
        self::assertTrue($result->isApproved());
    }

    public function testGetResultForUnknownReferenceThrows(): void
    {
        $this->expectException(ProviderNotFoundException::class);

        (new FakeCreditChecker())->getResult('CHECK-999');
    }

    public function testGetChangedListsSubmittedReferences(): void
    {
        $checker = new FakeCreditChecker();
        $first = $checker->submitCheck(SampleData::request());

        self::assertSame(
            [$first->reference],
            $checker->getChangedChecksSince(new DateTimeImmutable('2026-01-01'))->references,
        );
    }

    public function testRejectsUnsupportedCurrency(): void
    {
        $this->expectException(ProviderValidationException::class);

        $request = new CreateCreditCheck(
            'ORDER-GBP',
            new Subject(
                label: 'Car',
                variant: 'Base',
                amount: new Money(100000, new Currency('GBP')),
                termInMonths: 12,
                startDate: new DateTimeImmutable('2026-08-01'),
                endDate: new DateTimeImmutable('2027-08-01'),
            ),
            SalesChannel::Internet,
            SampleData::applicant(),
        );

        (new FakeCreditChecker())->submitCheck($request);
    }

    public function testRejectsUnsupportedCountry(): void
    {
        $this->expectException(ProviderValidationException::class);

        $german = new Person(
            initials: 'H.',
            firstName: 'Hans',
            surname: 'Müller',
            gender: Gender::Male,
            dateOfBirth: new DateTimeImmutable('1980-01-01'),
            contactInformation: new ContactInformation('h@example.de', SampleData::phoneNumber('+4915123456789', 'DE')),
            address: SampleData::address('DE'),
        );

        $request = new CreateCreditCheck(
            'ORDER-DE',
            SampleData::subject(),
            SalesChannel::Internet,
            new Applicant(person: $german, partner: $german),
        );

        (new FakeCreditChecker())->submitCheck($request);
    }
}
