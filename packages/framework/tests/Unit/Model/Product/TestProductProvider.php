<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;

class TestProductProvider
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    public static function getTestProductData(): ProductData
    {
        $availabilityData = new AvailabilityData();
        $availabilityData->name = [
            'en' => 'availability name',
        ];

        $unitData = new UnitData();
        $unitData->name = [
            'en' => 'unit name',
        ];

        $productData = new ProductData();
        $productData->availability = new Availability($availabilityData);
        $productData->unit = new Unit($unitData);

        return $productData;
    }
}
