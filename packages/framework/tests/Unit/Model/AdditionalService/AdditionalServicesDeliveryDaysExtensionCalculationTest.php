<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\AdditionalService;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicesDeliveryDaysExtensionCalculation;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

final class AdditionalServicesDeliveryDaysExtensionCalculationTest extends TestCase
{
    public function testDeliveryDaysExtensionIsTheHighestOfAllServicesChosenInCart(): void
    {
        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([
            $this->createCartItemStub([
                $this->createAdditionalServiceStub(1),
                $this->createAdditionalServiceStub(3),
            ]),
            $this->createCartItemStub([
                $this->createAdditionalServiceStub(0),
                $this->createAdditionalServiceStub(5),
            ]),
        ]);

        $additionalServicesDeliveryDaysExtensionCalculation = new AdditionalServicesDeliveryDaysExtensionCalculation();

        self::assertSame(5, $additionalServicesDeliveryDaysExtensionCalculation->calculateHighestDeliveryDaysExtension($cartStub));
    }

    public function testDeliveryDaysExtensionIsZeroWithoutChosenServices(): void
    {
        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([$this->createCartItemStub([])]);

        $additionalServicesDeliveryDaysExtensionCalculation = new AdditionalServicesDeliveryDaysExtensionCalculation();

        self::assertSame(0, $additionalServicesDeliveryDaysExtensionCalculation->calculateHighestDeliveryDaysExtension($cartStub));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $additionalServices
     */
    private function createCartItemStub(array $additionalServices): CartItem
    {
        $cartItemStub = $this->createStub(CartItem::class);
        $cartItemStub->method('getAdditionalServices')->willReturn($additionalServices);

        return $cartItemStub;
    }

    private function createAdditionalServiceStub(int $deliveryDaysExtension): AdditionalService
    {
        $additionalServiceStub = $this->createStub(AdditionalService::class);
        $additionalServiceStub->method('getDeliveryDaysExtension')->willReturn($deliveryDaysExtension);

        return $additionalServiceStub;
    }
}
