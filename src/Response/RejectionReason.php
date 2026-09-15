<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

/**
 * One reason a check was not approved, as the bureau's own code (e.g. "ShortageOfIncome").
 *
 * The codes are provider vocabulary — the SDK does not normalise them, because the set
 * differs per bureau and collapsing them would lose the detail callers need to explain the
 * outcome to an applicant.
 */
final readonly class RejectionReason
{
    public function __construct(
        public string $code,
    ) {
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
