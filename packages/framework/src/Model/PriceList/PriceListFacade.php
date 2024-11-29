<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class PriceListFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListFactory $priceListFactory
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListRepository $priceListRepository
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceFactory $productWithPriceFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PriceListFactory $priceListFactory,
        protected readonly PriceListRepository $priceListRepository,
        protected readonly ProductWithPriceFactory $productWithPriceFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceList
     */
    public function getById(int $id): PriceList
    {
        return $this->priceListRepository->getById($id);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPriceListGridQueryBuilder(): QueryBuilder
    {
        return $this->priceListRepository->getPriceListGridQueryBuilder();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceList
     */
    public function create(PriceListData $priceListData): PriceList
    {
        $priceList = $this->priceListFactory->create($priceListData);
        $this->em->persist($priceList);
        $this->em->flush();

        $this->refreshProductWithPrices($priceList, $priceListData);

        return $priceList;
    }

    /**
     * @param int $priceListId
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     */
    public function edit(int $priceListId, PriceListData $priceListData): void
    {
        $priceList = $this->getById($priceListId);
        $originalProductIds = array_map(
            static fn (ProductWithPrice $productWithPrice) => $productWithPrice->getProduct()->getId(),
            $priceList->getProductsWithPrices(),
        );

        $priceList->edit($priceListData);

        $this->em->flush();

        $this->refreshProductWithPrices($priceList, $priceListData, $originalProductIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList $priceList
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     * @param int[] $originalProductIds
     */
    protected function refreshProductWithPrices(
        PriceList $priceList,
        PriceListData $priceListData,
        array $originalProductIds = [],
    ): void {
        $dispatchedProductIds = [];

        foreach ($priceListData->productsWithPrices as $productWithPriceData) {
            $productWithPrice = $this->productWithPriceFactory->create($productWithPriceData);
            $this->em->persist($productWithPrice);
            $priceList->addProductWithPrice($productWithPrice);

            $this->productRecalculationDispatcher->dispatchSingleProductId(
                $productWithPrice->getProduct()->getId(),
                ProductRecalculationPriorityEnum::HIGH,
                [ProductExportScopeConfig::SCOPE_PRICE],
            );

            $dispatchedProductIds[] = $productWithPrice->getProduct()->getId();
        }

        $removedProductIds = array_diff($originalProductIds, $dispatchedProductIds);

        $this->productRecalculationDispatcher->dispatchProductIds(
            $removedProductIds,
            ProductRecalculationPriorityEnum::HIGH,
            [ProductExportScopeConfig::SCOPE_PRICE],
        );

        $this->em->flush();
    }

    /**
     * @param int $priceListId
     */
    public function delete(int $priceListId): void
    {
        $priceList = $this->getById($priceListId);

        $originalProductIds = array_map(
            static fn (PriceListProductPrice $priceListProductPrice) => $priceListProductPrice->getProduct()->getId(),
            $priceList->getPriceListProductPrices(),
        );

        $this->productRecalculationDispatcher->dispatchProductIds(
            $originalProductIds,
            ProductRecalculationPriorityEnum::HIGH,
            [ProductExportScopeConfig::SCOPE_PRICE],
        );

        $this->em->remove($priceList);
        $this->em->flush();
    }
}
