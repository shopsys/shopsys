<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OpeningHoursRangeFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHours
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeData[] $openingHoursRangesData
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange[]
     */
    public function createOpeningHoursRanges(OpeningHours $openingHours, array $openingHoursRangesData): array
    {
        $openingHoursRanges = [];

        foreach ($openingHoursRangesData as $openingHoursRangeData) {
            $openingHoursRanges[] = $this->create($openingHoursRangeData, $openingHours);
        }

        return $openingHoursRanges;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeData $openingHoursRangeData
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHours
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange
     */
    protected function create(
        OpeningHoursRangeData $openingHoursRangeData,
        OpeningHours $openingHours,
    ): OpeningHoursRange {
        $entityClassName = $this->entityNameResolver->resolve(OpeningHoursRange::class);

        return new $entityClassName($openingHoursRangeData, $openingHours);
    }
}
