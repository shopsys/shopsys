<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use SortDirection;

class ProductListRepository
{
    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return int[]
     */
    public function getProductIdsByProductList(ProductList $productList): array
    {
        $result = $this->createProductsByProductListQueryBuilder($productList)
            ->select('p.id')
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'id');
    }

    /**
     * @return string[]
     */
    public function getProductUuidsByProductList(ProductList $productList): array
    {
        $result = $this->createProductsByProductListQueryBuilder($productList)
            ->select('p.uuid')
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'uuid');
    }

    protected function createProductsByProductListQueryBuilder(ProductList $productList): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->from(ProductListItem::class, 'pli')
            ->join('pli.product', 'p')
            ->where('pli.productList = :productList')
            ->orderBy('pli.position', SortDirection::Ascending)
            ->addOrderBy('pli.id', SortDirection::Descending)
            ->setParameter('productList', $productList);
    }

    public function findProductListByTypeAndCustomerUser(
        string $productListType,
        CustomerUser $customerUser,
        ?string $uuid = null,
    ): ?ProductList {
        $criteria = [
            'type' => $productListType,
            'customerUser' => $customerUser,
        ];

        if ($uuid !== null) {
            $criteria['uuid'] = $uuid;
        }

        return $this->getRepository()->findOneBy($criteria, ['createdAt' => SortDirection::Ascending]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList[]
     */
    public function getProductListsByTypeAndCustomerUser(
        string $productListType,
        CustomerUser $customerUser,
    ): array {
        return $this->getRepository()->findBy([
            'type' => $productListType,
            'customerUser' => $customerUser,
        ]);
    }

    public function findAnonymousProductList(
        string $uuid,
        ?string $productListType = null,
    ): ?ProductList {
        $criteria = [
            'uuid' => $uuid,
            'customerUser' => null,
        ];

        if ($productListType !== null) {
            $criteria['type'] = $productListType;
        }

        return $this->getRepository()->findOneBy($criteria);
    }

    public function removeOldAnonymousProductLists(DateTimeImmutable $olderThan): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(ProductList::class, 'pl')
            ->where('pl.customerUser IS NULL')
            ->andWhere('pl.updatedAt < :olderThan')
            ->setParameter('olderThan', $olderThan)
            ->getQuery()
            ->execute();
    }

    protected function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(ProductList::class);
    }

    public function existsProductListWithUuid(string $uuid): bool
    {
        return $this->getRepository()->count(['uuid' => $uuid]) > 0;
    }
}
