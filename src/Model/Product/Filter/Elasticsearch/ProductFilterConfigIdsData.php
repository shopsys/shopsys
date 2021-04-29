<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;

class ProductFilterConfigIdsData
{
    /**
     * @var array
     */
    private array $parameterValueIdsByParameterId;

    /**
     * @var int[]
     */
    private array $flagIds;

    /**
     * @var int[]
     */
    private array $brandIds;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    private PriceRange $priceRange;

    /**
     * @param array $parameterValueIdsByParameterId
     * @param array $flagIds
     * @param array $brandIds
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange $priceRange
     */
    public function __construct(
        array $parameterValueIdsByParameterId,
        array $flagIds,
        array $brandIds,
        PriceRange $priceRange
    ) {
        $this->parameterValueIdsByParameterId = $parameterValueIdsByParameterId;
        $this->flagIds = $flagIds;
        $this->brandIds = $brandIds;
        $this->priceRange = $priceRange;
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
