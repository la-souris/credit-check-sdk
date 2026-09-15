<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

/**
 * The lifecycle state of a submitted check.
 */
enum CheckStatus: string
{
    case Submitted = 'submitted';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Unknown = 'unknown';

    public function isComplete(): bool
    {
        return $this === self::Completed;
    }
}
