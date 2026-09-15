<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\Tests\Unit\Response;

use LaSouris\CreditCheck\Sdk\Response\Decision;
use LaSouris\CreditCheck\Sdk\Response\TrafficLight;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DecisionTest extends TestCase
{
    #[DataProvider('trafficLightProvider')]
    public function testFromTrafficLight(?TrafficLight $light, bool $completed, Decision $expected): void
    {
        self::assertSame($expected, Decision::fromTrafficLight($light, $completed));
    }

    /**
     * @return iterable<string, array{?TrafficLight, bool, Decision}>
     */
    public static function trafficLightProvider(): iterable
    {
        yield 'completed green -> approved' => [TrafficLight::Green, true, Decision::Approved];
        yield 'completed red -> rejected' => [TrafficLight::Red, true, Decision::Rejected];
        yield 'completed orange -> referred' => [TrafficLight::Orange, true, Decision::Referred];
        yield 'green but not completed -> pending' => [TrafficLight::Green, false, Decision::Pending];
        yield 'no light -> pending' => [null, true, Decision::Pending];
    }

    public function testIsFinal(): void
    {
        self::assertTrue(Decision::Approved->isFinal());
        self::assertTrue(Decision::Rejected->isFinal());
        self::assertFalse(Decision::Referred->isFinal());
        self::assertFalse(Decision::Pending->isFinal());
    }
}
