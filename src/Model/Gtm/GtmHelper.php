<?php

declare(strict_types = 1);

namespace App\Model\Gtm;

use App\Model\Order\Item\OrderItem;
use App\Model\Order\OrderData;
use App\Model\Order\PromoCode\PromoCode;
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
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \App\Model\Gtm\GtmContainer $gtmContainer
     */
    public function __construct(NumberFormatterExtension $numberFormatterExtension, GtmContainer $gtmContainer)
    {
        $this->numberFormatterExtension = $numberFormatterExtension;
        $this->gtmContainer = $gtmContainer;
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

        $orderData->gtmCoupon = sprintf(
            '%s|%s',
            $usedPromoCode->getCode(),
            $this->numberFormatterExtension->formatPercent($usedPromoCode->getPercent())
        );
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
