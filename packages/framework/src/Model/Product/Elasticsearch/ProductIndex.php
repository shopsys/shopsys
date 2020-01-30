<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class ProductIndex extends AbstractIndex
{
    public const INDEX_NAME = 'product';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductDataProvider $productDataProvider
     */
    public function __construct(ProductDataProvider $productDataProvider)
    {
        parent::__construct($productDataProvider);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::INDEX_NAME;
    }
}
