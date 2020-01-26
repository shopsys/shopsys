<?php

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex;

class ProductIndex extends AbstractIndex
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'product';
    }
}
