<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

/**
 * A provider's answer to one contract call: who answered, and what they actually sent.
 *
 * Every {@see \LaSouris\CreditCheck\Sdk\Provider\CreditChecker} method returns a subclass of
 * this, so the provider identity and the untouched response body are available uniformly — not
 * only on the calls whose payload happens to carry them. Reading a provider-specific field the
 * SDK does not model is then the same gesture on every call, rather than dropping into the
 * provider package.
 *
 * The normalised payload is not held here: each use case declares its own fields on its own
 * subclass, so callers read `$response->reference` rather than unwrapping an untyped envelope.
 */
abstract readonly class Response
{
    /**
     * @param string                  $provider Machine name of the provider that answered, e.g. "edr".
     * @param array<array-key, mixed> $raw      The provider's response as decoded, untouched.
     */
    public function __construct(
        public string $provider,
        public array $raw = [],
    ) {
    }
}
