<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store\OpeningHours;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;
use Shopsys\FrameworkBundle\Model\Store\Store;

class StoreOpeningHoursApiProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider $storeOpeningHoursProvider
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @param \Shopsys\FrontendApiBundle\Model\Store\OpeningHours\OpeningHoursWithDateDataFactory $openingHoursWithDateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory $openingHoursRangeDataFactory
     */
    public function __construct(
        protected readonly StoreOpeningHoursProvider $storeOpeningHoursProvider,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly OpeningHoursWithDateDataFactory $openingHoursWithDateDataFactory,
        protected readonly OpeningHoursRangeDataFactory $openingHoursRangeDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return bool
     */
    public function isOpenNow(Store $store): bool
    {
        $now = new DateTimeImmutable(timezone: $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()));

        return $this->storeOpeningHoursProvider->getOpeningHoursSetting($store)->isOpenAt($now);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrontendApiBundle\Model\Store\OpeningHours\OpeningHoursWithDateData[]
     */
    public function getFollowingWeekOpeningHours(Store $store): array
    {
        $openingHoursData = [];

        $today = new DateTimeImmutable('today', $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()));

        for ($i = 0; $i <= 6; $i++) {
            $day = $today->modify("+{$i} days");
            $openingHoursData[] = $this->getOpeningHoursDataForDate($day, $store);
        }

        return $openingHoursData;
    }

    /**
     * @param \DateTimeImmutable $date
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrontendApiBundle\Model\Store\OpeningHours\OpeningHoursWithDateData
     */
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
}
