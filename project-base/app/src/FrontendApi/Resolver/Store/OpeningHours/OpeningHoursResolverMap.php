<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store\OpeningHours;

use App\Model\Store\OpeningHours\OpeningHours;
use DateTimeImmutable;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class OpeningHoursResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
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

                    return $openingHour->getStore()->isOpen($now);
                },
                'dayOfWeek' => function (array $openingHours): int {
                    return (int)(new DateTimeImmutable(
                        'now',
                        $this->domain->getCurrentDomainConfig()->getDateTimeZone(),
                    ))->format('N');
                },
                'openingHoursOfDays' => fn (array $openingHours): array => array_map(static function (OpeningHours $openingHours): array {
                    return [
                        'dayOfWeek' => $openingHours->getDayOfWeek(),
                        'firstOpeningTime' => $openingHours->getFirstOpeningTime(),
                        'firstClosingTime' => $openingHours->getFirstClosingTime(),
                        'secondOpeningTime' => $openingHours->getSecondOpeningTime(),
                        'secondClosingTime' => $openingHours->getSecondClosingTime(),
                    ];
                }, $openingHours),
            ],
        ];
    }
}
