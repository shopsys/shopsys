<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DemoDataFactory;

use App\DataFixtures\Demo\DataSetter\ProductDemoDataSetter;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;

class ProductDemoDataFactory
{
    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\DataFixtures\Demo\DataSetter\ProductDemoDataSetter $productDemoDataSetter
     */
    public function __construct(
        private readonly ProductDataFactory $productDataFactory,
        private readonly ProductDemoDataSetter $productDemoDataSetter,
    ) {
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\ProductData
     */
    public function createDefaultData(string $catnum): ProductData
    {
        $productData = $this->productDataFactory->create();

        $productData->catnum = $catnum;
        $productData->sellingDenied = false;

        $this->productDemoDataSetter->setUnit($productData, UnitDataFixture::UNIT_PIECES);

        return $productData;
    }
}
