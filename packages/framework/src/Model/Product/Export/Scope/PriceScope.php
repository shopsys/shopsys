<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

// TODO experiment s Honzou
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class PriceScope
{
    public function map(): array
    {
        return [
            'prices' => '$this->>exportPrices();',
            'visibility' => '$this->exportVisibility();',
        ];
    }

    public function getEntityFields()
    {
        return ['Product::price'];
    }
}
