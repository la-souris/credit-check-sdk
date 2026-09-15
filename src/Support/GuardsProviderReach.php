<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Support;

use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderValidationException;
use LaSouris\CreditCheck\Sdk\Provider\ProviderCapabilities;
use Money\Currency;
use Money\Money;

/**
 * Reach guards for providers, reading the class's own #[Provider] attribute.
 *
 * A provider calls these at the top of its submit methods to reject out-of-reach input before
 * any network call is made.
 */
trait GuardsProviderReach
{
    protected function reach(): ProviderCapabilities
    {
        return ProviderCapabilities::of($this);
    }

    protected function assertSupportedCountry(string $country): void
    {
        $reach = $this->reach();

        if (!$reach->assesses($country)) {
            throw new ProviderValidationException(sprintf(
                'Provider "%s" cannot assess applicants in country "%s".',
                $reach->name,
                $country,
            ));
        }
    }

    protected function assertSupportedCurrency(Currency $currency): void
    {
        $reach = $this->reach();

        if (!$reach->accepts($currency)) {
            throw new ProviderValidationException(sprintf(
                'Provider "%s" does not accept currency "%s".',
                $reach->name,
                $currency->getCode(),
            ));
        }
    }

    protected function assertSupportedMoney(Money $money): void
    {
        $this->assertSupportedCurrency($money->getCurrency());
    }
}
