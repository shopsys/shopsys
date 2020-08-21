<?php

declare(strict_types=1);

namespace App\Model\Product\Package;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductPackageFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Product\Package\ProductPackageRepository
     */
    private $productPackageRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Package\ProductPackageRepository $productPackageRepository
     */
    public function __construct(EntityManagerInterface $em, ProductPackageRepository $productPackageRepository)
    {
        $this->em = $em;
        $this->productPackageRepository = $productPackageRepository;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Package\ProductPackageData[] $productPackagesData
     */
    public function updateProductPackages(Product $product, array $productPackagesData): void
    {
        $dontDropProductPositions = [];
        foreach ($productPackagesData as $productPackageData) {
            $this->createOrEdit($productPackageData, $product);
            $dontDropProductPositions[] = $productPackageData->position;
        }

        $productPackages = $this->getProductPackagesByProduct($product);
        $canFlush = false;
        foreach ($productPackages as $productPackage) {
            if (in_array($productPackage->getPosition(), $dontDropProductPositions, true) === false) {
                $this->em->remove($productPackage);
                $canFlush = true;
            }
        }

        if ($canFlush) {
            $this->em->flush();
        }
    }

    /**
     * @param \App\Model\Product\Package\ProductPackageData $productPackageData
     * @param \App\Model\Product\Product $product
     */
    private function createOrEdit(ProductPackageData $productPackageData, Product $product): void
    {
        $productPackage = $this->productPackageRepository->findProductPackageByProductAndPosition($product, $productPackageData->position);
        if ($productPackage === null) {
            $productPackage = new ProductPackage($product);
            $productPackage->setPosition($productPackageData->position);
            $productPackage->setHeight($productPackageData->height);
            $productPackage->setLength($productPackageData->length);
            $productPackage->setWidth($productPackageData->width);
            $productPackage->setWeight($productPackageData->weight);
            $this->em->persist($productPackage);
        } else {
            $productPackage->setPosition($productPackageData->position);
            $productPackage->setHeight($productPackageData->height);
            $productPackage->setLength($productPackageData->length);
            $productPackage->setWidth($productPackageData->width);
            $productPackage->setWeight($productPackageData->weight);
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Package\ProductPackage[]
     */
    public function getProductPackagesByProduct(Product $product): array
    {
        return $this->productPackageRepository->getProductPackagesByProduct($product);
    }
}
