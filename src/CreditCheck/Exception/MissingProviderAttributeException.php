<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Exception;

/**
 * A CreditChecker implementation was inspected but carries no #[Provider] attribute, so its
 * name, reach and capabilities cannot be determined.
 */
final class MissingProviderAttributeException extends CreditCheckException
{
}
