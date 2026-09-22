# Credit Check SDK

The provider-agnostic core of the credit-check monorepo: the `CreditChecker` contract, a small
applicant/affordability domain modelled as typed value objects, a normalised result model, an
exception hierarchy, and small support helpers. Framework-free — no HTTP client, no framework.

The domain deliberately models only what every bureau needs. Supplier-specific vocabulary and
supplier-specific fields belong in the provider package, behind its own mapper — see
[`credit-check-edr`](../edr) for how that is done.

## The contract

Every provider implements `LaSouris\CreditCheck\Sdk\Provider\CreditChecker`:

```php
interface CreditChecker
{
    public function submitCheck(CreateCreditCheck $request): CreateCreditCheckResponse;

    public function getResult(string $reference): GetCreditCheckResponse;

    public function getChangedChecksSince(DateTimeInterface $since): ChangedChecksResponse;
}
```

Each call answers with its own flattened response class — no shared envelope, no `->data`
indirection. Every response still carries the same two fields plus its own named ones:

```php
$response = $checker->getResult($reference);

$response->reference;   // and the rest of GetCreditCheckResponse's own fields, read directly
$response->provider;    // machine name of the bureau that answered, e.g. 'edr'
$response->raw;         // that bureau's response body, decoded but otherwise untouched
```

`$provider` and `$raw` being on every response class means provider identity and the untouched
body are available everywhere, not only on the calls whose payload happens to carry them —
reading a field the SDK does not model is the same gesture on every call, with no drop into the
provider package.

Credit checks are **asynchronous**: `submitCheck()` hands the applicant(s) over and returns a
reference immediately while the bureau evaluates in the background; poll `getResult()` with that
reference, or use `getChangedChecksSince()` to discover which checks changed since a given moment.

Three methods is the whole contract. What a provider *is* — its name, the countries it assesses,
the currencies it accepts and the operations it can perform — is not behaviour, so it is declared
once with the `#[Provider]` attribute rather than implemented as four more methods:

```php
#[Provider(
    name: 'edr',
    countries: ['NL'],                          // ISO 3166-1 alpha-2
    currencies: ['EUR'],                        // ISO 4217
    capabilities: [Capability::CREATE_CHECK, Capability::GET_RESULT, Capability::LIST_CHANGED],
)]
final class EdrCreditChecker implements CreditChecker
{
    use GuardsProviderReach;                    // assertSupportedCountry() / assertSupportedCurrency()

    // submitCheck / getResult / getChangedChecksSince
}
```

`capabilities` defaults to `[Capability::CREATE_CHECK, Capability::GET_RESULT]`. Operations a
provider does not advertise throw `UnsupportedOperationException`.

Read it back with `ProviderCapabilities::of()`, which takes an instance **or a bare class name** —
so discovery needs no construction and no credentials, which is how the framework packages build
their registries. Results are cached per class, so the reflection happens once per process:

```php
$reach = ProviderCapabilities::of(EdrCreditChecker::class);

$reach->name;                                   // 'edr'
$reach->supports(Capability::LIST_CHANGED);     // change-feed available?
$reach->assesses('NL');                         // handles this country?
$reach->accepts(new Money\Currency('EUR'));     // handles this currency?
```

A class without the attribute throws `MissingProviderAttributeException`. The attribute is
inherited, so a shared base class may declare it for its subclasses.

## The domain

- **Request** (`Request\CreateCreditCheck`): a `reference` string (not promoted to a public
  property — pass it positionally, don't rely on `$request->reference`), one or more
  `CreditCheck\Applicant`s passed **variadically** (not as an array), and a `CreditCheck\Subject`
  (financed amount + term + product `label`/`variant`). `primaryApplicant()` returns the first one.
- **Applicant** (`CreditCheck\Applicant`): wraps two `Applicant\Person`s — `person` and `partner`,
  both required, neither nullable.
- **Person** (`Applicant\Person`): identity (`initials`, `firstName`, `surname`, `gender`,
  `dateOfBirth`), a `ContactInformation` (e-mail + phone) and an `Address`, plus `displayName()`.
- **Address** (`Applicant\Address`): `country` as an ISO 3166-1 alpha-2 string, a single
  `houseNumber` string so suffixed numbers ("12A", "3-bis") survive intact, `street`,
  `postalCode`, `city`, and a plain `occupants` count.

Monetary amounts use the [`moneyphp/money`](https://github.com/moneyphp/money) library
(`Money\Money` / `Money\Currency`) — integer minor units, no float rounding errors.

Phone numbers are [`libphonenumber`](https://github.com/giggsey/libphonenumber-for-php)'s own
`libphonenumber\PhoneNumber`, not a wrapper — parse once and pass it around:

```php
$util   = PhoneNumberUtil::getInstance();
$number = $util->parse('06 12345678', 'NL');   // NumberParseException if malformed

new ContactInformation('jan@example.com', $number);
// -> InvalidArgumentException if the number is well-formed but cannot exist

$util->format($number, PhoneNumberFormat::E164);      // '+31612345678' — for a wire payload
$util->format($number, PhoneNumberFormat::NATIONAL);  // '06 12345678'  — for a UI
```

`ContactInformation` validates the number against libphonenumber's metadata (parsing alone only
proves the input was well-formed) but stores it as-is. No formatting decision is baked into the
domain — rendering is the caller's or the provider's call.

`Support\Json::filter()` drops `null` and `[]` values from an array — a small helper for
providers serialising a payload where optional fields should be omitted rather than sent as
`null`.

Everything a particular bureau needs beyond this — income and affordability figures, marital
status, household flags, identification — lives in that bureau's provider package, behind its own
mapper. See [`credit-check-edr`](../edr) for how that is done.

## Building a request

```php
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;
use LaSouris\CreditCheck\Sdk\CreditCheck\Applicant\{Address, ContactInformation, Gender, Person};
use LaSouris\CreditCheck\Sdk\CreditCheck\{SalesChannel, Subject};
use LaSouris\CreditCheck\Sdk\Request\CreateCreditCheckRequest;
use libphonenumber\PhoneNumberUtil;
use Money\Money;
// Money::EUR(cents) — the MoneyFactory trait is composed onto Money

$person = new Person(
    initials: 'J.',
    firstName: 'Jan',
    surname: 'de Vries',
    gender: Gender::Male,
    dateOfBirth: new DateTimeImmutable('1990-05-01'),
    contactInformation: new ContactInformation(
        email: 'jan@example.com',
        mobileNumber: PhoneNumberUtil::getInstance()->parse('06 12345678', 'NL'),
    ),
    address: new Address(
        country: 'NL',
        houseNumber: '12A',                     // one string; suffixed numbers survive intact
        street: 'Kerkstraat',
        postalCode: '1011AA',
        city: 'Amsterdam',
        occupants: 2,
    ),
);

$applicant = new Applicant(person: $person, partner: $partner);   // both required today, neither nullable

$request = new CreateCreditCheckRequest(
    'ORDER-1001',                                // reference — positional; not constructor-promoted, but public and readable back as $request->reference
    new Subject(
        label: 'Tesla',
        variant: 'Model 3',
        amount: Money::EUR(4500000),            // €45,000.00 financed
        termInMonths: 60,
        startDate: new DateTimeImmutable('+1 week'),
        endDate: new DateTimeImmutable('+5 years'),
    ),
    SalesChannel::Internet,
    $applicant,                                   // ...$applicants is variadic — pass positionally, not as an array
);
```

Constructing a value object with input that violates its invariants throws
`Exception\InvalidArgumentException`.

## Reading a result

`submitCheck()` answers with a `Response\CreateCreditCheckResponse` — the provider name, the
reference to poll with, and a `Response\CheckStatus`. No decision: the bureau has not evaluated
anything yet.

`getResult()` answers with a `Response\GetCreditCheckResponse`:

```php
$response = $checker->getResult($receipt->reference);

$response->decision;            // Response\Decision: Pending | Approved | Referred | Rejected
$response->status;              // Response\CheckStatus: Submitted | InProgress | Completed | Unknown
$response->trafficLight;        // ?Response\TrafficLight: Green | Orange | Red
$response->expendableIncome;    // ?Money\Money — what the household can carry per month
$response->rejectionReasons;    // list<Response\RejectionReason>, each a provider code
$response->applicantResults;    // list<Response\ApplicantResult>, in submitted order
$response->startedAt;           // ?DateTimeImmutable
$response->completedAt;         // ?DateTimeImmutable
$response->raw;                 // the provider's decoded response, untouched

$response->isApproved();
$response->isRejected();
$response->isFinal();           // stop polling when true
```

Everything past `status` is optional, because bureaus differ in what they report and in how much
is available before a check completes. `$raw` keeps the provider's own response intact, so a
field the SDK does not model is still reachable without dropping to the provider package.

`Decision` is derived from the bureau's traffic light with
`Decision::fromTrafficLight($light, $completed)`: green/orange/red map to Approved/Referred/
Rejected, but **only once the check has completed** — a green light on a check still in progress
is an interim reading, so it stays `Pending`. Only `Approved` and `Rejected` are `isFinal()`;
`Referred` means someone at the bureau still has to look at it.

`RejectionReason` wraps the bureau's own code (e.g. `ShortageOfIncome`) and is deliberately not
normalised — the sets differ per provider, and collapsing them would lose the detail needed to
explain an outcome to an applicant.

`ApplicantResult` carries the per-person half of a decision (traffic light, monthly capacity);
both fields are optional, since a bureau may screen a household jointly or finish screening
before the affordability figures land.

## Provider discovery

`ProviderCapabilities::of()` snapshots a provider for routing or UI, from an instance or a class
name:

```php
$reach = ProviderCapabilities::of($checker);

$reach->supports(Capability::LIST_CHANGED);        // change-feed available?
$reach->assesses('NL');                            // handles this country?
$reach->accepts(new Money\Currency('EUR'));        // handles this currency?
```

Providers enforce their own reach with the `GuardsProviderReach` trait, which reads the same
attribute, so out-of-range input is rejected with a `ProviderValidationException` before any
network call:

```php
public function submitCheck(CreateCreditCheck $request): CreditCheckReceipt
{
    $this->assertSupportedCurrency($request->subject->amount->getCurrency());
    $this->assertSupportedCountry($request->primaryApplicant()->address->country);
    // ...
}
```

## Exceptions

Everything the SDK or a provider throws extends `Exception\CreditCheckException`:

| Exception | When |
|---|---|
| `InvalidArgumentException` | A value object was built with input that breaks its invariants (local, before any call). |
| `MissingProviderAttributeException` | A `CreditChecker` class was inspected but carries no `#[Provider]` attribute. |
| `UnsupportedOperationException` | An operation was invoked that the provider does not advertise in its `#[Provider]` attribute. |
| `ProviderException` | Base for anything returned by, or while reaching, a provider backend. Carries `httpStatus`, `errors`, `body`, `providerCode`. |
| `ProviderAuthenticationException` | 401 / 403 — credentials or token rejected. |
| `ProviderValidationException` | 400 / 409 / 422 — the request failed the provider's rules. |
| `ProviderNotFoundException` | 404 — the referenced check does not exist. |
| `ProviderRateLimitException` | 429 — back off and retry later. |
| `ProviderTransientException` | 5xx — transient backend failure; retrying may succeed. |

Providers translate their own transport/HTTP failures into this hierarchy
(`ProviderException::fromHttpStatus()`), so callers handle credit-check errors uniformly
regardless of supplier.

## Providers

This package is useless on its own — pair it with a provider implementation such as
`la-souris/credit-check-edr`, and with `la-souris/credit-check-laravel` or
`la-souris/credit-check-symfony` for framework wiring.
