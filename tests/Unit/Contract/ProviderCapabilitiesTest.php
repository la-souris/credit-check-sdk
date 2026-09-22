<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Unit\Contract;

use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\InvalidArgumentException;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\MissingProviderAttributeException;
use LaSouris\CreditCheck\Sdk\Provider\Capability;
use LaSouris\CreditCheck\Sdk\Provider\CreditChecker;
use LaSouris\CreditCheck\Sdk\Provider\Provider;
use LaSouris\CreditCheck\Sdk\Provider\ProviderCapabilities;
use LaSouris\CreditCheck\Sdk\Tests\Fake\FakeCreditChecker;
use Money\Currency;
use PHPUnit\Framework\TestCase;

final class ProviderCapabilitiesTest extends TestCase
{
    public function testReadsTheAttributeFromAnInstance(): void
    {
        $capabilities = ProviderCapabilities::of(new FakeCreditChecker());

        self::assertSame('fake', $capabilities->name);
        self::assertTrue($capabilities->supports(Capability::CREATE_CHECK));
        self::assertTrue($capabilities->supports(Capability::GET_RESULT));
        self::assertTrue($capabilities->supports(Capability::LIST_CHANGED));
        self::assertTrue($capabilities->assesses('NL'));
        self::assertFalse($capabilities->assesses('DE'));
        self::assertTrue($capabilities->accepts(new Currency('EUR')));
        self::assertFalse($capabilities->accepts(new Currency('GBP')));
    }

    /**
     * The whole point of the attribute: discovery without constructing the provider, which is
     * what the framework packages do when they build their registries.
     */
    public function testReadsTheAttributeFromAClassName(): void
    {
        self::assertSame('fake', ProviderCapabilities::of(FakeCreditChecker::class)->name);
    }

    public function testResultIsCachedPerClass(): void
    {
        self::assertSame(
            ProviderCapabilities::of(FakeCreditChecker::class),
            ProviderCapabilities::of(new FakeCreditChecker()),
        );
    }

    public function testCurrencyCodesAreHydratedIntoCurrencyObjects(): void
    {
        $currencies = ProviderCapabilities::of(FakeCreditChecker::class)->currencies;

        self::assertContainsOnlyInstancesOf(Currency::class, $currencies);
        self::assertSame('EUR', $currencies[0]->getCode());
    }

    public function testProviderWithoutTheAttributeThrows(): void
    {
        $this->expectException(MissingProviderAttributeException::class);

        ProviderCapabilities::of(UnannotatedChecker::class);
    }

    public function testUnknownClassThrows(): void
    {
        $this->expectException(MissingProviderAttributeException::class);

        /** @phpstan-ignore-next-line intentional bad input */
        ProviderCapabilities::of('No\Such\Provider');
    }

    public function testAttributeIsInheritedFromABaseClass(): void
    {
        $capabilities = ProviderCapabilities::of(SubclassedChecker::class);

        self::assertSame('base', $capabilities->name);
        self::assertTrue($capabilities->assesses('BE'));
    }

    public function testAttributeRejectsAnInvalidCurrencyCode(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Provider(name: 'x', countries: ['NL'], currencies: ['euro']);
    }

    public function testAttributeRejectsAnInvalidCountryCode(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Provider(name: 'x', countries: ['Netherlands'], currencies: ['EUR']);
    }

    public function testAttributeRejectsEmptyCountries(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Provider(name: 'x', countries: [], currencies: ['EUR']);
    }

    public function testAttributeDefaultsToTheTwoUniversalCapabilities(): void
    {
        $attribute = new Provider(name: 'x', countries: ['NL'], currencies: ['EUR']);

        self::assertSame([Capability::CREATE_CHECK, Capability::GET_RESULT], $attribute->capabilities);
    }
}

class UnannotatedChecker implements CreditChecker
{
    public function submitCheck(\LaSouris\CreditCheck\Sdk\Request\CreateCreditCheckRequest $request): \LaSouris\CreditCheck\Sdk\Response\CreateCreditCheckResponse
    {
        throw new \LogicException('not used');
    }

    public function getResult(string $reference): \LaSouris\CreditCheck\Sdk\Response\GetCreditCheckResponse
    {
        throw new \LogicException('not used');
    }

    public function getChangedChecksSince(\DateTimeInterface $since): \LaSouris\CreditCheck\Sdk\Response\ChangedChecksResponse
    {
        return new \LaSouris\CreditCheck\Sdk\Response\ChangedChecksResponse('unannotated');
    }
}

#[Provider(name: 'base', countries: ['BE'], currencies: ['EUR'])]
abstract class BaseChecker extends UnannotatedChecker
{
}

final class SubclassedChecker extends BaseChecker
{
}
