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
    public function create(ProductPackageData $productPackageData,Product $product): void
    {
        $isToFlush = false;
        foreach(array_keys($productPackageData->position) as $domainId){
            $productPackage = new ProductPackage($product, $domainId);
            $productPackage->setPosition($productPackageData->position[$domainId]);
            $productPackage->setHeight($productPackageData->height[$domainId]);
            $productPackage->setLength($productPackageData->length[$domainId]);
            $productPackage->setWidth($productPackageData->width[$domainId]);
            $productPackage->setWeight($productPackageData->weight[$domainId]);

            $this->em->persist($productPackage);
            $isToFlush = true;
        }

        if($isToFlush){
            $this->em->flush();
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Product\Package\ProductPackage[]
     */
    public function findProductPackagesByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->productPackageRepository->findProductPackagesByProductAndDomainId($product, $domainId);
    }

}