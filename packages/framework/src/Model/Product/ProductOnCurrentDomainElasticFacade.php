<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductOnCurrentDomainElasticFacade
{
    public function __construct(
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
    ) {
    }

    /**
     * @return int[]
     */
    public function getCategoryIdsForFilterData(ProductFilterData $productFilterData): array
    {
        return $this->productElasticsearchRepository->getCategoryIdsForFilterData($productFilterData);
    }
}
