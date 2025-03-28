<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Money\Money;
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
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class ApplyNominalPromoCodeMiddleware extends AbstractPromoCodeMiddleware
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation $discountCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        PromoCodeFacade $promoCodeFacade,
        protected readonly DiscountCalculation $discountCalculation,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly VatFacade $vatFacade,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
        parent::__construct($currentPromoCodeFacade, $promoCodeFacade);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getSupportedTypes(): array
    {
        return [PromoCodeTypeEnum::DISCOUNT_TYPE_NOMINAL];
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
        $totalApplicableProductsPrice = $this->calculateTotalApplicableProductsPrice($orderData, $validProductIds);

        $discountOrderItemData = $this->createDiscountOrderItemData(
            $appliedPromoCode,
            $promoCodeLimit,
            $orderProcessingData->getDomainConfig(),
            $totalApplicableProductsPrice,
        );

        if ($discountOrderItemData === null) {
            return;
        }

        $orderData->addItem($discountOrderItemData);
        $orderData->addTotalPrice($discountOrderItemData->getTotalPrice(), OrderItemTypeEnum::TYPE_DISCOUNT);

        foreach ($orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT) as $productItem) {
            if (in_array($productItem->product->getId(), $validProductIds, true)) {
                $productItem->relatedOrderItemsData[] = $discountOrderItemData;
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalApplicableProductsPrice
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData|null
     */
    protected function createDiscountOrderItemData(
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        DomainConfig $domainConfig,
        PriceInterface $totalApplicableProductsPrice,
    ): ?OrderItemData {
        $locale = $domainConfig->getLocale();
        $domainId = $domainConfig->getId();

        $defaultVat = $this->vatFacade->getDefaultVatForDomain($domainId);

        $discountValue = Money::create($promoCodeLimit->getDiscount());

        $discountPrice = $this->discountCalculation->calculateNominalDiscount(
            $discountValue,
            $totalApplicableProductsPrice,
            (float)$defaultVat->getPercent(),
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId),
        );

        $discountOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_DISCOUNT);

        $discountPrice = $discountPrice->inverse();

        $discountOrderItemData->name = $this->getOrderItemName($locale);
        $discountOrderItemData->quantity = 1;
        $discountOrderItemData->setUnitPrice($discountPrice);
        $discountOrderItemData->setTotalPrice($discountPrice);
        $discountOrderItemData->vatPercent = $defaultVat->getPercent();
        $discountOrderItemData->promoCode = $promoCode;

        return $discountOrderItemData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param array $validProductIds
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    protected function calculateTotalApplicableProductsPrice(
        OrderData $orderData,
        array $validProductIds,
    ): PriceInterface {
        $totalPrice = new Price(Money::zero(), Money::zero());

        foreach ($orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT) as $item) {
            if (in_array($item->product?->getId(), $validProductIds, true)) {
                $totalPrice = $totalPrice->add($item->getTotalPrice());
            }
        }

        return $totalPrice;
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getOrderItemName(string $locale): string
    {
        return sprintf(
            '%s',
            t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
        );
    }
}
