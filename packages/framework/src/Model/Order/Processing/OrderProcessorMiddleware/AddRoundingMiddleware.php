<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class AddRoundingMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Rounding $rounding,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
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
        $orderData = $orderProcessingData->orderData;

        $payment = $orderData->payment;
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($orderProcessingData->getDomainId());

        if (!$payment?->isCzkRounding() || $currency->getCode() !== Currency::CODE_CZK) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $priceWithVat = $orderData->totalPrice->getPriceWithVat();
        $roundedPriceWithVat = $priceWithVat->round(0);

        $roundingPriceAmount = $this->rounding->roundPriceWithVatByCurrency(
            $roundedPriceWithVat->subtract($priceWithVat),
            $currency,
        );

        $roundingPrice = new Price($roundingPriceAmount, $roundingPriceAmount);

        if (!$roundingPrice->isZero()) {
            $orderData->addItem($this->createRoundingItemData($roundingPrice, $orderProcessingData->getDomainConfig()));
            $orderData->addTotalPrice($roundingPrice, OrderItemTypeEnum::TYPE_ROUNDING);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $roundingPrice
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    protected function createRoundingItemData(Price $roundingPrice, DomainConfig $domainConfig): OrderItemData
    {
        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_ROUNDING);

        $orderItemData->setUnitPrice($roundingPrice);
        $orderItemData->setTotalPrice($roundingPrice);
        $orderItemData->vatPercent = '0';
        $orderItemData->name = t('Rounding', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainConfig->getLocale());
        $orderItemData->quantity = 1;

        return $orderItemData;
    }
}
