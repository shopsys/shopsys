<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

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
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductList $productList
     * @return int[]
     */
    public function getProductIdsByProductList(ProductList $productList): array
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('p.id')
            ->from(ProductListItem::class, 'pli')
            ->join('pli.product', 'p')
            ->where('pli.productList = :productList')
            ->orderBy('pli.createdAt', 'DESC')
            ->setParameter('productList', $productList)
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'id');
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
        $criteria = [
            'type' => $productListType,
            'customerUser' => $customerUser,
        ];

        if ($uuid !== null) {
            $criteria['uuid'] = $uuid;
        }

        return $this->getRepository()->findOneBy($criteria, ['createdAt' => 'asc']);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList[]
     */
    public function getProductListsByTypeAndCustomerUser(
        ProductListTypeEnumInterface $productListType,
        CustomerUser $customerUser,
    ): array {
        return $this->getRepository()->findBy([
            'type' => $productListType,
            'customerUser' => $customerUser,
        ]);
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
        return $this->getRepository()->findOneBy([
            'type' => $productListType,
            'uuid' => $uuid,
            'customerUser' => null,
        ]);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(ProductList::class);
    }
}
