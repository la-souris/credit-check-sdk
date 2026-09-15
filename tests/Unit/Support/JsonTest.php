<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Unit\Support;

use LaSouris\CreditCheck\Sdk\Support\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    public function testFilterDropsNullAndEmptyArrays(): void
    {
        $filtered = Json::filter([
            'keep' => 'value',
            'zero' => 0,
            'false' => false,
            'null' => null,
            'empty' => [],
        ]);

        self::assertSame(['keep' => 'value', 'zero' => 0, 'false' => false], $filtered);
    }
}
