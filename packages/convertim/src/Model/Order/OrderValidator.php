<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Convertim\Order\ConvertimOrderData;
use Convertim\Order\ConvertimValidationResult;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;

class OrderValidator
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Order\ConvertimOrderDataToCartMapper $convertimOrderDataToCartMapper
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        protected readonly ConvertimOrderDataToCartMapper $convertimOrderDataToCartMapper,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly CartFacade $cartFacade,
    ) {
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @return \Convertim\Order\ConvertimValidationResult
     */
    public function validateOrder(ConvertimOrderData $convertimOrderData): ConvertimValidationResult
    {
        $cart = $this->convertimOrderDataToCartMapper->mapConvertimOrderDataToCart($convertimOrderData);

        $validationResult = new ConvertimValidationResult();

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);

        if ($cartWithModifications->isCartModified()) {
            $modifications = $cartWithModifications->getModifications();

            $this->addCartItemModificationsToValidationResult($modifications['itemModifications'], $validationResult);
            $this->addTransportModificationsToValidationResult($modifications['transportModifications'], $validationResult);
            $this->addPaymentModificationsToValidationResult($modifications['paymentModifications'], $validationResult);
            $this->addPromoCodeModificationsToValidationResult($modifications['promoCodeModifications'], $validationResult);
            $this->addMultipleAddedProductModificationsToValidationResult($modifications['multipleAddedProductModifications'], $validationResult);
        }

        $this->cartFacade->deleteCart($cart);

        return $validationResult;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[] $noLongerListableCartItems
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addNoLongerListableCartItemsToValidationResult(
        array $noLongerListableCartItems,
        ConvertimValidationResult $validationResult,
    ): void {
        foreach ($noLongerListableCartItems as $noLongerListableCartItem) {
            $validationResult->addError(
                t(
                    'Item {{ name }} can no longer be bought.',
                    [
                        '{{ name }}' => $noLongerListableCartItem->getProduct()->getName(),
                    ],
                ),
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[] $cartItemsWithModifiedPrice
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addCartItemsWithModifiedPriceToValidationResult(
        array $cartItemsWithModifiedPrice,
        ConvertimValidationResult $validationResult,
    ): void {
        foreach ($cartItemsWithModifiedPrice as $cartItemWithModifiedPrice) {
            $validationResult->addError(
                t(
                    'The price of item {{ name }} has changed.',
                    [
                        '{{ name }}' => $cartItemWithModifiedPrice->getProduct()->getName(),
                    ],
                ),
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[] $cartItemsWithChangedQuantity
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addCartItemsWithChangedQuantityToValidationResult(
        array $cartItemsWithChangedQuantity,
        ConvertimValidationResult $validationResult,
    ): void {
        foreach ($cartItemsWithChangedQuantity as $cartItemWithChangedQuantity) {
            $validationResult->addError(
                t(
                    'The quantity of item {{ name }} has changed.',
                    [
                        '{{ name }}' => $cartItemWithChangedQuantity->getProduct()->getName(),
                    ],
                ),
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[] $noLongerAvailableCartItemsDueToQuantity
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addNoLongerAvailableCartItemsDueToQuantity(
        array $noLongerAvailableCartItemsDueToQuantity,
        ConvertimValidationResult $validationResult,
    ): void {
        foreach ($noLongerAvailableCartItemsDueToQuantity as $noLongerAvailableCartItemDueToQuantity) {
            $validationResult->addError(
                t(
                    'Item {{ name }} has been sold out.',
                    [
                        '{{ name }}' => $noLongerAvailableCartItemDueToQuantity->getProduct()->getName(),
                    ],
                ),
            );
        }
    }

    /**
     * @param array<string, array> $itemModifications
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addCartItemModificationsToValidationResult(
        array $itemModifications,
        ConvertimValidationResult $validationResult,
    ): void {
        if (count($itemModifications) > 0) {
            $this->addNoLongerListableCartItemsToValidationResult($itemModifications['noLongerListableCartItems'], $validationResult);
            $this->addCartItemsWithModifiedPriceToValidationResult($itemModifications['cartItemsWithModifiedPrice'], $validationResult);
            $this->addCartItemsWithChangedQuantityToValidationResult($itemModifications['cartItemsWithChangedQuantity'], $validationResult);
            $this->addNoLongerAvailableCartItemsDueToQuantity($itemModifications['noLongerAvailableCartItemsDueToQuantity'], $validationResult);
        }
    }

    /**
     * @param array<string, bool> $transportModifications
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addTransportModificationsToValidationResult(
        array $transportModifications,
        ConvertimValidationResult $validationResult,
    ): void {
        if (count($transportModifications) > 0) {
            if ($transportModifications['transportPriceChanged']) {
                $validationResult->addError(t('The price of the transport you selected has changed.'));
            }

            if ($transportModifications['transportUnavailable']) {
                $validationResult->addError(t('The transport you selected is no longer available.'));
            }

            if ($transportModifications['transportWeightLimitExceeded']) {
                $validationResult->addError(t('You have exceeded the weight limit of the selected transport.'));
            }

            if ($transportModifications['personalPickupStoreUnavailable']) {
                $validationResult->addError(t('The store you selected is no longer available.'));
            }
        }
    }

    /**
     * @param array<string, bool> $paymentModifications
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addPaymentModificationsToValidationResult(
        array $paymentModifications,
        ConvertimValidationResult $validationResult,
    ): void {
        if (count($paymentModifications) > 0) {
            if ($paymentModifications['paymentPriceChanged']) {
                $validationResult->addError(t('The price of the payment you selected has changed.'));
            }

            if ($paymentModifications['paymentUnavailable']) {
                $validationResult->addError(t('The payment you selected is no longer available.'));
            }
        }
    }

    /**
     * @param array<string, array<int, string>> $promoCodeModifications
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addPromoCodeModificationsToValidationResult(
        array $promoCodeModifications,
        ConvertimValidationResult $validationResult,
    ): void {
        if (count($promoCodeModifications) > 0) {
            /** @var string $noLongerApplicablePromoCode */
            foreach ($promoCodeModifications['noLongerApplicablePromoCode'] as $noLongerApplicablePromoCode) {
                $validationResult->addError(t(
                    'The promo code {{ code }} is no longer applicable.',
                    [
                        '{{ code }}' => $noLongerApplicablePromoCode,
                    ],
                ));
            }
        }
    }

    /**
     * @param array<string, array<int, string>> $multipleAddedProductModifications
     * @param \Convertim\Order\ConvertimValidationResult $validationResult
     */
    protected function addMultipleAddedProductModificationsToValidationResult(
        array $multipleAddedProductModifications,
        ConvertimValidationResult $validationResult,
    ): void {
        if (count($multipleAddedProductModifications) > 0) {
            /** @var \Shopsys\FrameworkBundle\Model\Product\Product $notAddedProduct */
            foreach ($multipleAddedProductModifications['notAddedProducts'] as $notAddedProduct) {
                $validationResult->addError(t(
                    'The product {{ name }} is not available.',
                    [
                        '{{ name }}' => $notAddedProduct->getName(),
                    ],
                ));
            }
        }
    }
}
