<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

class ImportPriceListResultFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ImportPriceListResult
     */
    protected function createInstance(): ImportPriceListResult
    {
        return new ImportPriceListResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ImportPriceListResult
     */
    public function create(): ImportPriceListResult
    {
        return $this->createInstance();
    }
}
