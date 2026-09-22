<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Fake;

use DateTimeInterface;
use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\ProviderNotFoundException;
use LaSouris\CreditCheck\Sdk\Provider\Capability;
use LaSouris\CreditCheck\Sdk\Provider\CreditChecker;
use LaSouris\CreditCheck\Sdk\Provider\Provider;
use LaSouris\CreditCheck\Sdk\Request\CreateCreditCheckRequest;
use LaSouris\CreditCheck\Sdk\Response\ChangedChecksResponse;
use LaSouris\CreditCheck\Sdk\Response\CheckStatus;
use LaSouris\CreditCheck\Sdk\Response\CreateCreditCheckResponse;
use LaSouris\CreditCheck\Sdk\Response\Decision;
use LaSouris\CreditCheck\Sdk\Response\GetCreditCheckResponse;
use LaSouris\CreditCheck\Sdk\Response\TrafficLight;
use LaSouris\CreditCheck\Sdk\Support\GuardsProviderReach;

/**
 * A minimal in-memory CreditChecker for exercising the contract, capability and
 * country/currency-guard behaviour without a real provider.
 */
#[Provider(
    name: 'fake',
    countries: ['NL'],
    currencies: ['EUR'],
    capabilities: [Capability::CREATE_CHECK, Capability::GET_RESULT, Capability::LIST_CHANGED],
)]
final class FakeCreditChecker implements CreditChecker
{
    use GuardsProviderReach;

    /** @var array<string, CreateCreditCheckRequest> */
    private array $submitted = [];

    private int $counter = 0;

    public function submitCheck(CreateCreditCheckRequest $request): CreateCreditCheckResponse
    {
        $this->assertSupportedMoney($request->subject->amount);
        $this->assertSupportedCountry($request->primaryApplicant()->person->address->country);

        $reference = 'CHECK-' . (++$this->counter);
        $this->submitted[$reference] = $request;

        return new CreateCreditCheckResponse(
            provider: $this->reach()->name,
            reference: $reference,
            status: CheckStatus::Submitted,
        );
    }

    public function getResult(string $reference): GetCreditCheckResponse
    {
        if (!isset($this->submitted[$reference])) {
            throw new ProviderNotFoundException(sprintf('Unknown check "%s".', $reference), 404);
        }

        return new GetCreditCheckResponse(
            provider: $this->reach()->name,
            reference: $reference,
            decision: Decision::Approved,
            status: CheckStatus::Completed,
            trafficLight: TrafficLight::Green,
        );
    }

    public function getChangedChecksSince(DateTimeInterface $since): ChangedChecksResponse
    {
        return new ChangedChecksResponse(
            provider: $this->reach()->name,
            references: array_keys($this->submitted),
        );
    }
}
