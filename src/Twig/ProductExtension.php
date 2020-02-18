<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation
     */
    private $basePriceCalculation;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     */
    public function __construct(
        BasePriceCalculation $basePriceCalculation
    ) {
        $this->basePriceCalculation = $basePriceCalculation;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        $functions = parent::getFunctions();
        $functions[] = new TwigFunction(
            'getProductNonSellingPrice',
            [$this, 'getProductNonSellingPrice']
        );

        return $functions;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getProductNonSellingPrice(Product $product, int $domainId): ProductPrice
    {
        $highPrice = new Price(Money::zero(), $product->getHighPriceWithVat($domainId) ?? Money::zero());
        $highPrice = $this->basePriceCalculation->applyCoefficients($highPrice, $product->getVatForDomain($domainId), []);
        return new ProductPrice($highPrice, false);
    }
}
