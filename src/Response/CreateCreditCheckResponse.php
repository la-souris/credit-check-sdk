<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

/**
 * What a provider hands back the moment a check is submitted.
 *
 * Checks are asynchronous, so this carries no decision — only the reference to poll
 * {@see \LaSouris\CreditCheck\Sdk\Provider\CreditChecker::getResult()} with, and the name of
 * the provider that issued it, so a stored reference can be routed back to the right bureau.
 */
final readonly class CreateCreditCheckResponse extends Response
{
    /**
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        string $provider,
        public string $reference,
        public CheckStatus $status = CheckStatus::Submitted,
        array $raw = [],
    ) {
        parent::__construct($provider, $raw);
    }
}
