<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\OpeningHours;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;

class OpeningHoursResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider $storeOpeningHoursProvider
     */
    public function __construct(
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

                    return $this->dateTimeHelper->getCurrentDayOfWeek($openingHour->getStore()->getDomainId());
                },
                'openingHoursOfDays' => function (array $openingHours): array {
                    /** @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = reset($openingHours);

                    return $this->storeOpeningHoursProvider->getThisWeekOpeningHours($openingHour->getStore());
                },
            ],
        ];
    }
}
