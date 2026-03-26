<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class ApplyPercentagePromoCodeMiddleware extends AbstractPromoCodeMiddleware
{
    public function __construct(
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        PromoCodeFacade $promoCodeFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly DiscountCalculation $discountCalculation,
        protected readonly NumberFormatterExtension $numberFormatterExtension,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
        parent::__construct($currentPromoCodeFacade, $promoCodeFacade);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getSupportedTypes(): array
    {
        return [PromoCodeTypeEnum::DISCOUNT_TYPE_PERCENT];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createAndAddOrderItemData(
        OrderData $orderData,
        array $validProductIds,
        PromoCode $appliedPromoCode,
        PromoCodeLimit $promoCodeLimit,
        OrderProcessingData $orderProcessingData,
    ): void {
        foreach ($orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT) as $productItem) {
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

            $productItem->relatedOrderItemsData[] = $discountOrderItemData;

            $orderData->addItem($discountOrderItemData);
            $orderData->addTotalPrice($discountOrderItemData->getTotalPrice(), OrderItemTypeEnum::TYPE_DISCOUNT);
        }
    }

    protected function createDiscountOrderItemData(
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        OrderItemData $productItem,
        DomainConfig $domainConfig,
    ): ?OrderItemData {
        $locale = $domainConfig->getLocale();
        $domainId = $domainConfig->getId();

        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $discountPrice = $this->discountCalculation->calculatePercentageDiscountRoundedByCurrency(
            $this->getItemTotalPriceWithAppliedPromotions($productItem),
            (float)$productItem->vatPercent,
            (float)$promoCodeLimit->getDiscount(),
            $currency->getRoundingType(),
            $currency->getRoundingPlacesPriceWithoutVat(),
        );

        if ($discountPrice === null) {
            return null;
        }

        $discountOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_DISCOUNT);

        $discountPrice = $discountPrice->inverse();

        $discountOrderItemData->name = $this->getOrderItemName($locale, $promoCodeLimit, $productItem);
        $discountOrderItemData->quantity = 1;
        $discountOrderItemData->setUnitPrice($discountPrice);
        $discountOrderItemData->setTotalPrice($discountPrice);
        $discountOrderItemData->vatPercent = $productItem->vatPercent;
        $discountOrderItemData->promoCode = $promoCode;

        return $discountOrderItemData;
    }

    protected function getOrderItemName(
        string $locale,
        PromoCodeLimit $promoCodeLimit,
        OrderItemData $productItem,
    ): string {
        return sprintf(
            '%s -%s - %s',
            t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
            $this->numberFormatterExtension->formatPercent($promoCodeLimit->getDiscount(), $locale),
            $productItem->name,
        );
    }

    protected function getItemTotalPriceWithAppliedPromotions(OrderItemData $item): PriceInterface
    {
        $totalPrice = $item->getTotalPrice();

        foreach ($item->relatedOrderItemsData as $itemData) {
            if ($itemData->type !== OrderItemTypeEnum::TYPE_PROMOTION) {
                continue;
            }

            $totalDiscountPrice = new Price(
                $itemData->totalPriceWithoutVat,
                $itemData->totalPriceWithVat,
            );

            $totalPrice = $totalPrice->add($totalDiscountPrice);
        }

        return $totalPrice;
    }
}
