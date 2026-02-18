<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPriceCalculationForCustomerUserTest extends TestCase
{
    public function testCalculatePriceByUserAndDomainIdWithUser(): void
    {
        $customerStub = $this->createStub(Customer::class);

        $product = $this->createStub(Product::class);

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroup = new PricingGroup($pricingGroupData, 1);

        $customerUserData = new CustomerUserData();
        $customerUserData->pricingGroup = $pricingGroup;
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUserData->customer = $customerStub;
        $customerUserData->firstName = 'firstName';
        $customerUserData->lastName = 'lastName';

        $customerUser = new CustomerUser($customerUserData);
        $expectedProductPrice = new ProductPrice(new Price(Money::create(1), Money::create(1)), $pricingGroup, false);

        $currentCustomerUserStub = $this->createStub(CurrentCustomerUser::class);
        $pricingGroupSettingFacadeStub = $this->createStub(PricingGroupSettingFacade::class);

        $productPriceCalculationMock = $this->getMockBuilder(ProductPriceCalculation::class)
            ->onlyMethods(['calculatePrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $productPriceCalculationMock->expects($this->once())->method('calculatePrice')->willReturn(
            $expectedProductPrice,
        );

        $specialPriceFacadeStub = $this->createStub(SpecialPriceFacade::class);

        $domainStub = $this->createStub(Domain::class);

        $productPriceCalculationForCustomerUser = new ProductPriceCalculationForCustomerUser(
            $productPriceCalculationMock,
            $currentCustomerUserStub,
            $pricingGroupSettingFacadeStub,
            $domainStub,
            $specialPriceFacadeStub,
        );

        $productPrice = $productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            Domain::FIRST_DOMAIN_ID,
            $customerUser,
        )->sellingProductPrice;
        $this->assertSame($expectedProductPrice, $productPrice);
    }

    public function testCalculatePriceByUserAndDomainIdWithoutUser(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $product = $this->createStub(Product::class);
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroup = new PricingGroup($pricingGroupData, $domainId);
        $expectedProductPrice = new ProductPrice(new Price(Money::create(1), Money::create(1)), $pricingGroup, false);

        $currentCustomerUserStub = $this->createStub(CurrentCustomerUser::class);

        $pricingGroupFacadeMock = $this->getMockBuilder(PricingGroupSettingFacade::class)
            ->onlyMethods(['getDefaultPricingGroupByDomainId'])
            ->disableOriginalConstructor()
            ->getMock();
        $pricingGroupFacadeMock
            ->expects($this->once())
            ->method('getDefaultPricingGroupByDomainId')
            ->with($this->equalTo($domainId))
            ->willReturn($pricingGroup);

        $productPriceCalculationMock = $this->getMockBuilder(ProductPriceCalculation::class)
            ->onlyMethods(['calculatePrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $productPriceCalculationMock->expects($this->once())->method('calculatePrice')->willReturn(
            $expectedProductPrice,
        );

        $specialPriceFacadeStub = $this->createStub(SpecialPriceFacade::class);

        $domainStub = $this->createStub(Domain::class);

        $productPriceCalculationForCustomerUser = new ProductPriceCalculationForCustomerUser(
            $productPriceCalculationMock,
            $currentCustomerUserStub,
            $pricingGroupFacadeMock,
            $domainStub,
            $specialPriceFacadeStub,
        );

        $productPrice = $productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            $domainId,
        )->sellingProductPrice;
        $this->assertSame($expectedProductPrice, $productPrice);
    }
}
