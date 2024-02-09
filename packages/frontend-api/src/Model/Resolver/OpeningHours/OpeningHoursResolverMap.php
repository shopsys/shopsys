<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\OpeningHours;

use DateTimeImmutable;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;
use Shopsys\FrameworkBundle\Model\Store\Store;

class OpeningHoursResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHoursDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
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

                    return $this->getOpeningHoursForStore($openingHour->getStore());
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return array
     */
    protected function getOpeningHoursForStore(Store $store): array
    {
        $openingHoursOfDays = [];

        $today = new DateTimeImmutable('today', timezone: $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()));

        for ($i = 0; $i <= 6; $i++) {
            $day = $today->modify("+${i} days");

            $openingHoursOfDays[] = [
                'date' => $day,
                'dayOfWeek' => (int)$day->format('N'),
                'openingHoursRanges' => $this->getOpeningHoursRanges($this->storeOpeningHoursProvider->getOpeningHoursDataForDay($day, $store)),
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
