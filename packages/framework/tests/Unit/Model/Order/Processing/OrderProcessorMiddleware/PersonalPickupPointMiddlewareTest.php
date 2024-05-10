<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreData;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class PersonalPickupPointMiddlewareTest extends MiddlewareTestCase
{
    public function testNoChangeWithoutPersonalPickupPointId(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $personalPickupPointMiddleware = $this->createPersonalPickupPointMiddleware();

        $result = $personalPickupPointMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertNull($actualOrderData->personalPickupStore);
        $this->assertNull($actualOrderData->pickupPlaceIdentifier);
    }

    public function testUpdatedDeliveryDataByStore(): void
    {
        $expectedPersonalPickupPoint = 'personalPickupPoint';
        $orderProcessingData = $this->createOrderProcessingData();

        $transport = $this->createMock(Transport::class);
        $transport->method('isPersonalPickup')->willReturn(true);

        $orderProcessingData->orderInput->setTransport($transport);
        $orderProcessingData->orderInput->addAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER, $expectedPersonalPickupPoint);

        $transportData = new OrderItemData();
        $transportData->type = OrderItemTypeEnum::TYPE_TRANSPORT;
        $orderProcessingData->orderData->items[] = $transportData;

        $city = 'city';
        $street = 'street';
        $postcode = '12345';
        $country = $this->createMock(Country::class);

        $storeData = new StoreData();
        $storeData->city = $city;
        $storeData->street = $street;
        $storeData->postcode = $postcode;
        $storeData->country = $country;
        $store = new Store($storeData);

        $personalPickupPointMiddleware = $this->createPersonalPickupPointMiddleware($store);

        $result = $personalPickupPointMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertSame($store, $actualOrderData->personalPickupStore);
        $this->assertSame($expectedPersonalPickupPoint, $actualOrderData->pickupPlaceIdentifier);

        $this->assertFalse($actualOrderData->deliveryAddressSameAsBillingAddress);

        $this->assertSame($street, $actualOrderData->deliveryStreet);
        $this->assertSame($city, $actualOrderData->deliveryCity);
        $this->assertSame($postcode, $actualOrderData->deliveryPostcode);
        $this->assertSame($country, $actualOrderData->deliveryCountry);
    }

    public function testPacketeryIdentifierIsAdded(): void
    {
        $expectedPersonalPickupPoint = 'personalPickupPoint';
        $orderProcessingData = $this->createOrderProcessingData();

        $transport = $this->createMock(Transport::class);
        $transport->method('isPersonalPickup')->willReturn(false);

        $orderProcessingData->orderInput->setTransport($transport);
        $orderProcessingData->orderInput->addAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER, $expectedPersonalPickupPoint);

        $personalPickupPointMiddleware = $this->createPersonalPickupPointMiddleware();

        $result = $personalPickupPointMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertNull($actualOrderData->personalPickupStore);
        $this->assertSame($expectedPersonalPickupPoint, $actualOrderData->pickupPlaceIdentifier);
    }

    public function testNoPersonalPickupPointIsAddedWithoutTransport(): void
    {
        $expectedPersonalPickupPoint = 'personalPickupPoint';
        $orderProcessingData = $this->createOrderProcessingData();

        $orderProcessingData->orderInput->addAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER, $expectedPersonalPickupPoint);

        $personalPickupPointMiddleware = $this->createPersonalPickupPointMiddleware();

        $result = $personalPickupPointMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertNull($actualOrderData->personalPickupStore);
        $this->assertNull($actualOrderData->pickupPlaceIdentifier);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store|null $store
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware
     */
    private function createPersonalPickupPointMiddleware(?Store $store = null): PersonalPickupPointMiddleware
    {
        $storeFacadeMock = $this->createMock(StoreFacade::class);

        if ($store !== null) {
            $storeFacadeMock->method('getByUuidAndDomainId')
                ->willReturn($store);
        }

        return new PersonalPickupPointMiddleware($storeFacadeMock);
    }
}
