<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Brand;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;

class LuigisBoxBrandSearchResultsMapper
{
    public function __construct(
        protected readonly BrandFacade $brandFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function mapBrandData(LuigisBoxResult $luigisBoxResult): array
    {
        return $this->brandFacade->getBrandsByIds($luigisBoxResult->getIds());
    }
}
