<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Spatie\OpeningHours\OpeningHours as SpatieOpeningHours;
use Symfony\Contracts\Service\ResetInterface;

class StoreOpeningHoursProvider implements ResetInterface
{
    protected const DAY_NUMBERS_TO_ENGLISH_NAMES_MAP = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    /**
     * @var \Spatie\OpeningHours\OpeningHours[]
     */
    protected array $openingHoursSetting = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     */
    public function __construct(
        protected readonly ClosedDayFacade $closedDayFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Spatie\OpeningHours\OpeningHours
     */
    public function getOpeningHoursSetting(Store $store): SpatieOpeningHours
    {
        $storeId = $store->getId();

        if (array_key_exists($storeId, $this->openingHoursSetting) === false) {
            $this->openingHoursSetting[$storeId] = SpatieOpeningHours::create([
                ...$this->getWeekSetting($store),
                'exceptions' => $this->getExceptions($store),
            ]);
        }

        return $this->openingHoursSetting[$storeId];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return array
     */
    protected function getWeekSetting(Store $store): array
    {
        $weekSetting = [];

        foreach ($store->getOpeningHours() as $openingHour) {
            $dayOfWeekName = $this->getEnglishDayNameFromDayNumber($openingHour->getDayOfWeek());

            foreach ($openingHour->getOpeningHoursRanges() as $openingHoursRange) {
                $weekSetting[$dayOfWeekName][] = $this->formatOpeningHours(
                    $openingHoursRange->getOpeningTime(),
                    $openingHoursRange->getClosingTime(),
                );
            }
        }

        return $weekSetting;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursData
     * @return \Spatie\OpeningHours\OpeningHours
     */
    public function getOpeningHoursSettingFromData(array $openingHoursData): SpatieOpeningHours
    {
        return SpatieOpeningHours::create($this->getWeekSettingFromData($openingHoursData));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return array[][]
     */
    protected function getExceptions(Store $store): array
    {
        $exceptions = [];
        $closedDays = $this->closedDayFacade->getFollowingWeekClosedDaysNotExcludedForStore($store);

        foreach ($closedDays as $closedDay) {
            $exceptions[$closedDay->getDate()->format('Y-m-d')] = [];
        }

        return $exceptions;
    }

    /**
     * @param int $dayNumber
     * @return string
     */
    protected function getEnglishDayNameFromDayNumber(int $dayNumber): string
    {
        return static::DAY_NUMBERS_TO_ENGLISH_NAMES_MAP[$dayNumber] ?? throw new InvalidArgumentException(sprintf('Day number "%s" is not valid. (expected a value in range 1-7)', $dayNumber));
    }

    public function reset(): void
    {
        $this->openingHoursSetting = [];
    }

    /**
     * @param string $openingTime
     * @param string $closingTime
     * @return string
     */
    protected function formatOpeningHours(string $openingTime, string $closingTime): string
    {
        return $openingTime . '-' . $closingTime;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursData
     * @return array{
     *     monday: string[],
     *     tuesday: string[],
     *     wednesday: string[],
     *     thursday: string[],
     *     friday: string[],
     *     saturday: string[],
     *     sunday: string[],
     * }
     */
    protected function getWeekSettingFromData(array $openingHoursData): array
    {
        $weekSetting = [];

        foreach ($openingHoursData as $openingHourData) {
            $dayOfWeekName = $this->getEnglishDayNameFromDayNumber($openingHourData->dayOfWeek);

            foreach ($openingHourData->openingHoursRanges as $openingHoursRange) {
                $weekSetting[$dayOfWeekName][] = $this->formatOpeningHours(
                    $openingHoursRange->openingTime,
                    $openingHoursRange->closingTime,
                );
            }
        }

        return $weekSetting;
    }
}
