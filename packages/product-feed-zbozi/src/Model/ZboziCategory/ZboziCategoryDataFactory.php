<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

class ZboziCategoryDataFactory
{
    protected function createInstance(): ZboziCategoryData
    {
        return new ZboziCategoryData();
    }

    public function create(string $locale): ZboziCategoryData
    {
        $zboziCategoryData = $this->createInstance();
        $zboziCategoryData->locale = $locale;

        return $zboziCategoryData;
    }
}
