<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductListFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFactory $productListFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListRepository $productListRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListDataFactory $productListDataFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ProductListFactory $productListFactory,
        protected readonly ProductListRepository $productListRepository,
        protected readonly ProductListDataFactory $productListDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListData $productListData
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList
     */
    public function create(ProductListData $productListData): ProductList
    {
        $productList = $this->productListFactory->create($productListData);
        $this->entityManager->persist($productList);
        $this->entityManager->flush();

        return $productList;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductList $productList
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList
     */
    public function addProductToList(ProductList $productList, Product $product): ProductList
    {
        $newProductListItem = new ProductListItem($productList, $product);
        $this->entityManager->persist($newProductListItem);

        $productList->addItem($newProductListItem);
        $this->entityManager->flush();

        return $productList;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductList $productList
     * @return int[]
     */
    public function getProductIdsByProductList(ProductList $productList): array
    {
        return $this->productListRepository->getProductIdsByProductList($productList);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList|null
     */
    public function findProductListByTypeAndCustomerUser(
        ProductListTypeEnumInterface $productListType,
        CustomerUser $customerUser,
        ?string $uuid = null,
    ): ?ProductList {
        return $this->productListRepository->findProductListByTypeAndCustomerUser($productListType, $customerUser, $uuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList|null
     */
    public function findAnonymousProductListByTypeAndUuid(
        ProductListTypeEnumInterface $productListType,
        string $uuid,
    ): ?ProductList {
        return $this->productListRepository->findAnonymousProductListByTypeAndUuid($productListType, $uuid);
    }
}
