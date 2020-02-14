<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use App\Model\Product\ProductRepository;
use App\Model\Product\Type\Exception\ProductTypeIsBeingUsedException;
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
     * @var \App\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Type\ProductTypeRepository $productTypeRepository
     * @param \App\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductTypeRepository $productTypeRepository,
        ProductRepository $productRepository
    ) {
        $this->em = $em;
        $this->productTypeRepository = $productTypeRepository;
        $this->productRepository = $productRepository;
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
        if ($this->productRepository->existsProductWithProductType($productType) === true) {
            throw new ProductTypeIsBeingUsedException($productType);
        }
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

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Type\ProductType|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?ProductType
    {
        return $this->productTypeRepository->findByAkeneoCode($akeneoCode);
    }
}
