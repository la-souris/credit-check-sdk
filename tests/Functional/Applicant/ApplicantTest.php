<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Functional\Applicant;

use DateTimeImmutable;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\ContactInformation;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Gender;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Person;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\InvalidArgumentException;
use LaSouris\CreditCheck\Sdk\Tests\Fake\SampleData;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\TestCase;

final class ApplicantTest extends TestCase
{
    public function testDisplayNameJoinsFirstNameAndSurname(): void
    {
        self::assertSame('Jan de Vries', SampleData::applicant()->person->displayName());
    }

    public function testApplicantKeepsPersonAndPartnerApart(): void
    {
        $applicant = SampleData::applicant();

        self::assertSame('Jan de Vries', $applicant->person->displayName());
        self::assertSame('Marieke de Vries', $applicant->partner->displayName());
    }

    public function testRejectsBlankInitials(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Person(
            initials: '',
            firstName: 'Jan',
            surname: 'de Vries',
            gender: Gender::Male,
            dateOfBirth: new DateTimeImmutable('1990-05-01'),
            contactInformation: SampleData::contactInformation(),
            address: SampleData::address(),
        );
    }

    public function testContactInformationRejectsInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ContactInformation('nope', SampleData::phoneNumber());
    }

    public function testContactInformationRejectsAnImpossibleNumber(): void
    {
        // Parses fine (well-formed for NL) but no such number can exist.
        $impossible = PhoneNumberUtil::getInstance()->parse('+3161', 'NL');

        $this->expectException(InvalidArgumentException::class);

        new ContactInformation('jan@example.com', $impossible);
    }

    public function testContactInformationKeepsTheParsedNumber(): void
    {
        $number = SampleData::phoneNumber('06 12345678');
        $contact = new ContactInformation('jan@example.com', $number);

        self::assertSame($number, $contact->mobileNumber);
        self::assertSame(31, $contact->mobileNumber->getCountryCode());
        self::assertSame('612345678', $contact->mobileNumber->getNationalNumber());
    }

    public function testAddressRejectsABlankHouseNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SampleData::address();
        new \LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Address(
            country: 'NL',
            houseNumber: '',
            street: 'Kerkstraat',
            postalCode: '1011AA',
            city: 'Amsterdam',
        );
    }
}
