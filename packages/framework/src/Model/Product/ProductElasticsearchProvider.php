<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductElasticsearchProvider
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository
     */
    protected $productElasticsearchRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory
     */
    protected $filterQueryFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     */
    public function __construct(
        ProductElasticsearchRepository $productElasticsearchRepository,
        FilterQueryFactory $filterQueryFactory
    ) {
        $this->productElasticsearchRepository = $productElasticsearchRepository;
        $this->filterQueryFactory = $filterQueryFactory;
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getVisibleProductArrayById(int $productId): array
    {
        $products = $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createVisibleProductsByProductIdsFilter([$productId])
        );

        if (count($products) === 0) {
            throw new ProductNotFoundException();
        }
        return array_shift($products);
    }

    /**
     * @param int[] $productIds
     * @param int|null $limit
     * @return array
     */
    public function getSellableProductArrayByIds(array $productIds, ?int $limit = null): array
    {
        return $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createSellableProductsByProductIdsFilter($productIds, $limit)
        );
    }
}
