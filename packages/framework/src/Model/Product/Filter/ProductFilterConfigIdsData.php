<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

class ProductFilterConfigIdsData
{
    /**
     * @param array $parameterValueIdsByParameterId
     * @param array $flagIds
     * @param array $brandIds
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange $priceRange
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRange(): PriceRange
    {
        return $this->priceRange;
    }

    /**
     * @return array
     */
    public function getParameterValueIdsByParameterId(): array
    {
        return $this->parameterValueIdsByParameterId;
    }
}
