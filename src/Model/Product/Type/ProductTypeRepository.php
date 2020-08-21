<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use App\Model\Order\Item\OrderItem;
use App\Model\Product\ProductDomain;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProductTypeRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductType::class);
    }

    /**
     * @param int $productTypeId
     * @return \App\Model\Product\Type\ProductType|null
     */
    public function findById(int $productTypeId): ?ProductType
    {
        return $this->getRepository()->find($productTypeId);
    }

    /**
     * @param int $productTypeId
     * @return \App\Model\Product\Type\ProductType
     */
    public function getById(int $productTypeId): ProductType
    {
        $productType = $this->findById($productTypeId);

        if ($productType === null) {
            throw new \App\Model\Product\Type\Exception\ProductTypeNotFoundException('ProductType with ID ' . $productTypeId . ' not found.');
        }

        return $productType;
    }

    /**
     * @return \App\Model\Product\Type\ProductType[]
     */
    public function getAll(): array
    {
        return $this->getRepository()->findBy([], ['position' => 'asc']);
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Type\ProductType|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?ProductType
    {
        return $this->getRepository()->findOneBy(['akeneoCode' => $akeneoCode]);
    }

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     * @return bool
     */
    public function existsRelationToProductType(ProductType $productType): bool
    {
        $orderItemsCount = $this->em->createQueryBuilder()
            ->select('COUNT(oi)')
            ->from(OrderItem::class, 'oi')
            ->where('oi.productType = :productType')
            ->setParameter('productType', $productType)
            ->getQuery()
            ->getSingleScalarResult();

        return $orderItemsCount > 0;
    }

    /**
     * @return \App\Model\Product\Type\ProductType
     */
    public function getFirstProductType(): ProductType
    {
        return $this->getRepository()->findOneBy([], ['position' => 'asc']);
    }
}
