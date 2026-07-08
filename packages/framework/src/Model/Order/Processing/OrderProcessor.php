<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;

class OrderProcessor
{
    public function __construct(
        protected readonly OrderProcessingStack $orderProcessingStack,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @template T of \Shopsys\FrameworkBundle\Model\Order\OrderData
     * @param T $orderData
     * @return T
     */
    public function process(
        OrderInput $orderInput,
        OrderData $orderData,
    ): OrderData {
        $orderData = clone $orderData;

        $this->fillCurrencyFields($orderInput, $orderData);

        $orderProcessingData = new OrderProcessingData(
            $orderInput,
            $orderData,
        );

        $this->orderProcessingStack->rewind();

        $orderProcessingData = $this->orderProcessingStack->processNext($orderProcessingData);

        return $orderProcessingData->orderData;
    }

    /**
     * The currency is snapshotted once (already filled currency fields win, e.g. when editing an existing order)
     */
    protected function fillCurrencyFields(OrderInput $orderInput, OrderData $orderData): void
    {
        $domainId = $orderInput->getDomainConfig()->getId();

        if ($orderData->currencyCode === null) {
            $currency = $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainId);
            $orderData->fillCurrencyFieldsFromCurrency($currency);
        } else {
            $currency = $this->currencyFacade->getByCode($orderData->currencyCode);
        }

        $orderData->currencyExchangeRate ??= $this->currencyFacade->getExchangeRateToDomainDefaultCurrency($currency, $domainId);
    }
}
