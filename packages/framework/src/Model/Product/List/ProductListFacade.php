<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\Exception\ProductAlreadyInListException;
use Shopsys\FrameworkBundle\Model\Product\List\Exception\ProductNotInListException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductListFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ProductListFactory $productListFactory,
        protected readonly ProductListRepository $productListRepository,
        protected readonly ProductListItemFactory $productListItemFactory,
    ) {
    }

    public function create(ProductListData $productListData): ProductList
    {
        $productList = $this->productListFactory->create($productListData);
        $this->entityManager->persist($productList);
        $this->entityManager->flush();

        return $productList;
    }

    public function addProductToList(ProductList $productList, Product $product): ProductList
    {
        if ($productList->findProductListItemByProduct($product) !== null) {
            throw new ProductAlreadyInListException(sprintf('Product with UUID %s already exists in the list.', $product->getUuid()));
        }
        $newProductListItem = $this->productListItemFactory->create($productList, $product);
        $this->entityManager->persist($newProductListItem);

        $productList->addItem($newProductListItem);
        $this->entityManager->flush();

        return $productList;
    }

    /**
     * @return int[]
     */
    public function getProductIdsByProductList(ProductList $productList): array
    {
        return $this->productListRepository->getProductIdsByProductList($productList);
    }

    public function findProductListByTypeAndCustomerUser(
        string $productListType,
        CustomerUser $customerUser,
        ?string $uuid = null,
    ): ?ProductList {
        return $this->productListRepository->findProductListByTypeAndCustomerUser($productListType, $customerUser, $uuid);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList[]
     */
    public function getProductListsByTypeAndCustomerUser(
        string $productListType,
        CustomerUser $customerUser,
    ): array {
        return $this->productListRepository->getProductListsByTypeAndCustomerUser($productListType, $customerUser);
    }

    public function findAnonymousProductList(
        string $uuid,
        ?string $productListType = null,
    ): ?ProductList {
        return $this->productListRepository->findAnonymousProductList($uuid, $productListType);
    }

    public function existsProductListWithUuid(string $uuid): bool
    {
        return $this->productListRepository->existsProductListWithUuid($uuid);
    }

    public function removeProductFromList(ProductList $productList, Product $product): ?ProductList
    {
        $productListItem = $productList->findProductListItemByProduct($product);

        if ($productListItem === null) {
            throw new ProductNotInListException(sprintf('Product with UUID %s does not exist in the list with UUID %s.', $product->getUuid(), $productList->getUuid()));
        }
        $productList->removeItem($productListItem);
        $this->entityManager->remove($productListItem);
        $this->entityManager->flush();

        if ($productList->getItemsCount() === 0) {
            $this->entityManager->remove($productList);
            $this->entityManager->flush();

            return null;
        }

        return $productList;
    }

    public function removeProductList(ProductList $productList): void
    {
        $this->entityManager->remove($productList);
        $this->entityManager->flush();
    }

    public function removeOldAnonymousProductLists(DateTimeImmutable $olderThan): void
    {
        $this->productListRepository->removeOldAnonymousProductLists($olderThan);
    }

    /**
     * @param string[] $productListsUuids
     */
    public function mergeProductListsToCustomerUser(array $productListsUuids, CustomerUser $customerUser): void
    {
        foreach ($productListsUuids as $productListUuid) {
            $anonymousProductList = $this->findAnonymousProductList($productListUuid);

            if ($anonymousProductList !== null) {
                $this->mergeProductListToCustomerUser($anonymousProductList, $customerUser);
            }
        }
    }

    protected function mergeProductListToCustomerUser(
        ProductList $anonymousProductList,
        CustomerUser $customerUser,
    ): void {
        $customerUserProductList = $this->findProductListByTypeAndCustomerUser($anonymousProductList->getType(), $customerUser);

        if ($customerUserProductList !== null) {
            $this->mergeProductLists($anonymousProductList, $customerUserProductList);
        } else {
            $this->setCustomerUserToProductList($customerUser, $anonymousProductList);
        }
    }

    protected function setCustomerUserToProductList(CustomerUser $customerUser, ProductList $productList): void
    {
        $productList->setCustomerUser($customerUser);
        $this->entityManager->flush();
    }

    protected function mergeProductLists(ProductList $productListToMergeFrom, ProductList $productListToMergeTo): void
    {
        foreach (array_reverse($productListToMergeFrom->getItems()) as $productListItem) {
            try {
                $this->addProductToList($productListToMergeTo, $productListItem->getProduct());
            } catch (ProductAlreadyInListException $e) {
                // Product is already in the list, so we can skip it
                continue;
            }
        }

        $this->removeProductList($productListToMergeFrom);
    }
}
