<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Tests\FrameworkBundle\Unit\Model\Customer\Mock\TokenMock;

class CurrentCustomerUserTest extends TestCase
{
    public function testGetPricingGroupForUnregisteredCustomerReturnsDefaultPricingGroup(): void
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $expectedPricingGroup = new PricingGroup($pricingGroupData, 1);

        $tokenStorageStub = $this->createStub(TokenStorage::class);
        $pricingGroupSettingFacadeStub = $this->getPricingGroupSettingFacadeStubReturningDefaultPricingGroup(
            $expectedPricingGroup,
        );
        $customerUserFacadeStub = $this->createStub(CustomerUserFacade::class);
        $inMemoryCacheStub = $this->createStub(InMemoryCache::class);

        $currentCustomerUser = new CurrentCustomerUser(
            $tokenStorageStub,
            $pricingGroupSettingFacadeStub,
            $customerUserFacadeStub,
            $inMemoryCacheStub,
        );

        $pricingGroup = $currentCustomerUser->getPricingGroup();
        $this->assertSame($expectedPricingGroup, $pricingGroup);
    }

    public function testGetPricingGroupForRegisteredCustomerReturnsHisPricingGroup(): void
    {
        $customerUser = TestCustomerProvider::getTestCustomerUser();
        $expectedPricingGroup = $customerUser->getPricingGroup();

        $tokenStorageStub = $this->getTokenStorageStubForCustomerUser($customerUser);
        $pricingGroupFacadeStub = $this->createStub(PricingGroupSettingFacade::class);
        $customerUserFacadeStub = $this->createStub(CustomerUserFacade::class);
        $inMemoryCacheStub = $this->createStub(InMemoryCache::class);

        $currentCustomerUser = new CurrentCustomerUser(
            $tokenStorageStub,
            $pricingGroupFacadeStub,
            $customerUserFacadeStub,
            $inMemoryCacheStub,
        );

        $pricingGroup = $currentCustomerUser->getPricingGroup();
        $this->assertSame($expectedPricingGroup, $pricingGroup);
    }

    private function getPricingGroupSettingFacadeStubReturningDefaultPricingGroup(
        PricingGroup $defaultPricingGroup,
    ): PricingGroupSettingFacade {
        $pricingGroupSettingFacadeStub = $this->createStub(PricingGroupSettingFacade::class);

        $pricingGroupSettingFacadeStub
            ->method('getDefaultPricingGroupByCurrentDomain')
            ->willReturn($defaultPricingGroup);

        return $pricingGroupSettingFacadeStub;
    }

    private function getTokenStorageStubForCustomerUser(
        CustomerUser $customerUser,
    ): TokenStorage {
        /**
         * Until version 6 of symfony, the TokenInterface mock needs to be mocked manually.
         * The function getUserIdentifier() is included in the interface only with annotation and therefore cannot be mocked using the phpunit tool.
         * Since version 6 of symfony, this function is then integrated into the interface. It is possible to remove the manual implementation of the mocked class.
         */
        // $tokenMock = $this->getMockBuilder(TokenMock::class)
        //     ->onlyMethods(['getUser'])
        //     ->getMock();
        // $tokenMock->method('getUser')->willReturn($customerUser);
        // $tokenMock->expects($this->any())->method('getUserIdentifier')->willReturn($customerUser->getEmail());

        $tokenMock = new TokenMock($customerUser);

        $tokenStorageStub = $this->createStub(TokenStorage::class);
        $tokenStorageStub->method('getToken')->willReturn($tokenMock);

        return $tokenStorageStub;
    }
}
