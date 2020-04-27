<?php

declare(strict_types=1);

namespace App\Model\Product\Package;

class ProductPackageDataFactory
{
    /**
     * @return \App\Model\Product\Package\ProductPackageData
     */
    public function create(): ProductPackageData
    {
        return  new ProductPackageData();
    }
}
