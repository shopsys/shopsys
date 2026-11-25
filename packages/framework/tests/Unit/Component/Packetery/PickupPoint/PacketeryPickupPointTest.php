<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Packetery\PickupPoint;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPoint;

final class PacketeryPickupPointTest extends TestCase
{
    /**
     * @return iterable<string, array{date: string, expectedIsOpen: bool}>
     */
    public static function getIsOpenOnDateData(): iterable
    {
        yield 'regular Thursday' => [
            'date' => '2026-11-26',
            'expectedIsOpen' => true,
        ];

        yield 'regularly closed Sunday' => [
            'date' => '2026-11-29',
            'expectedIsOpen' => false,
        ];

        yield 'exception closes a regularly open Friday' => [
            'date' => '2026-11-27',
            'expectedIsOpen' => false,
        ];

        yield 'exception opens a regularly closed Saturday' => [
            'date' => '2026-11-28',
            'expectedIsOpen' => true,
        ];

        yield 'upcoming hours close a regularly open Monday since their start' => [
            'date' => '2026-11-30',
            'expectedIsOpen' => false,
        ];

        yield 'upcoming hours open a regularly closed Sunday since their start' => [
            'date' => '2026-12-13',
            'expectedIsOpen' => true,
        ];
    }

    #[DataProvider('getIsOpenOnDateData')]
    public function testIsOpenOnDate(string $date, bool $expectedIsOpen): void
    {
        $isOpen = $this->createPacketeryPickupPoint()->isOpenOnDate(new DateTimeImmutable($date));

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
    public function testIsOpenAfterTimeOnThursday(string $time, bool $expectedIsOpen): void
    {
        $isOpen = $this->createPacketeryPickupPoint()->isOpenAfterTimeOnDate(new DateTimeImmutable('2026-11-26'), $time);

        $this->assertSame($expectedIsOpen, $isOpen);
    }

    private function createPacketeryPickupPoint(): PacketeryPickupPoint
    {
        return new PacketeryPickupPoint(
            '1',
            [
                1 => ['08:00-16:00'],
                2 => ['08:00-16:00'],
                3 => ['08:00-16:00'],
                4 => ['08:00-12:00', '13:00-16:00'],
                5 => ['08:00-16:00'],
            ],
            [2 => ['08:00-16:00'], 7 => ['10:00-12:00']],
            '2026-11-30',
            [
                '2026-11-27' => [],
                '2026-11-28' => ['10:00-12:00'],
            ],
        );
    }
}
