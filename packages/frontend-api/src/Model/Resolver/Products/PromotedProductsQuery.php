<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PromotedProductsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
    ) {
    }

    public function promotedProductsQuery(): array
    {
        $filterQuery = $this->filterQueryFactory->createPromotedOnDomainFilter(
            $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        );

        return $this->productElasticsearchRepository
            ->getSortedProductsResultByFilterQuery($filterQuery)
            ->getHits();
    }
}
