<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store\OpeningHours;

use DateTimeImmutable;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly OpeningHoursDataFactory $openingHoursDataFactory,
        protected readonly OpeningHoursFactory $openingHoursFactory,
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
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHours
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
