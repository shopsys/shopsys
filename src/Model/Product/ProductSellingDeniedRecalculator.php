<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator as BaseProductSellingDeniedRecalculator;

/**
 * @method calculateSellingDeniedForProduct(\App\Model\Product\Product $product)
 * @method calculate(\App\Model\Product\Product[] $products)
 * @method \App\Model\Product\Product[] getProductsForCalculations(\App\Model\Product\Product $product)
 * @method propagateMainVariantSellingDeniedToVariants(\App\Model\Product\Product[] $products)
 * @method propagateVariantsSellingDeniedToMainVariant(\App\Model\Product\Product[] $products)
 */
class ProductSellingDeniedRecalculator extends BaseProductSellingDeniedRecalculator
{
    /**
     * @param \App\Model\Product\Product[] $products
     */
    protected function calculateIndependent(array $products)
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', 'p.sellingDenied');
        if (count($products) > 0) {
            $qb->andWhere('p IN (:products)')->setParameter('products', $products);
        }
        $qb->getQuery()->execute();
    }
}
