<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderInputFingerprintTest extends TestCase
{
    public function testEquallyBuiltOrderInputsShareFingerprint(): void
    {
        $this->assertSame($this->createOrderInput()->getFingerprint(), $this->createOrderInput()->getFingerprint());
    }

    public function testAdditionalDataOrderDoesNotChangeFingerprint(): void
    {
        $firstOrderInput = $this->createOrderInput();
        $firstOrderInput->addAdditionalData('first', 1);
        $firstOrderInput->addAdditionalData('second', 2);

        $secondOrderInput = $this->createOrderInput();
        $secondOrderInput->addAdditionalData('second', 2);
        $secondOrderInput->addAdditionalData('first', 1);

        $this->assertSame($firstOrderInput->getFingerprint(), $secondOrderInput->getFingerprint());
    }

    public function testAdditionalServicesOfQuantifiedProductChangeFingerprint(): void
    {
        $orderInputWithoutAdditionalService = $this->createOrderInput();
        $orderInputWithAdditionalService = $this->createOrderInput();
        array_first($orderInputWithAdditionalService->getQuantifiedProducts())->setAdditionalData(
            QuantifiedProduct::ADDITIONAL_SERVICES_KEY,
            [$this->createEntityStub(AdditionalService::class, 99)],
        );
        $orderInputWithAnotherAdditionalService = $this->createOrderInput();
        array_first($orderInputWithAnotherAdditionalService->getQuantifiedProducts())->setAdditionalData(
            QuantifiedProduct::ADDITIONAL_SERVICES_KEY,
            [$this->createEntityStub(AdditionalService::class, 100)],
        );

        $this->assertNotSame($orderInputWithoutAdditionalService->getFingerprint(), $orderInputWithAdditionalService->getFingerprint());
        $this->assertNotSame($orderInputWithAdditionalService->getFingerprint(), $orderInputWithAnotherAdditionalService->getFingerprint());
    }

    private function createOrderInput(): OrderInput
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);
        $domainConfigStub->method('getId')->willReturn(1);

        $orderInput = new OrderInputFactory()->create($domainConfigStub);
        $orderInput->addProduct($this->createEntityStub(Product::class, 1), 2);
        $orderInput->setTransport($this->createEntityStub(Transport::class, 10));
        $orderInput->setPayment($this->createEntityStub(Payment::class, 30));
        $orderInput->setCustomerUser($this->createEntityStub(CustomerUser::class, 50));
        $orderInput->addAdditionalData('pickupPlaceIdentifier', 'store-uuid');
        $orderInput->addAdditionalData('cartTotalWeight', 1500);

        return $orderInput;
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
