<?php

declare(strict_types=1);


namespace App\Model\Product\Package;


class ProductPackageDataFactory
{

    /**
     * @param \App\Model\Product\Package\ProductPackage $productPackage
     * @return \App\Model\Product\Package\ProductPackageData
     */
    public function createFromProductPackage(ProductPackage $productPackage): ProductPackageData
    {
        $productPackageData = $this->create();
        $productPackageData->position = $productPackage->getPosition();
        $productPackageData->length = $productPackage->getLength();
        $productPackageData->width = $productPackage->getWidth();
        $productPackageData->height = $productPackage->getHeight();
        $productPackageData->weight = $productPackage->getWeight();

        return $productPackageData;
    }

    /**
     * @return \App\Model\Product\Package\ProductPackageData
     */
    public function create(): ProductPackageData
    {
        return  new ProductPackageData();
    }

}