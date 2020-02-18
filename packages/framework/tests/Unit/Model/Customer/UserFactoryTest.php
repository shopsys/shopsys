<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;

class UserFactoryTest extends TestCase
{
    public function testCreate()
    {
        $customerUserFactory = $this->getUserFactory();

        $customerUserData = new CustomerUserData();
        $customerUserData->firstName = 'firstName';
        $customerUserData->lastName = 'lastName';
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->password = 'pa55w0rd';
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;

        $customerUser = $customerUserFactory->create($customerUserData);

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
