<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;

class UserFactoryTest extends TestCase
{
    public function testCreate()
    {
        $customerUserFactory = $this->getUserFactory();
        $customerUser = $customerUserFactory->create(TestCustomerProvider::getTestCustomerUserData());

        $this->assertInstanceOf(CustomerUser::class, $customerUser);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactory
     */
    private function getUserFactory(): CustomerUserFactory
    {
        $customerUserPasswordFacade = $this->createMock(CustomerUserPasswordFacade::class);

        return new CustomerUserFactory(new EntityNameResolver([]), $customerUserPasswordFacade);
    }
}
