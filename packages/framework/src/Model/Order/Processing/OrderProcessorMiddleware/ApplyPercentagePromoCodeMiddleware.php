<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class ApplyPercentagePromoCodeMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation $discountCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly NumberFormatterExtension $numberFormatterExtension,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly DiscountCalculation $discountCalculation,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $appliedPromoCodes = $orderProcessingData->orderInput->getPromoCodes();

        $orderData = $orderProcessingData->orderData;

        foreach ($appliedPromoCodes as $appliedPromoCode) {
            if ($appliedPromoCode->getDiscountType() !== PromoCode::DISCOUNT_TYPE_PERCENT) {
                continue;
            }

            $products = array_map(
                static fn (OrderItemData $orderItemData) => $orderItemData->product,
                $orderData->getItemsByType(OrderItem::TYPE_PRODUCT),
            );

            try {
                $validProductIds = $this->currentPromoCodeFacade->validatePromoCode(
                    $appliedPromoCode,
                    $orderData->totalPricesByItemType[OrderItem::TYPE_PRODUCT],
                    $products,
                );

                $promoCodeLimit = $this->promoCodeFacade->getHighestLimitByPromoCodeAndTotalPrice($appliedPromoCode, $orderData->totalPricesByItemType[OrderItem::TYPE_PRODUCT]);
            } catch (PromoCodeException) {
                continue;
            }

            foreach ($orderData->getItemsByType(OrderItem::TYPE_PRODUCT) as $productItem) {
                if (!in_array($productItem->product->getId(), $validProductIds, true)) {
                    continue;
                }

                $discountOrderItemData = $this->createDiscountOrderItemData(
                    $appliedPromoCode,
                    $promoCodeLimit,
                    $productItem,
                    $orderProcessingData->getDomainConfig(),
                );

                if ($discountOrderItemData === null) {
                    continue;
                }

                $orderData->addItem($discountOrderItemData);
                $orderData->subtractTotalPrice($discountOrderItemData->getTotalPrice(), OrderItem::TYPE_DISCOUNT);
            }
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $productItem
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData|null
     */
    protected function createDiscountOrderItemData(
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        OrderItemData $productItem,
        DomainConfig $domainConfig,
    ): ?OrderItemData {
        $locale = $domainConfig->getLocale();
        $domainId = $domainConfig->getId();

        $discountPrice = $this->discountCalculation->calculatePercentageDiscountRoundedByCurrency(
            $productItem->getTotalPrice(),
            (float)$productItem->vatPercent,
            (float)$promoCodeLimit->getDiscount(),
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId),
        );

        if ($discountPrice === null) {
            return null;
        }

        $discountOrderItemData = $this->orderItemDataFactory->create(OrderItem::TYPE_DISCOUNT);

        $name = sprintf(
            '%s -%s - %s',
            t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
            $this->numberFormatterExtension->formatPercent($promoCodeLimit->getDiscount(), $locale),
            $productItem->name,
        );

        $discountOrderItemData->name = $name;
        $discountOrderItemData->quantity = 1;
        $discountOrderItemData->setUnitPrice($discountPrice->inverse());
        $discountOrderItemData->setTotalPrice($discountPrice);
        $discountOrderItemData->vatPercent = $productItem->vatPercent;
        $discountOrderItemData->promoCode = $promoCode;

        return $discountOrderItemData;
    }
}
