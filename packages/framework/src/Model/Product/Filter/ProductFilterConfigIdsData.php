<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

class ProductFilterConfigIdsData
{
    /**
     * @param array<int, int[]> $parameterValueIdsByParameterId
     * @param int[] $flagIds
     * @param int[] $brandIds
     */
    public function __construct(
        protected readonly array $parameterValueIdsByParameterId,
        protected readonly array $flagIds,
        protected readonly array $brandIds,
        protected readonly PriceRange $priceRange,
    ) {
    }

    /**
     * @return int[]
     */
    public function getBrandIds(): array
    {
        return $this->brandIds;
    }

    /**
     * @return int[]
     */
    public function getFlagIds(): array
    {
        return $this->flagIds;
    }

    public function getPriceRange(): PriceRange
    {
        return $this->priceRange;
    }

    public function getParameterValueIdsByParameterId(): array
    {
        return $this->parameterValueIdsByParameterId;
    }
}
