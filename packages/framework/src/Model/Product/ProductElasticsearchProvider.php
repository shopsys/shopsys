<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductElasticsearchProvider
{
    public function __construct(
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
    ) {
    }

    public function getVisibleProductArrayById(int $productId): array
    {
        $products = $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createVisibleProductsByProductIdsFilter([$productId]),
        );

        if (count($products) === 0) {
            throw new ProductNotFoundException('Product with ID ' . $productId . ' does not exist.');
        }

        return array_shift($products);
    }

    /**
     * @param int[] $productIds
     */
    public function getSellableProductArrayByIds(array $productIds, ?int $limit = null): array
    {
        return $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createSellableProductsByProductIdsFilter($productIds, $limit),
        );
    }

    /**
     * @param int[] $productIds
     */
    public function getListableProductArrayByIds(array $productIds, ?int $limit = null): array
    {
        return $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createListableProductsByProductIdsFilter($productIds, $limit),
        );
    }

    public function getVisibleProductArrayByUuid(string $productUuid): array
    {
        $products = $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createVisibleProductsByProductUuidsFilter([$productUuid]),
        );

        if (count($products) === 0) {
            throw new ProductNotFoundException('Product with UUID ' . $productUuid . ' does not exist.');
        }

        return array_shift($products);
    }

    /**
     * @param string[] $productUuids
     */
    public function getSellableProductArrayByUuids(array $productUuids): array
    {
        return $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createSellableProductsByProductUuidsFilter($productUuids),
        );
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    public function getOnlyExistingProductsIds(array $productIds, int $domainId): array
    {
        return $this->productElasticsearchRepository->getOnlyExistingProductIds($productIds, $domainId);
    }

    /**
     * @param string[] $productUuids
     */
    public function getSellableProductIdsByUuids(array $productUuids): array
    {
        return $this->productElasticsearchRepository->getSellableProductIdsByUuids($productUuids);
    }
}
