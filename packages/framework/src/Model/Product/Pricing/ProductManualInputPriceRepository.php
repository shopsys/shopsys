<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductManualInputPriceRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductManualInputPrice::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return object[]
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
    public function findByProductAndPricingGroup(Product $product, PricingGroup $pricingGroup): ?ProductManualInputPrice
    {
        return $this->getProductManualInputPriceRepository()->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup,
        ]);
    }
}
