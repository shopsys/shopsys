<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Customer\UserData;
use Shopsys\FrameworkBundle\Model\Customer\UserFactory;
use Shopsys\FrameworkBundle\Model\Customer\UserPasswordFacade;

class UserFactoryTest extends TestCase
{
    public function testCreate()
    {
        $customerService = $this->getUserFactory();

        $deliveryAddress = $this->createDeliveryAddress();
        $userData = new UserData();
        $userData->firstName = 'firstName';
        $userData->lastName = 'lastName';
        $userData->email = 'no-reply@shopsys.com';
        $userData->password = 'pa55w0rd';
        $userData->domainId = 1;

        $user = $customerService->create($userData, $deliveryAddress);

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\UserFactory
     */
    private function getUserFactory(): UserFactory
    {
        $userPasswordFacade = $this->createMock(UserPasswordFacade::class);

        return new UserFactory(new EntityNameResolver([]), $userPasswordFacade);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    private function createDeliveryAddress()
    {
        return new DeliveryAddress(new DeliveryAddressData());
    }
}
