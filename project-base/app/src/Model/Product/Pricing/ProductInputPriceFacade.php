<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade as BaseProductInputPriceFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][]|null $productRowsIterator
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceRecalculator $productInputPriceRecalculator, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher)
 * @method \Shopsys\FrameworkBundle\Component\Money\Money[]|null[] getManualInputPricesDataIndexedByPricingGroupId(\App\Model\Product\Product $product)
 */
class ProductInputPriceFacade extends BaseProductInputPriceFacade
{
}
