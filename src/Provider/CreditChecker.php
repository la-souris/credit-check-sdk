<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Provider;

use DateTimeInterface;
use LaSouris\CreditCheck\Sdk\Request\CreateCreditCheck;
use LaSouris\CreditCheck\Sdk\Response\ChangedChecksResponse;
use LaSouris\CreditCheck\Sdk\Response\CreateCreditCheckResponse;
use LaSouris\CreditCheck\Sdk\Response\GetCreditCheckResponse;

/**
 * The provider-agnostic contract every credit-check bureau implements.
 *
 * Credit checks are asynchronous: submitCheck() hands the applicant(s) over and returns a
 * reference immediately, while the bureau evaluates in the background. Poll getResult() with
 * that reference to read the decision once it is ready, or use getChangedChecksSince() to
 * discover which checks changed since a given moment.
 *
 * The contract is behaviour only. What a provider *is* — its name, the countries it assesses,
 * the currencies it accepts and the operations it can perform — is declared with the
 * {@see Provider} attribute on the implementing class and read back through
 * {@see ProviderCapabilities::of()}. Operations a provider does not advertise throw
 * {@see \LaSouris\CreditCheck\Sdk\CreditCheck\Exception\UnsupportedOperationException}.
 */
interface CreditChecker
{
    /**
     * Submit a credit check for one or more applicants; returns the created reference.
     */
    public function submitCheck(CreateCreditCheck $request): CreateCreditCheckResponse;

    /**
     * Fetch the current decision/result of a previously submitted check.
     *
     * @throws \LaSouris\CreditCheck\Sdk\CreditCheck\Exception\UnsupportedOperationException when unsupported.
     */
    public function getResult(string $reference): GetCreditCheckResponse;

    /**
     * List the references of checks whose result changed since the given moment.
     *
     * @throws \LaSouris\CreditCheck\Sdk\CreditCheck\Exception\UnsupportedOperationException when unsupported.
     */
    public function getChangedChecksSince(DateTimeInterface $since): ChangedChecksResponse;
}
