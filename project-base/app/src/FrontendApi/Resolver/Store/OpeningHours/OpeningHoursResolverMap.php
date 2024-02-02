<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store\OpeningHours;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataHelper;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;

class OpeningHoursResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHoursDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider $displayTimeZoneProvider
     * @param \Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider $storeOpeningHoursProvider
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly OpeningHoursDataFactory $openingHoursDataFactory,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly DateTimeHelper $dateTimeHelper,
        protected readonly StoreOpeningHoursProvider $storeOpeningHoursProvider,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'OpeningHours' => [
                'isOpen' => function (array $openingHours): bool {
                    /** @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = reset($openingHours);

                    return $this->storeOpeningHoursProvider->isOpenNow($openingHour->getStore());
                },
                'dayOfWeek' => function (array $openingHours): int {
                    /** @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = reset($openingHours);

                    return $this->dateTimeHelper->getDayOfWeek($openingHour->getStore()->getDomainId());
                },
                'openingHoursOfDays' => function (array $openingHours): array {
                    /** @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = reset($openingHours);

                    return $this->getOpeningHoursOfDays($this->storeOpeningHoursProvider->getThisWeekOpeningHours($openingHour->getStore()));
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursData
     * @return array
     */
    protected function getOpeningHoursOfDays(array $openingHoursData): array
    {
        $openingHoursOfDays = [];

        foreach (OpeningHoursDataHelper::getOpeningHoursIndexedByDayNumber($openingHoursData) as $dayNumber => $openingHours) {
            $openingHoursOfDays[] = [
                'dayOfWeek' => $dayNumber,
                'openingHoursRanges' => $this->getOpeningHoursRanges($openingHours),
            ];
        }

        return $openingHoursOfDays;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursData
     * @return array{openingTime: string, closingTime: string}[]
     */
    protected function getOpeningHoursRanges(array $openingHoursData): array
    {
        $openingHoursRanges = [];

        foreach ($openingHoursData as $openingHourData) {
            if ($openingHourData->openingTime !== null && $openingHourData->closingTime !== null) {
                $openingHoursRanges[] = [
                    'openingTime' => $openingHourData->openingTime,
                    'closingTime' => $openingHourData->closingTime,
                ];
            }
        }

        return $openingHoursRanges;
    }
}
