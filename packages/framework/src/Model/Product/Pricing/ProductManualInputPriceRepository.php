<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductManualInputPriceRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(ProductManualInputPrice::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice[]
     */
    public function getByProduct(Product $product): array
    {
        return $this->getProductManualInputPriceRepository()->findBy(['product' => $product]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice|null
     */
    public function findByProductAndPricingGroup(Product $product, PricingGroup $pricingGroup): ?\Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice
    {
        return $this->getProductManualInputPriceRepository()->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup,
        ]);
    }
}
