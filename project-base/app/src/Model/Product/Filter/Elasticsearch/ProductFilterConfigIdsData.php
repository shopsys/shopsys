<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;

class ProductFilterConfigIdsData
{
    /**
     * @param mixed[] $parameterValueIdsByParameterId
     * @param mixed[] $flagIds
     * @param mixed[] $brandIds
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange $priceRange
     */
    public function __construct(
        private array $parameterValueIdsByParameterId,
        private array $flagIds,
        private array $brandIds,
        private PriceRange $priceRange,
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
     * @return mixed[]
     */
    public function getParameterValueIdsByParameterId(): array
    {
        return $this->parameterValueIdsByParameterId;
    }
}
