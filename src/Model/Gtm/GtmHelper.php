<?php

declare(strict_types = 1);

namespace App\Model\Gtm;

use App\Model\Cart\CartFacade;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\OrderData;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class GtmHelper
{
    /**
     * @var \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension
     */
    private $numberFormatterExtension;

    /**
     * @var \App\Model\Gtm\GtmContainer
     */
    private $gtmContainer;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitResolver
     */
    private $promoCodeLimitByCartTotalResolver;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \App\Model\Gtm\GtmContainer $gtmContainer
     * @param \App\Model\Order\PromoCode\PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver
     * @param \App\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        NumberFormatterExtension $numberFormatterExtension,
        GtmContainer $gtmContainer,
        PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver,
        CartFacade $cartFacade
    ) {
        $this->numberFormatterExtension = $numberFormatterExtension;
        $this->gtmContainer = $gtmContainer;
        $this->promoCodeLimitByCartTotalResolver = $promoCodeLimitByCartTotalResolver;
        $this->cartFacade = $cartFacade;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\PromoCode\PromoCode|null $usedPromoCode
     */
    public function amendGtmCouponToOrderData(OrderData $orderData, ?PromoCode $usedPromoCode): void
    {
        if ($usedPromoCode === null) {
            return;
        }

        $limit = $this->promoCodeLimitByCartTotalResolver->getLimitByPromoCode(
            $usedPromoCode,
            $this->cartFacade->getQuantifiedProductsOfCurrentCustomer()
        );
        if ($limit === null) {
            return;
        }

        $orderData->gtmCoupon = $usedPromoCode->getCode();
    }

    /**
     * @param \App\Model\Order\Item\OrderItem $orderItem
     * @return string
     */
    public function getGtmAvailabilityByOrderItem(OrderItem $orderItem): string
    {
        if (!$orderItem->isTypeProduct() || $orderItem->getProduct() === null) {
            return '';
        }

        $availability = $orderItem->getProduct()->getCalculatedAvailability();
        $availabilityName = $availability->getName($this->gtmContainer->getDataLayer()->getLocale());

        return mb_strtolower($availabilityName);
    }
}
