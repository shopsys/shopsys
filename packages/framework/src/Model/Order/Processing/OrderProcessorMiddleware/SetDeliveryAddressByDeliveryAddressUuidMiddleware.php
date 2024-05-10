<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;

class SetDeliveryAddressByDeliveryAddressUuidMiddleware implements OrderProcessorMiddlewareInterface
{
    public const string DELIVERY_ADDRESS_UUID = 'deliveryAddressUuid';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     */
    public function __construct(
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
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
        $deliveryAddressUuid = $orderProcessingData->orderInput->findAdditionalData(static::DELIVERY_ADDRESS_UUID);

        $customerUser = $orderProcessingData->orderInput->getCustomerUser();

        if ($deliveryAddressUuid === null || $customerUser === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $deliveryAddress = $this->deliveryAddressFacade->findByUuidAndCustomer(
            $deliveryAddressUuid,
            $customerUser->getCustomer(),
        );

        if ($deliveryAddress !== null) {
            $this->setOrderDataDeliveryFieldsByDeliveryAddress($deliveryAddress, $orderProcessingData->orderData);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $deliveryAddress
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function setOrderDataDeliveryFieldsByDeliveryAddress(
        DeliveryAddress $deliveryAddress,
        OrderData $orderData,
    ): void {
        $orderData->deliveryFirstName = $deliveryAddress->getFirstName();
        $orderData->deliveryLastName = $deliveryAddress->getLastName();
        $orderData->deliveryCompanyName = $deliveryAddress->getCompanyName();
        $orderData->deliveryTelephone = $deliveryAddress->getTelephone();
        $orderData->deliveryStreet = $deliveryAddress->getStreet();
        $orderData->deliveryCity = $deliveryAddress->getCity();
        $orderData->deliveryPostcode = $deliveryAddress->getPostcode();
        $orderData->deliveryCountry = $deliveryAddress->getCountry();
    }
}
