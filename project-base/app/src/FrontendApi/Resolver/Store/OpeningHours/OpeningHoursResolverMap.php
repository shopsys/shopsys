<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store\OpeningHours;

use App\Model\Store\ClosedDay\ClosedDayFacade;
use App\Model\Store\OpeningHours\OpeningHours;
use App\Model\Store\OpeningHours\OpeningHoursDataFactory;
use App\Model\Store\Store;
use DateTimeImmutable;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class OpeningHoursResolverMap extends ResolverMap
{
    /**
     * @var array<int, array<int, \App\Model\Store\ClosedDay\ClosedDay>>
     */
    protected array $thisWeekClosedDaysIndexedByStoreIdAndDayNumber = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \App\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHoursDataFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly OpeningHoursDataFactory $openingHoursDataFactory,
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
                    /** @var \App\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = reset($openingHours);

                    $now = new DateTimeImmutable(
                        'now',
                        $this->domain->getCurrentDomainConfig()->getDateTimeZone(),
                    );

                    return $openingHour->getStore()->isOpen(
                        $now,
                        $this->closedDayFacade->getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber(
                            $this->domain->getId(),
                            $openingHour->getStore(),
                        ),
                    );
                },
                'dayOfWeek' => $this->getDayOfWeek(...),
                'openingHoursOfDays' => fn (array $openingHours): array => array_map(function (OpeningHours $openingHours): array {
                    if ($this->isStoreClosedOnDay($openingHours->getStore(), $openingHours->getDayOfWeek())) {
                        $openingHoursData = $this->openingHoursDataFactory->create();
                        $openingHoursData->dayOfWeek = $openingHours->getDayOfWeek();

                        return $this->mapOpeningHoursToArray(new OpeningHours($openingHoursData));
                    }

                    return $this->mapOpeningHoursToArray($openingHours);
                }, $openingHours),
            ],
        ];
    }

    /**
     * @param \App\Model\Store\Store $store
     * @param int $dayNumber
     * @return bool
     */
    protected function isStoreClosedOnDay(Store $store, int $dayNumber): bool
    {
        if (!array_key_exists($store->getId(), $this->thisWeekClosedDaysIndexedByStoreIdAndDayNumber)) {
            $this->thisWeekClosedDaysIndexedByStoreIdAndDayNumber[$store->getId()] = $this->closedDayFacade->getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber(
                $this->domain->getId(),
                $store,
            );
        }

        return array_key_exists($dayNumber, $this->thisWeekClosedDaysIndexedByStoreIdAndDayNumber[$store->getId()]);
    }

    /**
     * @return int
     */
    protected function getDayOfWeek(): int
    {
        return (int)(new DateTimeImmutable(
            'now',
            $this->domain->getCurrentDomainConfig()->getDateTimeZone(),
        ))->format('N');
    }

    /**
     * @param \App\Model\Store\OpeningHours\OpeningHours $openingHours
     * @return array
     */
    protected function mapOpeningHoursToArray(OpeningHours $openingHours): array
    {
        return [
            'dayOfWeek' => $openingHours->getDayOfWeek(),
            'firstOpeningTime' => $openingHours->getFirstOpeningTime(),
            'firstClosingTime' => $openingHours->getFirstClosingTime(),
            'secondOpeningTime' => $openingHours->getSecondOpeningTime(),
            'secondClosingTime' => $openingHours->getSecondClosingTime(),
        ];
    }
}
