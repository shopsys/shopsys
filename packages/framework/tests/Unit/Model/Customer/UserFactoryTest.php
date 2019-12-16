<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserFactory;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;

class UserFactoryTest extends TestCase
{
    public function testCreate()
    {
        $customerUserFactory = $this->getUserFactory();

        $deliveryAddress = $this->createDeliveryAddress();
        $customerUserData = new CustomerUserData();
        $customerUserData->firstName = 'firstName';
        $customerUserData->lastName = 'lastName';
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->password = 'pa55w0rd';
        $customerUserData->domainId = 1;

        $customerUser = $customerUserFactory->create($customerUserData, $deliveryAddress);

        $this->assertInstanceOf(CustomerUser::class, $customerUser);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserFactory
     */
    private function getUserFactory(): CustomerUserFactory
    {
        $customerUserPasswordFacade = $this->createMock(CustomerUserPasswordFacade::class);

        return new CustomerUserFactory(new EntityNameResolver([]), $customerUserPasswordFacade);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    private function createDeliveryAddress()
    {
        return new DeliveryAddress(new DeliveryAddressData());
    }
}
