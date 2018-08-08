<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

class HeurekaCategoryDataFactory implements HeurekaCategoryDataFactoryInterface
{
    public function create(): HeurekaCategoryData
    {
        return new HeurekaCategoryData();
    }
}
