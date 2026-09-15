<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Provider;

use Attribute;
use LaSouris\CreditCheck\Sdk\Support\Assert;

/**
 * Declares what a {@see CreditChecker} implementation is and what it can do.
 *
 * A provider's reach is a fixed property of the integration, not of an instance, so it is
 * declared once on the class rather than implemented as four methods. Reading it needs only a
 * class name — no construction, no credentials — which is what the framework packages do when
 * they build their provider registries.
 *
 * Read it back with {@see ProviderCapabilities::of()}.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Provider
{
    /** @var list<string> */
    public array $countries;

    /** @var list<string> */
    public array $currencies;

    /** @var list<Capability> */
    public array $capabilities;

    /**
     * @param string           $name         Short machine name, e.g. "edr". Unique per provider.
     * @param list<string>     $countries    ISO 3166-1 alpha-2 codes this provider can assess, e.g. ['NL'].
     * @param list<string>     $currencies   ISO 4217 codes this provider accepts, e.g. ['EUR'].
     * @param list<Capability> $capabilities Operations it can perform; the rest throw
     *                                       UnsupportedOperationException.
     */
    public function __construct(
        public string $name,
        array $countries,
        array $currencies,
        array $capabilities = [Capability::CREATE_CHECK, Capability::GET_RESULT],
    ) {
        Assert::length($name, 1, 64, 'name');
        Assert::notEmpty($countries, 'countries');
        Assert::notEmpty($currencies, 'currencies');
        Assert::allInstanceOf($capabilities, Capability::class, 'capabilities');

        $this->countries = Assert::allCountryCodes($countries, 'countries');
        $this->currencies = Assert::allCurrencyCodes($currencies, 'currencies');
        $this->capabilities = array_values($capabilities);
    }
}
