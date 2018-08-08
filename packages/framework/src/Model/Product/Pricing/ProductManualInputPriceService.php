<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceService
{

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFactoryInterface
     */
    protected $productManualInputPriceFactory;

    public function __construct(ProductManualInputPriceFactoryInterface $productManualInputPriceFactory)
    {
        $this->productManualInputPriceFactory = $productManualInputPriceFactory;
    }
    
    public function refresh(Product $product, PricingGroup $pricingGroup, string $inputPrice, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice $productManualInputPrice): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice
    {
        if ($productManualInputPrice === null) {
            $productManualInputPrice = $this->productManualInputPriceFactory->create($product, $pricingGroup, $inputPrice);
        } else {
            $productManualInputPrice->setInputPrice($inputPrice);
        }
        return $productManualInputPrice;
    }
}
