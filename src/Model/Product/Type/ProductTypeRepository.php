<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

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
    private function getProductTypeRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductType::class);
    }

    /**
     * @param int $productTypeId
     * @return \App\Model\Product\Type\ProductType|null
     */
    public function findById(int $productTypeId): ?ProductType
    {
        return $this->getProductTypeRepository()->find($productTypeId);
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
        return $this->getProductTypeRepository()->findBy([], ['id' => 'asc']);
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Type\ProductType|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?ProductType
    {
        return $this->getProductTypeRepository()->findOneBy(['akeneoCode' => $akeneoCode]);
    }
}
