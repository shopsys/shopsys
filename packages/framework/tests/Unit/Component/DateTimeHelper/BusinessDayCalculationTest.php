<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\DateTimeHelper;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\BusinessDayCalculation;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;

class BusinessDayCalculationTest extends TestCase
{
    /**
     * @param string[] $publicHolidayDates
     */
    #[DataProvider('closestBusinessDayDataProvider')]
    public function testGetClosestBusinessDay(
        string $inputDate,
        array $publicHolidayDates,
        string $expectedDate,
    ): void {
        $publicHolidays = array_map(
            static fn (string $date) => new DateTimeImmutable($date),
            $publicHolidayDates,
        );

        $calculation = $this->createCalculation($publicHolidays);
        $closestBusinessDay = $calculation->getClosestBusinessDay(new DateTimeImmutable($inputDate), Domain::FIRST_DOMAIN_ID);

        $this->assertEquals($expectedDate, $closestBusinessDay->format('Y-m-d'));
    }

    /**
     * @return iterable<string, array{inputDate: string, publicHolidayDates: string[], expectedDate: string}>
     */
    public static function closestBusinessDayDataProvider(): iterable
    {
        yield 'input business Monday remains unchanged' => [
            'inputDate' => '2024-01-15', // Monday
            'publicHolidayDates' => [],
            'expectedDate' => '2024-01-15',
        ];

        yield 'input Saturday returns Monday' => [
            'inputDate' => '2024-01-06', // Saturday
            'publicHolidayDates' => [],
            'expectedDate' => '2024-01-08',
        ];

        yield 'input Sunday returns Monday' => [
            'inputDate' => '2024-01-07', // Sunday
            'publicHolidayDates' => [],
            'expectedDate' => '2024-01-08',
        ];

        yield 'input Monday holiday returns Tuesday' => [
            'inputDate' => '2024-01-15', // Monday
            'publicHolidayDates' => ['2024-01-15'],
            'expectedDate' => '2024-01-16',
        ];

        yield 'input Friday holiday returns next Monday' => [
            'inputDate' => '2024-01-05', // Friday
            'publicHolidayDates' => ['2024-01-05'],
            'expectedDate' => '2024-01-08',
        ];

        yield 'input Friday holiday + weekend + Monday holiday returns next Tuesday' => [
            'inputDate' => '2024-01-05',
            'publicHolidayDates' => ['2024-01-05', '2024-01-08'],
            'expectedDate' => '2024-01-09',
        ];

        yield 'two consecutive weekday holidays return next Wednesday' => [
            'inputDate' => '2024-01-15', // Monday
            'publicHolidayDates' => ['2024-01-15', '2024-01-16'],
            'expectedDate' => '2024-01-17',
        ];
    }

    /**
     * @param \DateTimeImmutable[] $publicHolidays
     */
    private function createCalculation(array $publicHolidays): BusinessDayCalculation
    {
        $closedDayFacade = $this->createStub(ClosedDayFacade::class);
        $closedDayFacade->method('getPublicHolidays')
            ->willReturn($publicHolidays);

        return new BusinessDayCalculation($closedDayFacade);
    }
}
