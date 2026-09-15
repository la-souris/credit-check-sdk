<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Fake;

use DateTimeImmutable;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Address;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\ContactInformation;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Gender;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\Person;
use LaSouris\CreditCheck\Sdk\CreditCheck\SalesChannel;
use LaSouris\CreditCheck\Sdk\CreditCheck\Subject;
use LaSouris\CreditCheck\Sdk\Request\CreateCreditCheck;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

/**
 * Builders for valid domain objects, so tests share one canonical fixture.
 */
final class SampleData
{
    public static function eur(string $decimal): Money
    {
        return (new DecimalMoneyParser(new ISOCurrencies()))->parse($decimal, new Currency('EUR'));
    }

    public static function phoneNumber(string $number = '+31612345678', string $region = 'NL'): PhoneNumber
    {
        return PhoneNumberUtil::getInstance()->parse($number, $region);
    }

    public static function address(string $country = 'NL'): Address
    {
        return new Address(
            country: $country,
            houseNumber: '12',
            street: 'Kerkstraat',
            postalCode: '1011AA',
            city: 'Amsterdam',
            occupants: 2,
        );
    }

    public static function contactInformation(): ContactInformation
    {
        return new ContactInformation('jan@example.com', self::phoneNumber());
    }

    public static function person(): Person
    {
        return new Person(
            initials: 'J.',
            firstName: 'Jan',
            surname: 'de Vries',
            gender: Gender::Male,
            dateOfBirth: new DateTimeImmutable('1990-05-01'),
            contactInformation: self::contactInformation(),
            address: self::address(),
        );
    }

    public static function partner(): Person
    {
        return new Person(
            initials: 'M.',
            firstName: 'Marieke',
            surname: 'de Vries',
            gender: Gender::Female,
            dateOfBirth: new DateTimeImmutable('1992-03-14'),
            contactInformation: self::contactInformation(),
            address: self::address(),
        );
    }

    public static function applicant(): Applicant
    {
        return new Applicant(
            person: self::person(),
            partner: self::partner(),
        );
    }

    public static function subject(): Subject
    {
        return new Subject(
            label: 'Tesla',
            variant: 'Model 3',
            amount: self::eur('45000.00'),
            termInMonths: 60,
            startDate: new DateTimeImmutable('2026-08-01'),
            endDate: new DateTimeImmutable('2031-08-01'),
        );
    }

    public static function request(): CreateCreditCheck
    {
        return new CreateCreditCheck(
            'ORDER-123',
            self::subject(),
            SalesChannel::Internet,
            self::applicant(),
        );
    }
}
