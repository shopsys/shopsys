<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

class HeurekaCategoryDataFactory implements HeurekaCategoryDataFactoryInterface
{
    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData
     */
    protected function createInstance(): HeurekaCategoryData
    {
        return new HeurekaCategoryData();
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData
     */
    public function create(): HeurekaCategoryData
    {
        return $this->createInstance();
    }
}
