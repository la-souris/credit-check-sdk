<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Provider;

use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\MissingProviderAttributeException;
use Money\Currency;
use ReflectionClass;

/**
 * A read-model of one provider's {@see Provider} attribute — handy for discovery, routing and UI.
 *
 * Resolve it from an instance or from a bare class name; results are cached per class, so the
 * reflection happens once per process.
 */
final readonly class ProviderCapabilities
{
    /**
     * @param list<Capability> $capabilities
     * @param list<string>     $countries ISO 3166-1 alpha-2 codes.
     * @param list<Currency>   $currencies
     */
    public function __construct(
        public string $name,
        public array $capabilities,
        public array $countries,
        public array $currencies,
    ) {
    }

    /**
     * @param CreditChecker|class-string $provider An instance, or the class name of one.
     *
     * @throws MissingProviderAttributeException when the class carries no #[Provider] attribute.
     */
    public static function of(CreditChecker|string $provider): self
    {
        // A static local rather than a static property: a readonly class may not declare one.
        static $cache = [];

        $class = is_string($provider) ? $provider : $provider::class;

        return $cache[$class] ??= self::read($class);
    }

    /**
     * @param class-string $class
     */
    private static function read(string $class): self
    {
        if (!class_exists($class)) {
            throw new MissingProviderAttributeException(
                sprintf('Credit-check provider class "%s" does not exist.', $class),
            );
        }

        $attribute = self::findAttribute(new ReflectionClass($class));

        if ($attribute === null) {
            throw new MissingProviderAttributeException(sprintf(
                'Credit-check provider "%s" is missing the #[%s] attribute; it must declare its name, '
                . 'supported countries and currencies.',
                $class,
                Provider::class,
            ));
        }

        return new self(
            $attribute->name,
            $attribute->capabilities,
            $attribute->countries,
            array_map(static fn (string $code): Currency => new Currency($code), $attribute->currencies),
        );
    }

    /**
     * Walks up the hierarchy, so a shared base class may declare the attribute for its subclasses.
     *
     * @param ReflectionClass<object> $class
     */
    private static function findAttribute(ReflectionClass $class): ?Provider
    {
        for ($current = $class; $current !== false; $current = $current->getParentClass()) {
            $attributes = $current->getAttributes(Provider::class);

            if ($attributes !== []) {
                return $attributes[0]->newInstance();
            }
        }

        return null;
    }

    public function supports(Capability $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    public function assesses(string $country): bool
    {
        return in_array($country, $this->countries, true);
    }

    public function accepts(Currency $currency): bool
    {
        foreach ($this->currencies as $supported) {
            if ($supported->equals($currency)) {
                return true;
            }
        }

        return false;
    }
}
