<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

class ImportPriceListResultFactory
{
    protected function createInstance(): ImportPriceListResult
    {
        return new ImportPriceListResult();
    }

    public function create(): ImportPriceListResult
    {
        return $this->createInstance();
    }
}
