<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store\OpeningHours;

use DateTimeImmutable;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursFactory;
use Shopsys\FrameworkBundle\Model\Store\Store;

class OpeningHoursResolverMap extends ResolverMap
{
    /**
     * @var array<int, array<int, \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay>>
     */
    protected array $thisWeekClosedDaysIndexedByStoreIdAndDayNumber = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHoursDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursFactory $openingHoursFactory
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider $displayTimeZoneProvider
     * @param \Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly OpeningHoursDataFactory $openingHoursDataFactory,
        protected readonly OpeningHoursFactory $openingHoursFactory,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly DateTimeHelper $dateTimeHelper,
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

                    $closedDays = $this->closedDayFacade->getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber(
                        $this->domain->getId(),
                        $openingHour->getStore(),
                    );

                    return $openingHour->getStore()->isOpenNow(
                        $closedDays,
                        $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId()),
                    );
                },
                'dayOfWeek' => $this->getDayOfWeek(...),
                'openingHoursOfDays' => fn (array $openingHours): array => array_map(function (OpeningHours $openingHours): array {
                    if ($this->isStoreClosedOnDay($openingHours->getStore(), $openingHours->getDayOfWeek())) {
                        $openingHoursData = $this->openingHoursDataFactory->create();
                        $openingHoursData->dayOfWeek = $openingHours->getDayOfWeek();

                        return $this->mapOpeningHoursToArray($this->openingHoursFactory->create($openingHoursData));
                    }

                    return $this->mapOpeningHoursToArray($openingHours);
                }, $openingHours),
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @param int $dayNumber
     * @return bool
     */
    protected function isStoreClosedOnDay(Store $store, int $dayNumber): bool
    {
        $timeZone = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId());
        $day = DateTimeHelper::getUtcDateForDayInCurrentWeek(
            $dayNumber,
            $timeZone,
        );
        $closedDays = $this->closedDayFacade->getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber(
            $this->domain->getId(),
            $store,
        );

        foreach ($closedDays as $closedDay) {
            if ($closedDay->getDate()->format('N') === $day->format('N')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    protected function getDayOfWeek(): int
    {
        return (int)(new DateTimeImmutable(
            'now',
        ))->format('N');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHours
     * @return array
     */
    protected function mapOpeningHoursToArray(OpeningHours $openingHours): array
    {
        return [
            'dayOfWeek' => $openingHours->getDayOfWeek(),
            'firstOpeningTime' => $this->dateTimeHelper->convertHoursAndMinutesFromUtcToDisplayTimezone($openingHours->getFirstOpeningTime(), $this->domain->getId()),
            'firstClosingTime' => $this->dateTimeHelper->convertHoursAndMinutesFromUtcToDisplayTimezone($openingHours->getFirstClosingTime(), $this->domain->getId()),
            'secondOpeningTime' => $this->dateTimeHelper->convertHoursAndMinutesFromUtcToDisplayTimezone($openingHours->getSecondOpeningTime(), $this->domain->getId()),
            'secondClosingTime' => $this->dateTimeHelper->convertHoursAndMinutesFromUtcToDisplayTimezone($openingHours->getSecondClosingTime(), $this->domain->getId()),
        ];
    }
}
