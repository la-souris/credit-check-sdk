<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

/**
 * The references of the checks whose result changed since a given moment.
 *
 * Only the references are returned: what changed is read by polling
 * {@see \LaSouris\CreditCheck\Sdk\Provider\CreditChecker::getResult()} for each one, because
 * bureaus report the change feed as a list of identifiers, not as decisions.
 */
final readonly class ChangedChecksResponse extends Response
{
    /**
     * @param list<string>            $references
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        string $provider,
        public array $references = [],
        array $raw = [],
    ) {
        parent::__construct($provider, $raw);
    }
}
