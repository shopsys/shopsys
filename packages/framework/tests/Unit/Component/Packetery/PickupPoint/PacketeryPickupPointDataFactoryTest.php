<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Packetery\PickupPoint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPointDataFactory;

final class PacketeryPickupPointDataFactoryTest extends TestCase
{
    public function testRegularOpeningHoursAreNormalizedAndClosedDaysAreOmitted(): void
    {
        $packeteryPickupPointData = (new PacketeryPickupPointDataFactory())->createFromFeedItem([
            'id' => 13789,
            'openingHours' => [
                'regular' => [
                    'monday' => '08:00–12:30, 13:30–17:30',
                    'tuesday' => '09:00-21:00',
                    'wednesday' => '',
                    'thursday' => [],
                    'friday' => 'invalid, 10:00–12:00',
                    'saturday' => '00:00–23:59',
                ],
            ],
        ]);

        $this->assertSame('13789', $packeteryPickupPointData->id);
        $this->assertSame(
            [
                1 => ['08:00-12:30', '13:30-17:30'],
                2 => ['09:00-21:00'],
                5 => ['10:00-12:00'],
                6 => ['00:00-23:59'],
            ],
            $packeteryPickupPointData->regularOpeningHours,
        );
        $this->assertNull($packeteryPickupPointData->upcomingOpeningHours);
        $this->assertNull($packeteryPickupPointData->upcomingOpeningHoursSince);
        $this->assertSame([], $packeteryPickupPointData->exceptionOpeningHours);
    }

    public function testUpcomingOpeningHoursAreParsedWithTheirStartDate(): void
    {
        $packeteryPickupPointData = (new PacketeryPickupPointDataFactory())->createFromFeedItem([
            'id' => '1',
            'openingHours' => [
                'regular' => ['monday' => '08:00–18:00'],
                'upcoming' => ['monday' => '08:00–17:00', 'sunday' => '10:00–12:00', 'startDate' => '2026-11-30'],
            ],
        ]);

        $this->assertSame([1 => ['08:00-17:00'], 7 => ['10:00-12:00']], $packeteryPickupPointData->upcomingOpeningHours);
        $this->assertSame('2026-11-30', $packeteryPickupPointData->upcomingOpeningHoursSince?->format('Y-m-d'));
    }

    /**
     * @return iterable<string, array{exceptions: mixed, expectedExceptionOpeningHours: array<string, string[]>}>
     */
    public static function getExceptionOpeningHoursData(): iterable
    {
        yield 'single exception converted from XML as an object' => [
            'exceptions' => ['exception' => ['date' => '2026-12-24', 'hours' => '08:00–12:00']],
            'expectedExceptionOpeningHours' => ['2026-12-24' => ['08:00-12:00']],
        ];

        yield 'more exceptions converted from XML as a list' => [
            'exceptions' => ['exception' => [
                ['date' => '2026-12-24', 'hours' => '08:00–12:00, 12:45–16:30'],
                ['date' => '2026-12-25', 'hours' => []],
                ['date' => '2026-12-26', 'hours' => ''],
            ]],
            'expectedExceptionOpeningHours' => [
                '2026-12-24' => ['08:00-12:00', '12:45-16:30'],
                '2026-12-25' => [],
                '2026-12-26' => [],
            ],
        ];

        yield 'plain list as documented' => [
            'exceptions' => [['date' => '2026-12-31', 'hours' => '12:00–18:00']],
            'expectedExceptionOpeningHours' => ['2026-12-31' => ['12:00-18:00']],
        ];

        yield 'exception without a valid date is skipped' => [
            'exceptions' => ['exception' => [['date' => 'tomorrow', 'hours' => '12:00–18:00']]],
            'expectedExceptionOpeningHours' => [],
        ];
    }

    /**
     * @param array<string, string[]> $expectedExceptionOpeningHours
     */
    #[DataProvider('getExceptionOpeningHoursData')]
    public function testExceptionOpeningHoursAreIndexedByDateAndEmptyHoursMeanClosed(
        mixed $exceptions,
        array $expectedExceptionOpeningHours,
    ): void {
        $packeteryPickupPointData = (new PacketeryPickupPointDataFactory())->createFromFeedItem([
            'id' => '1',
            'openingHours' => ['regular' => [], 'exceptions' => $exceptions],
        ]);

        $this->assertSame($expectedExceptionOpeningHours, $packeteryPickupPointData->exceptionOpeningHours);
    }
}
