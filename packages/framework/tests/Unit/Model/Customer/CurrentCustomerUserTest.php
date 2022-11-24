<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CurrentCustomerUserTest extends TestCase
{
    public function testGetPricingGroupForUnregisteredCustomerReturnsDefaultPricingGroup(): void
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $expectedPricingGroup = new PricingGroup($pricingGroupData, 1);

        $tokenStorageMock = $this->createMock(TokenStorage::class);
        $pricingGroupSettingFacadeMock = $this->getPricingGroupSettingFacadeMockReturningDefaultPricingGroup(
            $expectedPricingGroup
        );
        $customerUserFacadeMock = $this->createMock(CustomerUserFacade::class);

        $currentCustomerUser = new CurrentCustomerUser(
            $tokenStorageMock,
            $pricingGroupSettingFacadeMock,
            $customerUserFacadeMock
        );

        $pricingGroup = $currentCustomerUser->getPricingGroup();
        $this->assertSame($expectedPricingGroup, $pricingGroup);
    }

    public function testGetPricingGroupForRegisteredCustomerReturnsHisPricingGroup(): void
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $expectedPricingGroup = new PricingGroup($pricingGroupData, 1);
        $customerUser = $this->getCustomerUserWithPricingGroup($expectedPricingGroup);

        $tokenStorageMock = $this->getTokenStorageMockForCustomerUser($customerUser);
        $pricingGroupFacadeMock = $this->createMock(PricingGroupSettingFacade::class);
        $customerUserFacadeMock = $this->createMock(CustomerUserFacade::class);

        $currentCustomerUser = new CurrentCustomerUser(
            $tokenStorageMock,
            $pricingGroupFacadeMock,
            $customerUserFacadeMock
        );

        $pricingGroup = $currentCustomerUser->getPricingGroup();
        $this->assertSame($expectedPricingGroup, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $defaultPricingGroup
     * @return \PHPUnit\Framework\MockObject\MockObject|\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private function getPricingGroupSettingFacadeMockReturningDefaultPricingGroup(PricingGroup $defaultPricingGroup): \PHPUnit\Framework\MockObject\MockObject|\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
    {
        $pricingGroupSettingFacadeMock = $this->getMockBuilder(PricingGroupSettingFacade::class)
            ->setMethods(['getDefaultPricingGroupByCurrentDomain'])
            ->disableOriginalConstructor()
            ->getMock();

        $pricingGroupSettingFacadeMock
            ->method('getDefaultPricingGroupByCurrentDomain')
            ->willReturn($defaultPricingGroup);

        return $pricingGroupSettingFacadeMock;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    private function getCustomerUserWithPricingGroup(PricingGroup $pricingGroup): \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
    {
        $customerUserData = new CustomerUserData();
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->pricingGroup = $pricingGroup;
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;

        return new CustomerUser($customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    private function getTokenStorageMockForCustomerUser(CustomerUser $customerUser): \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->setMethods(['getUser'])
            ->getMockForAbstractClass();
        $tokenMock->method('getUser')->willReturn($customerUser);

        $tokenStorageMock = $this->getMockBuilder(TokenStorage::class)
            ->setMethods(['getToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        return $tokenStorageMock;
    }
}
