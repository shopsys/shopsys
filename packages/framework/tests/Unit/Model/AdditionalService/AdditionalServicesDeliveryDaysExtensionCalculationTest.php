<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\AdditionalService;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicesDeliveryDaysExtensionCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Product\Product;

final class AdditionalServicesDeliveryDaysExtensionCalculationTest extends TestCase
{
    public function testDeliveryDaysExtensionIsTheHighestOfAllServicesChosenForTheProducts(): void
    {
        $quantifiedProducts = [
            $this->createQuantifiedProductWithAdditionalServices([
                $this->createAdditionalServiceStub(1),
                $this->createAdditionalServiceStub(3),
            ]),
            $this->createQuantifiedProductWithAdditionalServices([
                $this->createAdditionalServiceStub(0),
                $this->createAdditionalServiceStub(5),
            ]),
        ];

        $additionalServicesDeliveryDaysExtensionCalculation = new AdditionalServicesDeliveryDaysExtensionCalculation();

        self::assertSame(5, $additionalServicesDeliveryDaysExtensionCalculation->calculateHighestDeliveryDaysExtension($quantifiedProducts));
    }

    public function testDeliveryDaysExtensionIsZeroWithoutChosenServices(): void
    {
        $quantifiedProducts = [
            $this->createQuantifiedProductWithAdditionalServices([]),
            new QuantifiedProduct($this->createStub(Product::class), 1),
        ];

        $additionalServicesDeliveryDaysExtensionCalculation = new AdditionalServicesDeliveryDaysExtensionCalculation();

        self::assertSame(0, $additionalServicesDeliveryDaysExtensionCalculation->calculateHighestDeliveryDaysExtension($quantifiedProducts));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $additionalServices
     */
    private function createQuantifiedProductWithAdditionalServices(array $additionalServices): QuantifiedProduct
    {
        $quantifiedProduct = new QuantifiedProduct($this->createStub(Product::class), 1);
        $quantifiedProduct->setAdditionalData(QuantifiedProduct::ADDITIONAL_SERVICES_KEY, $additionalServices);

        return $quantifiedProduct;
    }

    private function createAdditionalServiceStub(int $deliveryDaysExtension): AdditionalService
    {
        $additionalServiceStub = $this->createStub(AdditionalService::class);
        $additionalServiceStub->method('getDeliveryDaysExtension')->willReturn($deliveryDaysExtension);

        return $additionalServiceStub;
    }
}
