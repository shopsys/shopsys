<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store\OpeningHours;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;
use Shopsys\FrameworkBundle\Model\Store\Store;

class StoreOpeningHoursApiProvider
{
    public function __construct(
        protected readonly StoreOpeningHoursProvider $storeOpeningHoursProvider,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly OpeningHoursWithDateDataFactory $openingHoursWithDateDataFactory,
        protected readonly OpeningHoursRangeDataFactory $openingHoursRangeDataFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Store\OpeningHours\OpeningHoursWithDateData[]
     */
    public function getFollowingWeekOpeningHours(Store $store): array
    {
        $openingHoursData = [];

        $today = $this->clock->now()
            ->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()))
            ->setTime(0, 0, 0);

        for ($i = 0; $i <= 6; $i++) {
            $day = $today->modify("+{$i} days");
            $openingHoursData[] = $this->getOpeningHoursDataForDate($day, $store);
        }

        return $openingHoursData;
    }

    protected function getOpeningHoursDataForDate(DateTimeImmutable $date, Store $store): OpeningHoursWithDateData
    {
        $openingHoursForDate = $this->storeOpeningHoursProvider->getOpeningHoursSetting($store)->forDate($date);

        $openingHoursData = $this->openingHoursWithDateDataFactory->createForDate($date);

        if ($openingHoursForDate->isEmpty()) {
            return $openingHoursData;
        }

        /** @var \Spatie\OpeningHours\TimeRange $openingHour */
        foreach ($openingHoursForDate->getIterator() as $openingHour) {
            $openingHoursData->openingHoursRanges[] = $this->openingHoursRangeDataFactory->create($openingHour->start()->format(), $openingHour->end()->format());
        }

        return $openingHoursData;
    }

    public function getStatus(Store $store): string
    {
        $now = $this->clock->now()->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()));
        $oneHourLater = $now->modify('+1 hour');
        $openingHoursSetting = $this->storeOpeningHoursProvider->getOpeningHoursSetting($store);

        if ($openingHoursSetting->isOpenAt($now)) {
            if ($openingHoursSetting->isClosedAt($oneHourLater)) {
                return StoreOpeningTypeEnum::STATUS_CLOSED_SOON;
            }

            return StoreOpeningTypeEnum::STATUS_OPEN;
        }

        if ($openingHoursSetting->isOpenAt($oneHourLater)) {
            return StoreOpeningTypeEnum::STATUS_OPEN_SOON;
        }

        return StoreOpeningTypeEnum::STATUS_CLOSED;
    }
}
