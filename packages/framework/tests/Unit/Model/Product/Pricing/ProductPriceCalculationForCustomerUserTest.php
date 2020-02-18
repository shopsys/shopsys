<?php

namespace Tests\FrameworkBundle\Unit\Model\Product\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPriceCalculationForCustomerUserTest extends TestCase
{
    public function testCalculatePriceByUserAndDomainIdWithUser()
    {
        $product = $this->createMock(Product::class);
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroup = new PricingGroup($pricingGroupData, 1);
        $customerUserData = new CustomerUserData();
        $customerUserData->pricingGroup = $pricingGroup;
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUser = new CustomerUser($customerUserData);
        $expectedProductPrice = new ProductPrice(new Price(Money::create(1), Money::create(1)), false);

        $currentCustomerUserMock = $this->createMock(CurrentCustomerUser::class);
        $pricingGroupSettingFacadeMock = $this->createMock(PricingGroupSettingFacade::class);

        $productPriceCalculationMock = $this->getMockBuilder(ProductPriceCalculation::class)
            ->setMethods(['calculatePrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $productPriceCalculationMock->expects($this->once())->method('calculatePrice')->willReturn($expectedProductPrice);

        $domainMock = $this->createMock(Domain::class);

        $productPriceCalculationForCustomerUser = new ProductPriceCalculationForCustomerUser(
            $productPriceCalculationMock,
            $currentCustomerUserMock,
            $pricingGroupSettingFacadeMock,
            $domainMock
        );

        $productPrice = $productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId($product, Domain::FIRST_DOMAIN_ID, $customerUser);
        $this->assertSame($expectedProductPrice, $productPrice);
    }

    public function testCalculatePriceByUserAndDomainIdWithoutUser()
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $product = $this->createMock(Product::class);
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroup = new PricingGroup($pricingGroupData, $domainId);
        $expectedProductPrice = new ProductPrice(new Price(Money::create(1), Money::create(1)), false);

        $currentCustomerUserMock = $this->createMock(CurrentCustomerUser::class);

        $pricingGroupFacadeMock = $this->getMockBuilder(PricingGroupSettingFacade::class)
            ->setMethods(['getDefaultPricingGroupByDomainId'])
            ->disableOriginalConstructor()
            ->getMock();
        $pricingGroupFacadeMock
            ->expects($this->once())
            ->method('getDefaultPricingGroupByDomainId')
            ->with($this->equalTo($domainId))
            ->willReturn($pricingGroup);

        $productPriceCalculationMock = $this->getMockBuilder(ProductPriceCalculation::class)
            ->setMethods(['calculatePrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $productPriceCalculationMock->expects($this->once())->method('calculatePrice')->willReturn($expectedProductPrice);

        $domainMock = $this->createMock(Domain::class);

        $productPriceCalculationForCustomerUser = new ProductPriceCalculationForCustomerUser(
            $productPriceCalculationMock,
            $currentCustomerUserMock,
            $pricingGroupFacadeMock,
            $domainMock
        );

        $productPrice = $productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId($product, $domainId, null);
        $this->assertSame($expectedProductPrice, $productPrice);
    }
}
