<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

class HeurekaCategoryDataFactory
{
    protected function createInstance(): HeurekaCategoryData
    {
        return new HeurekaCategoryData();
    }

    public function create(string $locale): HeurekaCategoryData
    {
        $heurekaCategoryData = $this->createInstance();
        $heurekaCategoryData->locale = $locale;

        return $heurekaCategoryData;
    }
}
