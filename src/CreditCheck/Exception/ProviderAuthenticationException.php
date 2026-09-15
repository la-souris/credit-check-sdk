<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

/**
 * HTTP 401 / 403 — the provider rejected our credentials or token.
 */
class ProviderAuthenticationException extends ProviderException
{
}
