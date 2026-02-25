<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\OpeningHours;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrontendApiBundle\Model\Store\OpeningHours\StoreOpeningHoursApiProvider;

class OpeningHoursResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly DateTimeHelper $dateTimeHelper,
        protected readonly StoreOpeningHoursApiProvider $storeOpeningHoursApiProvider,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'OpeningHours' => [
                'status' => function (array $openingHours): string {
                    /** @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = array_first($openingHours);

                    return $this->storeOpeningHoursApiProvider->getStatus($openingHour->getStore());
                },
                'dayOfWeek' => function (array $openingHours): int {
                    /** @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = array_first($openingHours);

                    return $this->dateTimeHelper->getCurrentDayOfWeek($openingHour->getStore()->getDomainId());
                },
                'openingHoursOfDays' => function (array $openingHours): array {
                    /** @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHour */
                    $openingHour = array_first($openingHours);

                    return $this->storeOpeningHoursApiProvider->getFollowingWeekOpeningHours($openingHour->getStore());
                },
            ],
        ];
    }
}
