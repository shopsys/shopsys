<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Order\FrontOrderData as BaseFrontOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderDataMapper as BaseOrderDataMapper;

/**
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 */
class OrderDataMapper extends BaseOrderDataMapper
{
    /**
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     */
    public function __construct(OrderDataFactoryInterface $orderDataFactory)
    {
        parent::__construct($orderDataFactory);
    }

    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @return \App\Model\Order\OrderData
     */
    public function getOrderDataFromFrontOrderData(BaseFrontOrderData $frontOrderData): \Shopsys\FrameworkBundle\Model\Order\OrderData
    {
        /** @var \App\Model\Order\OrderData $orderData */
        $orderData = parent::getOrderDataFromFrontOrderData($frontOrderData);
        $orderData->transport = $frontOrderData->transport;
        $orderData->password = $frontOrderData->password;
        $orderData->isCompanyCustomer = $frontOrderData->companyCustomer;

        if ($frontOrderData->personalPickupStore !== null) {
            $orderData->deliveryAddressSameAsBillingAddress = false;
            $orderData->personalPickupStore = $frontOrderData->personalPickupStore;
            $orderData->pickupPlaceIdentifier = $frontOrderData->personalPickupStore->getUuid();
            $this->setOrderDeliveryAddressDataByStore($orderData, $frontOrderData, $orderData->personalPickupStore);
        }

        return $orderData;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @param \App\Model\Store\Store $store
     */
    private function setOrderDeliveryAddressDataByStore(
        OrderData $orderData,
        FrontOrderData $frontOrderData,
        Store $store,
    ): void {
        $orderData->deliveryFirstName = $frontOrderData->firstName;
        $orderData->deliveryLastName = $frontOrderData->lastName;
        $orderData->deliveryCompanyName = $frontOrderData->companyName;
        $orderData->deliveryTelephone = $frontOrderData->telephone;

        $orderData->deliveryStreet = $store->getStreet();
        $orderData->deliveryCity = $store->getCity();
        $orderData->deliveryPostcode = $store->getPostcode();
        $orderData->deliveryCountry = $store->getCountry();
    }
}
