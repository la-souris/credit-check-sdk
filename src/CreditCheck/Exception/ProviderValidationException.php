<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

/**
 * HTTP 400 / 409 / 422 — the submitted credit-check request did not satisfy the provider's rules.
 */
class ProviderValidationException extends ProviderException
{
}
