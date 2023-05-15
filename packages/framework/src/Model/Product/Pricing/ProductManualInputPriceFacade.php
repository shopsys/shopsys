<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFactoryInterface $productManualInputPriceFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductManualInputPriceFactoryInterface $productManualInputPriceFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $inputPrice
     */
    public function refresh(Product $product, PricingGroup $pricingGroup, ?Money $inputPrice)
    {
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductAndPricingGroup(
            $product,
            $pricingGroup,
        );

        if ($manualInputPrice === null) {
            $manualInputPrice = $this->productManualInputPriceFactory->create($product, $pricingGroup, $inputPrice);
        } else {
            $manualInputPrice->setInputPrice($inputPrice);
        }
        $this->em->persist($manualInputPrice);
        $this->em->flush();
    }
}
