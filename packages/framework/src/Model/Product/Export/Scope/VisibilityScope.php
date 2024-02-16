<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

// TODO experiment s Honzou
class VisibilityScope
{
    public function map(): array
    {
        return [
            'price' => $this->exportPrice()
        ];
    }

    public function getEntityFields()
    {
        return ['Product::hidden', 'Product::sellingFrom', 'Product::sellingTo', 'Product::price'];
    }
}
