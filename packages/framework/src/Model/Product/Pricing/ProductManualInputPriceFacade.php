<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFactoryInterface $productManualInputPriceFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductManualInputPriceFactoryInterface $productManualInputPriceFactory,
        protected readonly PricingGroupRepository $pricingGroupRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $inputPrice
     */
    protected function refresh(Product $product, PricingGroup $pricingGroup, ?Money $inputPrice): void
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
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Money\Money[]|null[] $manualInputPrices
     */
    public function refreshProductManualInputPrices(Product $product, array $manualInputPrices): void
    {
        foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
            $this->refresh(
                $product,
                $pricingGroup,
                $manualInputPrices[$pricingGroup->getId()],
            );
        }

        $this->em->flush();
    }
}
