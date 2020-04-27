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

    public function __construct(EntityManagerInterface $em, ProductPackageRepository $productPackageRepository)
    {
        $this->em = $em;
        $this->productPackageRepository = $productPackageRepository;
    }


    /**
     * @param \App\Model\Product\Package\ProductPackageData $productPackageData
     * @param \App\Model\Product\Product $product
     */
    public function createOrEdit(ProductPackageData $productPackageData, Product $product): void
    {
        $productPackage = $this->productPackageRepository->findProductPackageByProductAndPosition($product, $productPackageData->position);
        if($productPackage === null){
            $productPackage = new ProductPackage($product);
            $productPackage->setPosition($productPackageData->position);
            $productPackage->setHeight($productPackageData->height);
            $productPackage->setLength($productPackageData->length);
            $productPackage->setWidth($productPackageData->width);
            $productPackage->setWeight($productPackageData->weight);
            $this->em->persist($productPackage);
        }else{
            $productPackage->setPosition($productPackageData->position);
            $productPackage->setHeight($productPackageData->height);
            $productPackage->setLength($productPackageData->length);
            $productPackage->setWidth($productPackageData->width);
            $productPackage->setWeight($productPackageData->weight);
        }

        $this->em->flush();
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