<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator as BaseProductHiddenRecalculator;

/**
 * @method calculateHiddenForProduct(\App\Model\Product\Product $product)
 */
class ProductHiddenRecalculator extends BaseProductHiddenRecalculator
{
    /**
     * @param \App\Model\Product\Product|null $product
     */
    protected function executeQuery(?Product $product = null): void
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedHidden', 'p.hidden');

        if ($product !== null) {
            $qb->where('p = :product')->setParameter('product', $product);
        }

        $qb->getQuery()->execute();
    }
}
