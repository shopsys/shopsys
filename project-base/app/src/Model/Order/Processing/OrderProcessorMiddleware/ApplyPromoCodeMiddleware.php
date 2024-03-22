<?php

declare(strict_types=1);

namespace App\Model\Order\Processing\OrderProcessorMiddleware;

use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Product\Pricing\QuantifiedProductDiscountCalculation;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStackInterface;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;

class ApplyPromoCodeMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        private readonly QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation,
        private readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStackInterface $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(OrderProcessingData $orderProcessingData, OrderProcessingStackInterface $orderProcessingStack): OrderProcessingData
    {
        $quantifiedProducts = $orderProcessingData->cart->getQuantifiedProducts();

        /** @var \App\Model\Cart\Cart $cart */
        $cart = $orderProcessingData->cart;

        $appliedPromoCodes = $cart->getAllAppliedPromoCodes();

        foreach ($appliedPromoCodes as $appliedPromoCode) {
            $domainId = $orderProcessingData->domainConfig->getId();
            $promoCodePerProduct = $this->currentPromoCodeFacade->getPromoCodePerProductByDomainId(
                $quantifiedProducts,
                $domainId,
                $appliedPromoCode,
            );

            d($promoCodePerProduct);
        }

        return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);

        $promoCodePerProduct = $this->currentPromoCodeFacade->getPromoCodePerProductByDomainId($quantifiedProducts, $domainId, $promoCode);

        $quantifiedItemsDiscounts = $this->quantifiedProductDiscountCalculation->calculateDiscountsPerProductRoundedByCurrency(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $promoCodePerProduct,
            $currency,
        );

        $quantifiedItemsDiscountPrices = $this->quantifiedProductDiscountCalculation->calculateDiscountPricesPerProductRoundedByCurrency(
            $quantifiedItemsPrices,
            $quantifiedItemsDiscounts,
            $currency,
        );

        return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);
    }
}
