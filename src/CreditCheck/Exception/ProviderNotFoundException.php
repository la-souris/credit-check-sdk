<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

/**
 * HTTP 404 — the referenced check/order does not exist at the provider.
 */
class ProviderNotFoundException extends ProviderException
{
}
