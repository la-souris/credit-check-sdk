<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

/**
 * The colour a bureau assigns to a check or a verification step: green (pass),
 * orange (needs review) or red (fail).
 */
enum TrafficLight: string
{
    case Green = 'green';
    case Orange = 'orange';
    case Red = 'red';
}
