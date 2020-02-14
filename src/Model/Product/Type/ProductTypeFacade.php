<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use Doctrine\ORM\EntityManagerInterface;

class ProductTypeFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \App\Model\Product\Type\ProductTypeRepository
     */
    protected $productTypeRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Type\ProductTypeRepository $productTypeRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductTypeRepository $productTypeRepository
    ) {
        $this->em = $em;
        $this->productTypeRepository = $productTypeRepository;
    }

    /**
     * @param int $productTypeId
     * @return \App\Model\Product\Type\ProductType
     */
    public function getById(int $productTypeId): ProductType
    {
        return $this->productTypeRepository->getById($productTypeId);
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     * @return \App\Model\Product\Type\ProductType
     */
    public function create(ProductTypeData $productTypeData): ProductType
    {
        $productType = new ProductType($productTypeData);
        $this->em->persist($productType);
        $this->em->flush();

        return $productType;
    }

    /**
     * @param int $productTypeId
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    public function edit(int $productTypeId, ProductTypeData $productTypeData): void
    {
        $productType = $this->getById($productTypeId);
        $productType->edit($productTypeData);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     */
    public function delete(ProductType $productType): void
    {
        $this->em->remove($productType);
        $this->em->flush();
    }

    /**
     * @return \App\Model\Product\Type\ProductType[]
     */
    public function getAll(): array
    {
        return $this->productTypeRepository->getAll();
    }
}
