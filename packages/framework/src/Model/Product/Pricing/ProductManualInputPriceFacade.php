<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
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
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    protected function refresh(
        Product $product,
        PricingGroup $pricingGroup,
        Currency $currency,
        ?Money $inputPrice,
    ): void {
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductPricingGroupAndCurrency(
            $product,
            $pricingGroup,
            $currency,
        );

        if ($manualInputPrice === null) {
            $manualInputPrice = $this->productManualInputPriceFactory->create($product, $pricingGroup, $currency, $inputPrice);
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
            $manualInputPricesByCurrencyCode = $productInputPriceDataByDomain[$pricingGroup->getDomainId()]->manualInputPricesByPricingGroupIdAndCurrencyCode[$pricingGroup->getId()] ?? null;

            if ($manualInputPricesByCurrencyCode === null) {
                continue;
            }

            foreach ($this->currencyFacade->getEnabledCurrenciesByDomainId($pricingGroup->getDomainId()) as $currency) {
                if (!array_key_exists($currency->getCode(), $manualInputPricesByCurrencyCode)) {
                    continue;
                }

                $this->refresh(
                    $product,
                    $pricingGroup,
                    $currency,
                    $manualInputPricesByCurrencyCode[$currency->getCode()],
                );
            }
        }

        $this->em->flush();
    }
}
