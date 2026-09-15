<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck;

/**
 * The channel the application originated from.
 */
enum SalesChannel: string
{
    case Unknown = 'unknown';
    case Internet = 'internet';
    case Dealership = 'dealership';
}
