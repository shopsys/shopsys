<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetDeliveryAddressByDeliveryAddressUuidMiddleware;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class SetDeliveryAddressByDeliveryAddressUuidMiddlewareTest extends MiddlewareTestCase
{
    public function testDeliveryAddressIsAdded(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $deliveryAddressUuid = '30a07736-85aa-4407-8a85-4e722ed3e035';
        $customerUser = $this->createCustomerUser();

        $orderProcessingData->orderInput->addAdditionalData(SetDeliveryAddressByDeliveryAddressUuidMiddleware::DELIVERY_ADDRESS_UUID, $deliveryAddressUuid);
        $orderProcessingData->orderInput->setCustomerUser($customerUser);

        $expectedFirstName = 'firstName';
        $expectedLastName = 'lastName';
        $expectedCompanyName = 'companyName';
        $expectedTelephone = '123456789';
        $expectedStreet = 'street';
        $expectedCity = 'city';
        $expectedPostCode = '12312';
        $expectedCountry = $this->createMock(Country::class);

        $deliveryAddressData = new DeliveryAddressData();
        $deliveryAddressData->firstName = $expectedFirstName;
        $deliveryAddressData->lastName = $expectedLastName;
        $deliveryAddressData->companyName = $expectedCompanyName;
        $deliveryAddressData->telephone = $expectedTelephone;
        $deliveryAddressData->street = $expectedStreet;
        $deliveryAddressData->city = $expectedCity;
        $deliveryAddressData->postcode = $expectedPostCode;
        $deliveryAddressData->country = $expectedCountry;

        $deliveryAddress = new DeliveryAddress($deliveryAddressData);

        $createSetDeliveryAddressByDeliveryAddressUuidMiddleware = $this->createSetDeliveryAddressByDeliveryAddressUuidMiddleware(
            $deliveryAddress,
            $deliveryAddressUuid,
            $customerUser,
        );

        $result = $createSetDeliveryAddressByDeliveryAddressUuidMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertSame($expectedFirstName, $actualOrderData->deliveryFirstName);
        $this->assertSame($expectedLastName, $actualOrderData->deliveryLastName);
        $this->assertSame($expectedCompanyName, $actualOrderData->deliveryCompanyName);
        $this->assertSame($expectedTelephone, $actualOrderData->deliveryTelephone);
        $this->assertSame($expectedStreet, $actualOrderData->deliveryStreet);
        $this->assertSame($expectedCity, $actualOrderData->deliveryCity);
        $this->assertSame($expectedPostCode, $actualOrderData->deliveryPostcode);
        $this->assertSame($expectedCountry, $actualOrderData->deliveryCountry);
    }

    /**
     * @param string|null $deliveryAddressUuid
     * @param bool $createCustomerUser
     * @param bool $createDeliveryAddress
     */
    #[DataProvider('noDeliveryIsSetDataProvider')]
    public function testNoDeliveryAddressIsSet(
        ?string $deliveryAddressUuid,
        bool $createCustomerUser,
        bool $createDeliveryAddress,
    ): void {
        $customerUser = null;
        $deliveryAddress = null;

        if ($createCustomerUser) {
            $customerUser = $this->createCustomerUser();
        }

        if ($createDeliveryAddress) {
            $deliveryAddress = $this->createMock(DeliveryAddress::class);
        }

        $orderProcessingData = $this->createOrderProcessingData();

        $orderProcessingData->orderInput->addAdditionalData(SetDeliveryAddressByDeliveryAddressUuidMiddleware::DELIVERY_ADDRESS_UUID, $deliveryAddressUuid);
        $orderProcessingData->orderInput->setCustomerUser($customerUser);

        $createSetDeliveryAddressByDeliveryAddressUuidMiddleware = $this->createSetDeliveryAddressByDeliveryAddressUuidMiddleware(
            $deliveryAddress,
            $deliveryAddressUuid,
            $customerUser,
        );

        $result = $createSetDeliveryAddressByDeliveryAddressUuidMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertNull($actualOrderData->deliveryFirstName);
        $this->assertNull($actualOrderData->deliveryLastName);
        $this->assertNull($actualOrderData->deliveryCompanyName);
        $this->assertNull($actualOrderData->deliveryTelephone);
        $this->assertNull($actualOrderData->deliveryStreet);
        $this->assertNull($actualOrderData->deliveryCity);
        $this->assertNull($actualOrderData->deliveryPostcode);
        $this->assertNull($actualOrderData->deliveryCountry);
    }

    /**
     * @return iterable
     */
    public static function noDeliveryIsSetDataProvider(): iterable
    {
        yield 'no delivery address uuid' => [
            null,
            true,
            true,
        ];

        yield 'no customer user' => [
            '234f5474-8c84-47c6-8713-21cfc0a32ade',
            false,
            true,
        ];

        yield 'no delivery address' => [
            '86caffe0-c926-4cdf-8e03-7458e22422e5',
            true,
            false,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @param string|null $deliveryAddressUuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetDeliveryAddressByDeliveryAddressUuidMiddleware
     */
    private function createSetDeliveryAddressByDeliveryAddressUuidMiddleware(
        ?DeliveryAddress $deliveryAddress,
        ?string $deliveryAddressUuid,
        ?CustomerUser $customerUser,
    ): SetDeliveryAddressByDeliveryAddressUuidMiddleware {
        $deliveryAddressFacadeMock = $this->createMock(DeliveryAddressFacade::class);

        $deliveryAddressFacadeMock
            ->method('findByUuidAndCustomer')
            ->with($deliveryAddressUuid, $customerUser?->getCustomer())
            ->willReturn($deliveryAddress);

        return new SetDeliveryAddressByDeliveryAddressUuidMiddleware($deliveryAddressFacadeMock);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    private function createCustomerUser(): CustomerUser
    {
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->method('getCustomer')->willReturn($this->createMock(Customer::class));

        return $customerUser;
    }
}
