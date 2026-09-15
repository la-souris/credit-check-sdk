<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Provider;

/**
 * The operations a provider may or may not support. Query CreditChecker::supports()
 * before invoking an operation that is not universally available.
 */
enum Capability: string
{
    case CREATE_CHECK = 'create_check';
    case LIST_CHANGED = 'list_changed';
    case GET_RESULT = 'get_result';
}
