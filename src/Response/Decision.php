<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Response;

/**
 * The normalised outcome of a check, independent of the vocabulary the bureau used.
 *
 * Only Approved and Rejected are final; Referred means a human at the bureau still has to
 * look at it, and Pending means the evaluation has not finished. Poll getResult() until
 * {@see isFinal()}.
 */
enum Decision: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Referred = 'referred';
    case Rejected = 'rejected';

    /**
     * Derive the decision from a bureau's traffic light.
     *
     * A light only means something once the check has completed: a green light on a check
     * still in progress is an interim reading, not an approval, so it stays Pending.
     */
    public static function fromTrafficLight(?TrafficLight $light, bool $completed): self
    {
        if (!$completed || $light === null) {
            return self::Pending;
        }

        return match ($light) {
            TrafficLight::Green => self::Approved,
            TrafficLight::Orange => self::Referred,
            TrafficLight::Red => self::Rejected,
        };
    }

    /**
     * Whether the outcome can still change. Referred and Pending can; the other two cannot.
     */
    public function isFinal(): bool
    {
        return $this === self::Approved || $this === self::Rejected;
    }
}
