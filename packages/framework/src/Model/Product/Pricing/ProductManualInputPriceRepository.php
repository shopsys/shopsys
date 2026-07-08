<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getProductManualInputPriceRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductManualInputPrice::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice[]
     */
    public function getByProduct(Product $product): array
    {
        return $this->getProductManualInputPriceRepository()->findBy(['product' => $product]);
    }

    public function findByProductPricingGroupAndCurrency(
        Product $product,
        PricingGroup $pricingGroup,
        Currency $currency,
    ): ?ProductManualInputPrice {
        return $this->getProductManualInputPriceRepository()->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup,
            'currency' => $currency,
        ]);
    }
}
