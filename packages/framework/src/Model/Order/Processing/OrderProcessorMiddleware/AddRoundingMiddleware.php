<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class AddRoundingMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     */
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Rounding $rounding,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;

        $payment = $orderData->payment;

        if ($payment === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($orderProcessingData->getDomainId());

        $roundingPrice = $this->orderPriceCalculation->calculateOrderRoundingPrice($payment, $currency, $orderData->totalPrice);

        if ($roundingPrice !== null && !$roundingPrice->isZero()) {
            $orderData->addItem($this->createRoundingItemData($roundingPrice, $orderProcessingData->getDomainConfig()));
            $orderData->addTotalPrice($roundingPrice, OrderItemTypeEnum::TYPE_ROUNDING);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $roundingPrice
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    protected function createRoundingItemData(PriceInterface $roundingPrice, DomainConfig $domainConfig): OrderItemData
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
