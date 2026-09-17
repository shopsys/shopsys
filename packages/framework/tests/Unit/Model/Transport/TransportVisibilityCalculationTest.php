<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;
use Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation;

class TransportVisibilityCalculationTest extends TestCase
{
    private const string TRANSPORT_COMMON = 'common';
    private const string TRANSPORT_PERSONAL_PICKUP = 'personalPickup';
    private const string TRANSPORT_EMAIL = 'email';

    public function testIsVisibleWhenIndepentlyInvisible(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(false);

        $independentPaymentVisibilityCalculationStub = $this
            ->createStub(IndependentPaymentVisibilityCalculation::class);

        $transportRepositoryStub = $this->createStub(TransportRepository::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationStub,
            $transportRepositoryStub,
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportStub, [], $domainId));
    }

    public function testIsVisibleWithHiddenPayment(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);
        $paymentStub = $this->createStub(Payment::class);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentStub), $this->equalTo($domainId))
            ->willReturn(false);

        $transportRepositoryStub = $this->createStub(TransportRepository::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $transportRepositoryStub,
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportStub, [$paymentStub], $domainId));
    }

    public function testIsVisibleWithoutPayment(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $transportRepositoryStub = $this->createStub(TransportRepository::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $transportRepositoryStub,
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportStub, [$paymentMock], $domainId));
    }

    public function testIsVisibleWithVisiblePayment(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([$transportStub]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $transportRepositoryStub = $this->createStub(TransportRepository::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $transportRepositoryStub,
        );

        $this->assertTrue($transportVisibilityCalculation->isVisible($transportStub, [$paymentMock], $domainId));
    }

    public function testFilterVisible(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportHiddenStub = $this->createStub(Transport::class);
        $transportVisibleStub = $this->createStub(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([$transportVisibleStub]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportVisibleStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $transportRepositoryStub = $this->createStub(TransportRepository::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $transportRepositoryStub,
        );

        $transports = [$transportHiddenStub, $transportVisibleStub];

        $filteredTransports = $transportVisibilityCalculation->filterVisible($transports, [$paymentMock], $domainId);

        $this->assertCount(1, $filteredTransports);
        $this->assertContains($transportVisibleStub, $filteredTransports);
    }

    /**
     * @param string[] $expectedTransportKeys
     */
    #[DataProvider('filterTransportsUsableForProductDataProvider')]
    public function testFilterTransportsUsableForProduct(
        bool $isElectronicGiftVoucher,
        bool $isPersonalPickupOnly,
        array $expectedTransportKeys,
    ): void {
        $transports = [
            self::TRANSPORT_COMMON => $this->createTransportStub(1, isPersonalPickup: false, isEmailType: false),
            self::TRANSPORT_PERSONAL_PICKUP => $this->createTransportStub(2, isPersonalPickup: true, isEmailType: false),
            self::TRANSPORT_EMAIL => $this->createTransportStub(3, isPersonalPickup: false, isEmailType: true),
        ];

        $productStub = $this->createStub(Product::class);
        $productStub->method('getId')->willReturn(10);
        $productStub->method('isElectronicGiftVoucher')->willReturn($isElectronicGiftVoucher);
        $productStub->method('isPersonalPickupOnly')->willReturn($isPersonalPickupOnly);

        $transportRepositoryStub = $this->createStub(TransportRepository::class);
        $transportRepositoryStub->method('getProductIdsIndexedByExcludedTransportId')->willReturn([]);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $this->createStub(IndependentTransportVisibilityCalculation::class),
            $this->createStub(IndependentPaymentVisibilityCalculation::class),
            $transportRepositoryStub,
        );

        $usableTransports = $transportVisibilityCalculation->filterTransportsUsableForProduct(array_values($transports), $productStub);

        $expectedTransports = array_map(static fn (string $key): Transport => $transports[$key], $expectedTransportKeys);
        $this->assertSame($expectedTransports, $usableTransports);
    }

    /**
     * @return iterable<string, array{isElectronicGiftVoucher: bool, isPersonalPickupOnly: bool, expectedTransportKeys: string[]}>
     */
    public static function filterTransportsUsableForProductDataProvider(): iterable
    {
        yield 'regular product is never delivered by email' => [
            'isElectronicGiftVoucher' => false,
            'isPersonalPickupOnly' => false,
            'expectedTransportKeys' => [self::TRANSPORT_COMMON, self::TRANSPORT_PERSONAL_PICKUP],
        ];

        yield 'electronic gift voucher is delivered by email only' => [
            'isElectronicGiftVoucher' => true,
            'isPersonalPickupOnly' => false,
            'expectedTransportKeys' => [self::TRANSPORT_EMAIL],
        ];

        yield 'personal pickup only product gets personal pickup transports only' => [
            'isElectronicGiftVoucher' => false,
            'isPersonalPickupOnly' => true,
            'expectedTransportKeys' => [self::TRANSPORT_PERSONAL_PICKUP],
        ];
    }

    private function createTransportStub(int $id, bool $isPersonalPickup, bool $isEmailType): Transport
    {
        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getId')->willReturn($id);
        $transportStub->method('isPersonalPickup')->willReturn($isPersonalPickup);
        $transportStub->method('isEmailType')->willReturn($isEmailType);

        return $transportStub;
    }
}
