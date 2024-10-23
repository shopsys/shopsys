<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Convertim\Order\ConvertimOrderData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;

class OrderFacade
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Order\ConvertimOrderDataToOrderDataMapper $convertimOrderDataToOrderMapper
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade $placeOrderFacade
     */
    public function __construct(
        protected readonly ConvertimOrderDataToOrderDataMapper $convertimOrderDataToOrderMapper,
        protected readonly Domain $domain,
        protected readonly PlaceOrderFacade $placeOrderFacade,
    ) {
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function saveOrder(ConvertimOrderData $convertimOrderData): Order
    {
        $orderData = $this->convertimOrderDataToOrderMapper->mapConvertimOrderDataToOrderData($convertimOrderData);

        //        d($orderData);

        $deliveryAddressUuid = $convertimOrderData->getCustomerData()->getConvertimCustomerDeliveryAddressData()->getUuid();


        //        d($orderData->items);

        $order = $this->placeOrderFacade->placeOrder($orderData, $deliveryAddressUuid);


        return $order;
    }
}
