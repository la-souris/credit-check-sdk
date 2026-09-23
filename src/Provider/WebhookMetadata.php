<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Provider;

/**
 * Marker for a provider's own parsed webhook payload. A provider that receives callbacks defines
 * its own implementation (e.g. EDR's `Metadata`, carrying its `Ref`/`orderId`/`newStatus` fields)
 * and attaches an instance to the webhook-received event, so callers can read provider-specific
 * fields without re-parsing the raw request themselves.
 */
interface WebhookMetadata
{
}
