<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;

class PersonalPickupPointMiddleware implements OrderProcessorMiddlewareInterface
{
    public const string ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER = 'pickupPlaceIdentifier';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     */
    public function __construct(
        protected readonly StoreFacade $storeFacade,
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
        $pickupPlaceIdentifier = $orderProcessingData->orderInput->findAdditionalData(static::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER);

        $orderData = $orderProcessingData->orderData;
        $transport = $orderProcessingData->orderInput->getTransport();

        if ($pickupPlaceIdentifier === null || $transport === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        if ($transport->isPersonalPickup()) {
            try {
                $store = $this->storeFacade->getByUuidAndDomainId(
                    $pickupPlaceIdentifier,
                    $orderProcessingData->getDomainId(),
                );

                $orderData->getItemsByType(OrderItemTypeEnum::TYPE_TRANSPORT)[0]->name .= ' ' . $store->getName();

                $this->updateDeliveryDataByStore($orderData, $store);
            } catch (StoreByUuidNotFoundException) {
                // not existing store is reported by TransportAndPaymentWatcherFacade
            }
        }

        $orderData->pickupPlaceIdentifier = $pickupPlaceIdentifier;

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     */
    protected function updateDeliveryDataByStore(OrderData $orderData, Store $store): void
    {
        $orderData->personalPickupStore = $store;
        $orderData->deliveryAddressSameAsBillingAddress = false;

        $orderData->deliveryFirstName = $orderData->deliveryFirstName ?? $orderData->firstName;
        $orderData->deliveryLastName = $orderData->deliveryLastName ?? $orderData->lastName;
        $orderData->deliveryCompanyName = $orderData->deliveryCompanyName ?? $orderData->companyName;
        $orderData->deliveryTelephone = $orderData->deliveryTelephone ?? $orderData->telephone;

        $orderData->deliveryStreet = $store->getStreet();
        $orderData->deliveryCity = $store->getCity();
        $orderData->deliveryPostcode = $store->getPostcode();
        $orderData->deliveryCountry = $store->getCountry();
    }
}
