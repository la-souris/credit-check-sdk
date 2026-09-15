<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Support;

use LaSouris\CreditCheck\Sdk\CreditCheck\Exception\InvalidArgumentException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Minimal input guards for value-object invariants, shared across the SDK.
 */
final class Assert
{
    public static function length(string $value, int $min, int $max, string $field): string
    {
        $length = mb_strlen($value);

        if ($length < $min || $length > $max) {
            throw new InvalidArgumentException(
                sprintf('%s must be between %d and %d characters, got %d.', $field, $min, $max, $length),
            );
        }

        return $value;
    }

    public static function maxLength(string $value, int $max, string $field): string
    {
        return self::length($value, 0, $max, $field);
    }

    public static function notBlank(string $value, string $field): string
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException(sprintf('%s must not be blank.', $field));
        }

        return $value;
    }

    public static function email(string $value, string $field): string
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(sprintf('%s is not a valid e-mail address: "%s".', $field, $value));
        }

        return $value;
    }

    /**
     * Checks a parsed number against libphonenumber's metadata for its region — parsing alone
     * only proves the input was well-formed, not that the number can exist.
     */
    public static function validPhoneNumber(PhoneNumber $value, string $field): PhoneNumber
    {
        $util = PhoneNumberUtil::getInstance();

        if (!$util->isValidNumber($value)) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a valid phone number: "%s".',
                $field,
                $util->format($value, PhoneNumberFormat::E164),
            ));
        }

        return $value;
    }

    public static function positive(int $value, string $field): int
    {
        if ($value <= 0) {
            throw new InvalidArgumentException(sprintf('%s must be greater than zero, got %d.', $field, $value));
        }

        return $value;
    }

    /**
     * @param array<mixed> $value
     *
     * @return array<mixed>
     */
    public static function notEmpty(array $value, string $field): array
    {
        if ($value === []) {
            throw new InvalidArgumentException(sprintf('%s must contain at least one item.', $field));
        }

        return $value;
    }

    /**
     * @param array<mixed> $value
     *
     * @return list<string>
     */
    public static function allCountryCodes(array $value, string $field): array
    {
        foreach ($value as $code) {
            if (!is_string($code) || preg_match('/^[A-Z]{2}$/', $code) !== 1) {
                throw new InvalidArgumentException(
                    sprintf('%s must contain ISO 3166-1 alpha-2 codes such as "NL".', $field),
                );
            }
        }

        return array_values($value);
    }

    /**
     * @param array<mixed> $value
     *
     * @return list<string>
     */
    public static function allCurrencyCodes(array $value, string $field): array
    {
        foreach ($value as $code) {
            if (!is_string($code) || preg_match('/^[A-Z]{3}$/', $code) !== 1) {
                throw new InvalidArgumentException(
                    sprintf('%s must contain ISO 4217 codes such as "EUR".', $field),
                );
            }
        }

        return array_values($value);
    }

    /**
     * @param array<mixed> $value
     * @param class-string $type
     *
     * @return array<mixed>
     */
    public static function allInstanceOf(array $value, string $type, string $field): array
    {
        foreach ($value as $item) {
            if (!$item instanceof $type) {
                throw new InvalidArgumentException(
                    sprintf('%s must contain only %s instances.', $field, $type),
                );
            }
        }

        return $value;
    }
}
