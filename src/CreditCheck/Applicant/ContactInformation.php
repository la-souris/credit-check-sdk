<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;

use LaSouris\CreditCheck\Sdk\Support\Assert;
use libphonenumber\PhoneNumber;

/**
 * How to reach a person during the check.
 *
 * The mobile number is libphonenumber's own {@see PhoneNumber}, parsed by the caller with
 * `PhoneNumberUtil::getInstance()->parse($input, $region)` and validated here. Rendering it —
 * E.164 for a wire payload, national notation for a UI — is `PhoneNumberUtil::format()`'s job,
 * so no formatting decision is baked into the domain.
 */
final readonly class ContactInformation
{
    public function __construct(
        public string $email,
        public PhoneNumber $mobileNumber,
    ) {
        Assert::email($email, 'email');
        Assert::validPhoneNumber($mobileNumber, 'mobileNumber');
    }
}
