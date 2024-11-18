<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PriceListFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListFactory $priceListFactory
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListRepository $priceListRepository
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceFactory $productWithPriceFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PriceListFactory $priceListFactory,
        protected readonly PriceListRepository $priceListRepository,
        protected readonly ProductWithPriceFactory $productWithPriceFactory,
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

        $priceList->edit($priceListData);

        $this->em->flush();

        $this->refreshProductWithPrices($priceList, $priceListData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList $priceList
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     */
    protected function refreshProductWithPrices(PriceList $priceList, PriceListData $priceListData): void
    {
        foreach ($priceListData->productsWithPrices as $productWithPriceData) {
            $productWithPrice = $this->productWithPriceFactory->create($productWithPriceData);
            $this->em->persist($productWithPrice);
            $priceList->addProductWithPrice($productWithPrice);
        }

        $this->em->flush();
    }

    /**
     * @param int $priceListId
     */
    public function delete(int $priceListId): void
    {
        $priceList = $this->getById($priceListId);

        $this->em->remove($priceList);
        $this->em->flush();
    }
}
