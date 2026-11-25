<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Packetery\PickupPoint;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPoint;
use Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPointData;
use Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPointOpeningHoursProvider;

final class PacketeryPickupPointOpeningHoursProviderTest extends TestCase
{
    /**
     * @return iterable<string, array{date: string, expectedIsOpen: bool}>
     */
    public static function getIsOpenOnDateData(): iterable
    {
        yield 'regular weekday' => [
            'date' => '2026-11-26',
            'expectedIsOpen' => true,
        ]; // Thursday

        yield 'regular closed Sunday' => [
            'date' => '2026-11-29',
            'expectedIsOpen' => false,
        ];

        yield 'exception closes a regularly open day' => [
            'date' => '2026-11-27',
            'expectedIsOpen' => false,
        ]; // Friday

        yield 'exception opens a regularly closed day' => [
            'date' => '2026-11-28',
            'expectedIsOpen' => true,
        ]; // Saturday

        yield 'upcoming hours close a regularly open day since their start' => [
            'date' => '2026-11-30',
            'expectedIsOpen' => false,
        ]; // Monday

        yield 'upcoming hours open a regularly closed day since their start' => [
            'date' => '2026-12-13',
            'expectedIsOpen' => true,
        ]; // Sunday
    }

    #[DataProvider('getIsOpenOnDateData')]
    public function testIsOpenOnDate(string $date, bool $expectedIsOpen): void
    {
        $isOpen = (new PacketeryPickupPointOpeningHoursProvider())->isOpenOnDate(
            $this->createPacketeryPickupPoint(),
            new DateTimeImmutable($date),
        );

        $this->assertSame($expectedIsOpen, $isOpen);
    }

    /**
     * @return iterable<string, array{time: string, expectedIsOpen: bool}>
     */
    public static function getIsOpenAfterTimeOnDateData(): iterable
    {
        yield 'before the first range' => [
            'time' => '07:00',
            'expectedIsOpen' => true,
        ];

        yield 'during the lunch break' => [
            'time' => '12:30',
            'expectedIsOpen' => true,
        ];

        yield 'after the last range' => [
            'time' => '17:00',
            'expectedIsOpen' => false,
        ];

        yield 'exactly at the closing time' => [
            'time' => '16:00',
            'expectedIsOpen' => false,
        ];
    }

    #[DataProvider('getIsOpenAfterTimeOnDateData')]
    public function testIsOpenAfterTimeOnDate(string $time, bool $expectedIsOpen): void
    {
        $isOpen = (new PacketeryPickupPointOpeningHoursProvider())->isOpenAfterTimeOnDate(
            $this->createPacketeryPickupPoint(),
            new DateTimeImmutable('2026-11-26'), // Thursday
            $time,
        );

        $this->assertSame($expectedIsOpen, $isOpen);
    }

    private function createPacketeryPickupPoint(): PacketeryPickupPoint
    {
        $packeteryPickupPointData = new PacketeryPickupPointData();
        $packeteryPickupPointData->id = '1';
        $packeteryPickupPointData->regularOpeningHours = [
            1 => ['08:00-16:00'],
            2 => ['08:00-16:00'],
            3 => ['08:00-16:00'],
            4 => ['08:00-12:00', '13:00-16:00'],
            5 => ['08:00-16:00'],
        ];
        $packeteryPickupPointData->upcomingOpeningHours = [2 => ['08:00-16:00'], 7 => ['10:00-12:00']];
        $packeteryPickupPointData->upcomingOpeningHoursSince = new DateTimeImmutable('2026-11-30');
        $packeteryPickupPointData->exceptionOpeningHours = [
            '2026-11-27' => [],
            '2026-11-28' => ['10:00-12:00'],
        ];

        return new PacketeryPickupPoint($packeteryPickupPointData);
    }
}
