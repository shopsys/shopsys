<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
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
            new InMemoryCache(),
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
            new InMemoryCache(),
        );

        $productPrice = $productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            $domainId,
        )->sellingProductPrice;
        $this->assertSame($expectedProductPrice, $productPrice);
    }

    public function testPricesAreCalculatedAgainForAnotherPricingGroup(): void
    {
        $productPriceCalculationMock = $this->createProductPriceCalculationMock(2);
        $productPriceCalculationForCustomerUser = $this->createProductPriceCalculationForCustomerUser($productPriceCalculationMock);
        $product = $this->createEntityStub(Product::class, 1);

        $firstPrices = $productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId($product, Domain::FIRST_DOMAIN_ID, $this->createCustomerUserStubWithPricingGroupId(1));
        $secondPrices = $productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId($product, Domain::FIRST_DOMAIN_ID, $this->createCustomerUserStubWithPricingGroupId(2));

        $this->assertNotSame($firstPrices, $secondPrices);
    }

    private function createProductPriceCalculationMock(int $expectedCalculatePriceCalls): ProductPriceCalculation
    {
        $productPriceCalculationMock = $this->createMock(ProductPriceCalculation::class);
        $productPriceCalculationMock->expects($this->exactly($expectedCalculatePriceCalls))->method('calculatePrice')->willReturnCallback(
            static fn (Product $product, int $domainId, PricingGroup $pricingGroup): ProductPrice => new ProductPrice(new Price(Money::create(1), Money::create(1)), $pricingGroup, false),
        );

        return $productPriceCalculationMock;
    }

    private function createProductPriceCalculationForCustomerUser(
        ProductPriceCalculation $productPriceCalculation,
    ): ProductPriceCalculationForCustomerUser {
        return new ProductPriceCalculationForCustomerUser(
            $productPriceCalculation,
            $this->createStub(CurrentCustomerUser::class),
            $this->createStub(PricingGroupSettingFacade::class),
            $this->createStub(Domain::class),
            $this->createStub(SpecialPriceFacade::class),
            new InMemoryCache(),
        );
    }

    private function createCustomerUserStubWithPricingGroupId(int $pricingGroupId): CustomerUser
    {
        $customerUserStub = $this->createStub(CustomerUser::class);
        $customerUserStub->method('getPricingGroup')->willReturn($this->createEntityStub(PricingGroup::class, $pricingGroupId));

        return $customerUserStub;
    }

    /**
     * @template T of object
     * @param class-string<T> $entityClassName
     * @return T
     */
    private function createEntityStub(string $entityClassName, int $id): object
    {
        $entityStub = $this->createStub($entityClassName);
        $entityStub->method('getId')->willReturn($id);

        return $entityStub;
    }
}
