<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductListFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFactory $productListFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ProductListFactory $productListFactory,
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
}
