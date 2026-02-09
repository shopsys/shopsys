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
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductManualInputPriceFactory $productManualInputPriceFactory,
        protected readonly PricingGroupRepository $pricingGroupRepository,
    ) {
    }

    protected function refresh(Product $product, PricingGroup $pricingGroup, ?Money $inputPrice): void
    {
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductAndPricingGroup(
            $product,
            $pricingGroup,
        );

        if ($manualInputPrice === null) {
            $manualInputPrice = $this->productManualInputPriceFactory->create($product, $pricingGroup, $inputPrice);
            $this->em->persist($manualInputPrice);
        } else {
            $manualInputPrice->setInputPrice($inputPrice);
        }
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Product\ProductInputPriceData> $productInputPriceDataByDomain
     */
    public function refreshProductManualInputPrices(Product $product, array $productInputPriceDataByDomain): void
    {
        foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
            if (!array_key_exists($pricingGroup->getId(), $productInputPriceDataByDomain[$pricingGroup->getDomainId()]->manualInputPricesByPricingGroupId)) {
                continue;
            }

            $this->refresh(
                $product,
                $pricingGroup,
                $productInputPriceDataByDomain[$pricingGroup->getDomainId()]->manualInputPricesByPricingGroupId[$pricingGroup->getId()],
            );
        }

        $this->em->flush();
    }
}
